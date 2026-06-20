<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Centralized image optimization for every CRUD module that uploads photos.
 *
 * One-line usage (drop-in replacement for `$file->store('images', 'public')`):
 *
 *     $path = ImageOptimizerService::storeAndOptimize(
 *         $request->file('image'),
 *         'images/aduan'
 *     );
 *
 * The returned value is the path RELATIVE to the public disk (e.g.
 * `images/aduan/uuid.jpg`) — identical in shape to what `store()` returns, so
 * callers that wrap it with `url('storage/'.$path)` keep working unchanged.
 *
 * Behavior:
 *  - Optimization happens EXACTLY ONCE, here, during upload. Images are never
 *    reprocessed on read.
 *  - Auto-orients from EXIF, preserves aspect ratio, NEVER upscales, downsizes
 *    only when width > {@see self::MAX_WIDTH}, keeps the original format when it
 *    can, and re-encodes JPEG/WEBP at quality {@see self::QUALITY}.
 *  - SAFE: if Intervention Image (or a GD/Imagick driver) is unavailable, or any
 *    step throws, it logs and falls back to storing the original upload via
 *    Laravel Storage. CRUD never breaks. No move() is used.
 *
 * Activation note: optimization requires `intervention/image` (v3) installed and
 * a GD or Imagick PHP extension enabled. Until then this transparently stores the
 * original file (no error surfaced to the user).
 */
class ImageOptimizerService
{
    /** Only downscale when the source is wider than this (px). Never upscale. */
    public const MAX_WIDTH = 1600;

    /** Encoder quality for lossy formats (JPEG/WEBP). */
    public const QUALITY = 80;

    /** Formats we will re-encode; anything else is stored as-is. */
    private const OPTIMIZABLE = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Optimize (once) and store an uploaded image on the given disk/directory.
     *
     * @param  UploadedFile|null  $file       The uploaded file (e.g. $request->file('image')).
     * @param  string             $directory  Target directory on the disk (e.g. 'images/aduan').
     * @param  string             $disk       Storage disk; defaults to 'public'.
     * @return string|null  Path relative to the disk, or null when no valid file was given.
     */
    public static function storeAndOptimize(
        ?UploadedFile $file,
        string $directory,
        string $disk = 'public'
    ): ?string {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        $disk = $disk !== '' ? $disk : 'public';
        $directory = trim($directory, '/');
        $extension = self::resolveExtension($file);
        $filename = Str::uuid()->toString() . '.' . $extension;
        $path = $directory . '/' . $filename;

        // 1) Try the optimized path.
        try {
            $encoded = self::optimize($file, $extension);
            if ($encoded !== null) {
                Storage::disk($disk)->put($path, $encoded);
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService: optimization failed; storing original.', [
                'directory' => $directory,
                'original'  => $file->getClientOriginalName(),
                'error'     => $e->getMessage(),
            ]);
        }

        // 2) Fallback: store the untouched upload via Storage (NO move()).
        try {
            $stored = $file->storeAs($directory, $filename, ['disk' => $disk]);
            return $stored !== false ? $stored : null;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizerService: fallback store failed.', [
                'directory' => $directory,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Returns the optimized binary string, or null when optimization is not
     * possible (library/driver missing, or format we do not re-encode) so the
     * caller can fall back to storing the original.
     */
    private static function optimize(UploadedFile $file, string $extension): ?string
    {
        if (! in_array($extension, self::OPTIMIZABLE, true)) {
            return null; // e.g. gif/bmp/heic — keep original untouched.
        }

        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return null; // intervention/image not installed yet.
        }

        $manager = self::manager();
        if ($manager === null) {
            return null; // no GD/Imagick driver available.
        }

        $image = $manager->read($file->getRealPath());

        // Auto-orient from EXIF, then downscale to MAX_WIDTH. scaleDown preserves
        // the aspect ratio and is a NO-OP when width <= MAX_WIDTH (never upscales).
        $image = $image->orient()->scaleDown(width: self::MAX_WIDTH);

        // Keep the original format; JPEG/WEBP at QUALITY, PNG re-encoded losslessly.
        $encoded = $image->encodeByExtension($extension, quality: self::QUALITY);

        return (string) $encoded;
    }

    /**
     * Build an Intervention Image v3 manager, preferring Imagick, then GD.
     */
    private static function manager(): ?\Intervention\Image\ImageManager
    {
        try {
            if (extension_loaded('imagick')) {
                return \Intervention\Image\ImageManager::imagick();
            }
            if (extension_loaded('gd')) {
                return \Intervention\Image\ImageManager::gd();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Resolve a safe lowercase file extension for the stored image.
     */
    private static function resolveExtension(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === '') {
            $ext = strtolower((string) $file->guessExtension());
        }

        return $ext !== '' ? $ext : 'jpg';
    }
}

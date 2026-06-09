<?php

namespace App\Support\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SiteContext
{
    public static function resolve(Request $request, bool $fallbackToAuth = true): ?string
    {
        $site = $request->query('site', $request->input('site'));

        if ($request->user() && ! self::canAccessAnySite($request)) {
            $site = $request->user()->site;
        }

        if (! $site && $fallbackToAuth && $request->user()) {
            $site = $request->user()->site;
        }

        if (! $site) {
            return null;
        }

        return strtoupper((string) $site);
    }

    public static function requested(Request $request, bool $fallbackToAuth = true): ?string
    {
        $site = $request->query('site', $request->input('site'));

        if (! $site && $fallbackToAuth && $request->user()) {
            $site = $request->user()->site;
        }

        if (! $site) {
            return null;
        }

        return strtoupper((string) $site);
    }

    public static function canAccessAnySite(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        return $user->role === 'ict_developer'
            || $user->role === 'ict_ho'
            || strtoupper((string) $user->site) === 'HO';
    }

    public static function authorizeSite(Request $request, ?string $site): void
    {
        if (self::canAccessAnySite($request)) {
            return;
        }

        $userSite = strtoupper((string) $request->user()?->site);

        if (strtoupper((string) $site) !== $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }
    }

    public static function authorizeWrite(Request $request, ?string $site = null): void
    {
        $allowedRoles = [
            'ict_developer',
            'ict_ho',
            'ict_section_head',
            'ict_group_leader',
            'ict_admin',
            'ict_technician',
        ];

        if (! in_array($request->user()?->role, $allowedRoles, true)) {
            abort(403, 'You dont have permission to access this page.');
        }

        if ($site !== null) {
            self::authorizeSite($request, $site);
        }
    }

    public static function isHo(?string $site): bool
    {
        return $site === null || $site === '' || strtoupper($site) === 'HO';
    }

    public static function apply(Builder $query, string $column, ?string $site): Builder
    {
        if (self::isHo($site)) {
            return $query->where(function (Builder $builder) use ($column) {
                $builder->whereNull($column)->orWhere($column, 'HO');
            });
        }

        return $query->where($column, strtoupper($site));
    }
}

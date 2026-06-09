<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        return array_merge(parent::share($request), [
            // Authenticated user
            'auth.user' => fn () => $request->user(),

            // Session messages
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'duplicates' => session('duplicates'),
                'duplicatesInsertSn' => session('duplicatesInsertSn'),
                'computer_code' => session('computer_code'),
                'laptop_code' => session('laptop_code'),
                'inventory_number' => session('inventory_number'),
                'dept' => session('dept'),
                'newAduan' => session('new_aduan'),
            ],
        ]);
    }
}

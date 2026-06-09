<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $roles = array_slice(func_get_args(), 2);

        $userRole = \Auth::user()->role;
        $userSite = \Auth::user()->site;
        // dd($roles);
        foreach ($roles as $role) {
            [$role, $site] = explode(':', $role);

            if ($userRole === $role && $userSite === $site) {
                return $next($request); // Jika cocok, izinkan akses
            }
        }

        return abort(403, 'Anda tidak memiliki aksess pada halaman ini!');
    }
}
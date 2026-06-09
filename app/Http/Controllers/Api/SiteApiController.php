<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $authorized = $user->tokenCan('role:ict_ho') || $user->tokenCan('role:ict_developer');

        if (! $authorized) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $sites = DB::table('users')
            ->select('site')
            ->whereNotNull('site')
            ->where('site', '<>', '')
            ->distinct()
            ->orderBy('site')
            ->pluck('site');

        return response()->json(['sites' => $sites]);
    }
}

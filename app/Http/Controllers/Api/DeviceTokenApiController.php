<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

/**
 * Register / unregister the caller's FCM device token.
 *
 *   POST   /api/device-token   → called right after login (and on token refresh)
 *   DELETE /api/device-token   → called on logout
 *
 * A token is globally unique: if it was previously owned by another user (shared
 * device / re-login) it is re-pointed to the current user via updateOrCreate.
 */
class DeviceTokenApiController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        $device = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'  => $request->user()->id,
                'platform' => $data['platform'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Device token registered.',
            'data'    => $device,
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        // Only delete the caller's own token (defensive: scope by user).
        DeviceToken::where('token', $data['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Device token removed.']);
    }
}

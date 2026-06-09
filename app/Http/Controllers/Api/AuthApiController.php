<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SyncUserAllService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function __construct(private readonly SyncUserAllService $syncUserAllService) {}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nrp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('nrp', $credentials['nrp'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }

        $this->syncUserAllService->syncFromUser($user);

        $token = $user->createToken('api', ['role:' . $user->role])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function index(Request $request){
        $errorLoginUnamePasswr = $request->session()->get('errorLoginUnamePasswr', null);
        $errorMessage = $request->session()->get('errorMessage', null);
        $errorMessagePE = $request->session()->get('errorMessagePE', null);
        $errorLoginUnamePassnf = $request->session()->get('errorLoginUnamePassnf', null);
        $errorLoginKoneksi = $request->session()->get('errorLoginKoneksi', null);
        return Inertia::render('Auth/LoginLocal', [
            'errorLoginUnamePasswr' => session('errorLoginUnamePasswr'),
            'errorMessage' => session('errorMessage'),
            'errorMessagePE' => session('errorMessagePE'),
            'errorLoginUnamePassnf' => session('errorLoginUnamePassnf'),
            'errorLoginKoneksi' => session('errorLoginKoneksi'),
        ]);
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nrp' => 'required|string|max:255|unique:users',
            'position' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            // 'c_password' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nrp' => $request->nrp,
            'role' => $request->role,
            'position' => $request->position,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function change_password(Request $request)
    {
        $data = [
            'nrp' => $request->nrp,
            'password' => $request->new_password,
        ];
        // return response()->json($data);

        $user = User::where('nrp', $request->nrp)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message', 'The provided credentials are incorrect.']);
        } else {
            $update_password = User::firstWhere('nrp', $request->nrp)->update($data);
            return response()->json($update_password, 201);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
}

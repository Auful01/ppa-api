<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

function decodeJWT($token)
{
    try {
        // Replace dengan secret key atau public key sesuai kebutuhan
        $secretKey = '81d39ca86a907e014d6784a219f563f5c9d578139c5a1ece9c09927b55843ea1';

        // Decode token (gunakan algoritma yang sesuai, misalnya HS256 atau RS256)
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        // Jika menggunakan RS256
        // $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

        // Ubah hasil menjadi array
        return (array) $decoded;
    } catch (\Exception $e) {
        // Tangani error jika terjadi kesalahan saat decoding
        return ['error' => $e->getMessage()];
    }
}
class TestingAuthApiController extends Controller
{

    public function index(Request $request): RedirectResponse
    {
        $dataLogin = [
            'nrp' => $request->nrp,
            'password' => $request->password,
        ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ])->post('https://apikong.transformore.net/ict/auth/v1/auth', $dataLogin);
        if ($response['statusCode'] == 200) {
            $cookies = $response->cookies();
            foreach ($cookies as $cookie) {
                if ($cookie->getName() === 'access_token') {
                    $accessToken = $cookie->getValue();
                    break;
                }
            }
            if (isset($accessToken)) {
                // return dd($accessToken);
                $token = str_replace('Bearer ', '', $accessToken); // Hilangkan prefix "Bearer"
                $dataToArray = decodeJWT($token); // Panggil fungsi decode JWT

                $dataUser = User::where('nrp', $dataToArray['nrp'])->first();
                if ($dataUser) {
                    $dataUpdate = [
                        'password' => Hash::make($request->password),
                        'position' => $dataToArray['posisi'],
                        'department' => $dataToArray['dept'],
                        'email' => $dataToArray['email'],
                        'site' => $dataToArray['site'],
                    ];
                    $updateDatauser = User::firstWhere('nrp', $dataToArray['nrp'])->update($dataUpdate);

                    $request->authenticate();

                    $request->session()->regenerate();

                    return redirect()->intended(route('home', absolute: false));
                } else {
                    $dataCreate = [
                        'name' => $dataToArray['nama'],
                        'nrp' => $dataToArray['nrp'],
                        'password' => Hash::make($request->password),
                        'position' => $dataToArray['posisi'],
                        'department' => $dataToArray['dept'],
                        'email' => $dataToArray['email'],
                        'site' => $dataToArray['site'],
                        'role' => 'ict_developer', //masih default developer role
                    ];
                    User::create($dataCreate);

                    $request->authenticate();

                    $request->session()->regenerate();

                    return redirect()->intended(route('home', absolute: false));
                }
            }
        } else {
            $request->authenticate();

            $request->session()->regenerate();

            return redirect()->intended(route('home', absolute: false));
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\SyncUserAllService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;


function decodeJWT($token)
{
    try {
        $secretKey = env('JWT_SECRET');
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return (array) $decoded;
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly SyncUserAllService $syncUserAllService) {}

    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        $errorLoginUnamePasswr = $request->session()->get('errorLoginUnamePasswr', null);
        $errorMessage = $request->session()->get('errorMessage', null);
        $errorMessagePE = $request->session()->get('errorMessagePE', null);
        $errorLoginUnamePassnf = $request->session()->get('errorLoginUnamePassnf', null);
        $errorLoginKoneksi = $request->session()->get('errorLoginKoneksi', null);
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'errorLoginUnamePasswr' => session('errorLoginUnamePasswr'),
            'errorMessage' => session('errorMessage'),
            'errorMessagePE' => session('errorMessagePE'),
            'errorLoginUnamePassnf' => session('errorLoginUnamePassnf'),
            'errorLoginKoneksi' => session('errorLoginKoneksi'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // dd($request);
        if ($request->local == 'Y') {
            // Login langsung ke database lokal tanpa validasi JWT
            $dataUser = User::where('nrp', $request->nrp)->where('site', 'BGE')->first();

            if ($dataUser && Hash::check($request->password, $dataUser->password)) {
                $this->syncUserAllService->syncFromUser($dataUser);
                $request->authenticate();
                $request->session()->regenerate();
                return redirect(route('home', absolute: false));
            } else if (empty($dataUser)) {
                $request->session()->flash('errorLoginUnamePasswr', 'NRP anda tidak terdaftar di local! lakukan login dengan SS6');
                return redirect('/login-bge');
            } else {
                $request->session()->flash('errorLoginUnamePasswr', 'NRP atau Password anda salah!');
                return redirect('/login');
            }
        }

        $dataLogin = [
            'nrp' => $request->nrp,
            'password' => $request->password,
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ])->timeout(10)->post('https://ict-auth.ppa-ho.net/auth', $dataLogin);

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

                    // dd($dataToArray['error']);

                    if (!empty($dataToArray['error'])) {
                        $request->authenticate();

                        if ($request->user()) {
                            $this->syncUserAllService->syncFromUser($request->user());
                        }

                        $request->session()->regenerate();

                        return redirect(route('home', absolute: false));
                    } else {
                        $dataUser = User::where('nrp', $dataToArray['nrp'])->first();
                        if ($dataUser) {
                            $dataUpdate = [
                                'password' => Hash::make($request->password),
                                'position' => $dataToArray['posisi'],
                                'department' => $dataToArray['dept'],
                                'email' => $dataToArray['email'],
                                'site' => $dataToArray['site'],
                            ];
                            User::firstWhere('nrp', $dataToArray['nrp'])->update($dataUpdate);
                            $this->syncUserAllService->sync([
                                'nrp' => $dataToArray['nrp'],
                                'username' => $dataToArray['nama'],
                                'position' => $dataToArray['posisi'],
                                'department' => $dataToArray['dept'],
                                'email' => $dataToArray['email'],
                                'site' => $dataToArray['site'],
                            ]);
                            $request->authenticate();

                            $request->session()->regenerate();

                            return redirect(route('home', absolute: false));
                        } else {
                            $dataCreate = [
                                'name' => $dataToArray['nama'],
                                'nrp' => $dataToArray['nrp'],
                                'password' => Hash::make($request->password),
                                'position' => $dataToArray['posisi'],
                                'department' => $dataToArray['dept'],
                                'email' => $dataToArray['email'],
                                'site' => $dataToArray['site'],
                                'role' => 'guest', //masih default developer role
                            ];
                            User::create($dataCreate);

                            $this->syncUserAllService->sync([
                                'nrp' => $dataToArray['nrp'],
                                'username' => $dataToArray['nama'],
                                'position' => $dataToArray['posisi'],
                                'department' => $dataToArray['dept'],
                                'email' => $dataToArray['email'],
                                'site' => $dataToArray['site'],
                            ]);

                            $request->authenticate();

                            $request->session()->regenerate();

                            return redirect(route('home', absolute: false));
                        }
                    }
                }
            } elseif ($response['statusCode'] == 400) {
                $request->session()->flash('errorLoginUnamePasswr', 'NRP atau Password anda salah!');
                return redirect('/login');
            } elseif ($response['statusCode'] == 419) {
                $request->session()->flash('errorMessagePE', 'Terlalu banyak melakukan percobaan!');
                return redirect('/login');
            } elseif ($response['statusCode'] == 500) {
                $request->session()->flash('errorLoginUnamePassnf', 'Nrp tidak terdaftar!');
                return redirect('/login');
            }
        } catch (RequestException $e) {
            $request->session()->flash('errorLoginKoneksi', 'Internet anda tidak stabil untuk mengakses halaman ini!');
            return redirect('/login');
        } catch (\Exception $e) {
            $request->session()->flash('errorLoginKoneksi', 'Internet anda tidak stabil untuk mengakses halaman ini!');
            return redirect('/login');
        }
        return redirect('/login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $auth = auth()->user()->site;

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ])->connectTimeout(10)->timeout(30)
                ->delete('https://ict-auth.ppa-ho.net/auth/logout');

            if ($auth == 'BGE') {
                Auth::guard('web')->logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();

                return redirect('/login-bge');
            } else {
                if ($response['statusCode'] == 401) {
                    Auth::guard('web')->logout();

                    $request->session()->invalidate();

                    $request->session()->regenerateToken();

                    return redirect('/login');
                } elseif ($response['statusCode'] == 429) {
                    $request->session()->flash('errorMessage', 'Terlalu banyak melakukan percobaan!');
                    Auth::guard('web')->logout();

                    $request->session()->invalidate();

                    $request->session()->regenerateToken();

                    return redirect('/login');
                }
            }
        } catch (\Throwable $e) {

            $request->session()->flash(
                'errorLoginKoneksi',
                'Internet anda tidak stabil untuk mengakses halaman ini!'
            );
        }

    }
}

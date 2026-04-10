<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nrp' => 'required',
            'password' => 'required'
        ]);

        $apiUrl = env('NODE_API_URL', 'http://localhost:3000');

        try {
            $response = Http::post("{$apiUrl}/api/auth/login", [
                'nrp' => $request->nrp,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Store token and user data in session
                Session::put('api_token', $data['token'] ?? null);
                Session::put('user', $data['user'] ?? null);
                
                // Helper session keys for easier access
                Session::put('user_role', $data['user']['role'] ?? null);
                Session::put('mitra_id', $data['user']['mitra_id'] ?? null);

                // Restrict employee accounts from accessing the admin website
                if (($data['user']['role'] ?? '') === 'employee') {
                    Session::forget(['api_token', 'user', 'user_role', 'mitra_id']);
                    return back()->withErrors([
                        'nrp' => 'Akun anda tidak memiliki akses ke portal admin. Silahkan gunakan aplikasi mobile.',
                    ]);
                }

                return redirect()->intended('/select-profile')->with('success', 'Berhasil login!');
            }

            $apiError = $response->json('error') ?? $response->json('message') ?? 'Unauthorized';
            return back()->withErrors([
                'nrp' => 'NRP atau Password salah. Peringatan SERVER: ' . $apiError,
            ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'nrp' => 'Gagal terhubung ke API Node.js: ' . $e->getMessage(),
            ]);
        }
    }

    public function selectProfileView()
    {
        // Pastikan user sudah memiliki sesi login
        if (!Session::has('api_token')) {
            return redirect('/login')->withErrors(['nrp' => 'Silakan login terlebih dahulu.']);
        }

        return view('auth.select-profile');
    }

    public function selectProfilePost(Request $request)
    {
        // Validasi isian profile dan site
        $request->validate([
            'profile' => 'required|string',
            'site' => 'required|string'
        ]);

        // Setelah divalidasi, simpan opsi ini ke session (opsional, tergantung kebutuhan realnya)
        Session::put('selected_profile', $request->profile);
        Session::put('selected_site', $request->site);

        // Setelah selesai, akan diarahkan ke dashboard
        return redirect('/')->with('success', 'Profil berhasil dipilih!');
    }

    public function dashboard()
    {
        // Pastikan sudah ada sesi dari API backend
        if (!Session::has('api_token')) {
            return redirect('/login')->withErrors(['nrp' => 'Silakan login terlebih dahulu.']);
        }

        // Jika profile & site belum dipilih, paksa kembali ke halaman select profile
        if (!Session::has('selected_profile')) {
            return redirect('/select-profile')->withErrors(['nrp' => 'Anda harus menyeleksi profil terlebih dahulu.']);
        }

        return view('dashboard');
    }

    public function users()
    {
        if (!Session::has('api_token')) {
            return redirect('/login')->withErrors(['nrp' => 'Silakan login terlebih dahulu.']);
        }

        $apiUrl = env('NODE_API_URL', 'http://localhost:3000');
        $token = Session::get('api_token');

        try {
            $response = Http::withToken($token)->get("{$apiUrl}/api/users");
            $rawUsers = $response->successful() ? $response->json() : [];
            
            // Trim data for performance
            $users = array_map(function($u) {
                return [
                    'nrp' => $u['nrp'],
                    'role' => $u['role'],
                    'is_active' => $u['is_active'],
                    'employee' => [
                        'full_name' => $u['employee']['full_name'] ?? '-'
                    ]
                ];
            }, $rawUsers);
        } catch (\Exception $e) {
            $users = [];
        }

        return view('auth.users', compact('users'));
    }

    public function updateUserStatus(Request $request, $nrp)
    {
        $token = Session::get('api_token');
        $apiUrl = env('NODE_API_URL', 'http://localhost:3000');

        try {
            $response = Http::withToken($token)->patch("{$apiUrl}/api/users/{$nrp}", [
                'is_active' => $request->is_active
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyUser($nrp)
    {
        $token = Session::get('api_token');
        $apiUrl = env('NODE_API_URL', 'http://localhost:3000');

        try {
            $response = Http::withToken($token)->delete("{$apiUrl}/api/users/{$nrp}");

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        Session::forget('api_token');
        Session::forget('user');
        return redirect('/login');
    }
}

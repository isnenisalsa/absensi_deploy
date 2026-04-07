<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MasterMitraController extends Controller
{
    public function index()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        // Super Admin check (optional, but good practice)
        if (Session::get('user_role') !== 'admin' || Session::get('mitra_id') !== null) {
            return redirect('/dashboard')->withErrors(['error' => 'Hanya Super Admin yang bisa mengakses menu ini.']);
        }

        $token = Session::get('api_token');
        $response = Http::withToken($token)->get('http://localhost:3000/api/master/mitras');
        $mitras = $response->successful() ? $response->json() : [];

        return view('master.mitra', compact('mitras'));
    }

    public function store(Request $request)
    {
        $token = Session::get('api_token');
        $response = Http::withToken($token)->post('http://localhost:3000/api/master/mitras', $request->all());

        if ($response->successful()) {
            $data = $response->json();
            $msg = "Mitra berhasil ditambahkan. Akun Admin: " . $data['admin']['nrp'] . " (Password: " . $data['admin']['password'] . ")";
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->withErrors(['error' => $response->json()['error'] ?? 'Gagal menambah mitra']);
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('api_token');
        $response = Http::withToken($token)->put("http://localhost:3000/api/master/mitras/{$id}", $request->all());

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Data mitra berhasil diperbarui');
        }

        return redirect()->back()->withErrors(['error' => 'Gagal memperbarui data mitra']);
    }

    public function destroy($id)
    {
        $token = Session::get('api_token');
        $response = Http::withToken($token)->delete("http://localhost:3000/api/master/mitras/{$id}");

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Mitra berhasil dihapus');
        }

        return redirect()->back()->withErrors(['error' => $response->json()['error'] ?? 'Gagal menghapus mitra']);
    }
}

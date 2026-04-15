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

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada mitra yang dipilih.']);
        }

        $token = Session::get('api_token');
        $response = Http::withToken($token)->delete("http://localhost:3000/api/master/mitras/bulk", ['ids' => $ids]);

        return $response->successful() 
            ? redirect()->back()->with('success', count($ids) . ' Mitra berhasil dihapus sekaligus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus mitra massal: ' . ($response->json()['error'] ?? $response->body())]);
    }

    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return redirect()->back()->withErrors(['error' => 'Pilih file Excel terlebih dahulu.']);
        }

        $file = $request->file('file');
        $token = Session::get('api_token');
        $response = Http::withToken($token)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("http://localhost:3000/api/master/mitras/import");

        return $response->successful() 
            ? redirect()->back()->with('success', $response->json()['message'] ?? 'Import mitra berhasil!')
            : redirect()->back()->withErrors(['error' => 'Gagal impor mitra: ' . ($response->json()['error'] ?? $response->body())]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class EmployeeController extends Controller
{
    private $baseUrl = 'http://localhost:3000/api/employees';

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . Session::get('api_token'),
            'Accept' => 'application/json',
        ];
    }

    public function index()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $token = Session::get('api_token');
        
        // Fetch Employees
        $response = Http::withHeaders($this->getHeaders())->get($this->baseUrl);
        $rawEmployees = $response->successful() ? $response->json() : [];
        
        // Trim data for performance
        $employees = array_map(function($emp) {
            return [
                'nrp' => $emp['nrp'],
                'full_name' => $emp['full_name'],
                'pos_id' => $emp['pos_id'] ?? null,
                'div_id' => $emp['div_id'] ?? null,
                'dept_id' => $emp['dept_id'] ?? null,
                'mitra_id' => $emp['mitra_id'] ?? null,
                'location_id' => $emp['location_id'] ?? null,
                'position' => ['pos_name' => $emp['position']['pos_name'] ?? '-'],
                'division' => ['div_name' => $emp['division']['div_name'] ?? '-'],
                'location' => ['location_name' => $emp['location']['location_name'] ?? '-'],
                'mitra' => ['mitra_name' => $emp['mitra']['mitra_name'] ?? '-'],
                'user' => [
                    'role' => $emp['user']['role'] ?? 'employee',
                    'is_active' => $emp['user']['is_active'] ?? false
                ]
            ];
        }, $rawEmployees);

        // Fetch Master Data for Forms
        $resDeps = Http::withToken($token)->get("http://localhost:3000/api/master/departments");
        $deps = $resDeps->successful() ? $resDeps->json() : [];

        $resDivs = Http::withToken($token)->get("http://localhost:3000/api/master/divisions");
        $divs = $resDivs->successful() ? $resDivs->json() : [];

        $resLocations = Http::withToken($token)->get("http://localhost:3000/api/master/locations");
        $locations = $resLocations->successful() ? $resLocations->json() : [];

        $resPos = Http::withToken($token)->get("http://localhost:3000/api/master/positions");
        $positions = $resPos->successful() ? $resPos->json() : [];

        $resMitras = Http::withToken($token)->get("http://localhost:3000/api/master/mitras");
        $mitras = $resMitras->successful() ? $resMitras->json() : [];

        return view('employees.index', compact('employees', 'deps', 'divs', 'locations', 'positions', 'mitras'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        // Automate password: P4ssw0rd4ria + nrp
        $data['password'] = "P4ssw0rd4ria" . $request->nrp;

        $response = Http::withHeaders($this->getHeaders())->post($this->baseUrl, $data);
        
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Karyawan berhasil didaftarkan! Password: ' . $data['password']);
        }

        
        $errorMsg = 'Gagal mendaftarkan karyawan.';
        if ($response->json() && isset($response->json()['error'])) {
            $errorMsg = $response->json()['error'];
        }
        
        return redirect()->back()->withErrors(['error' => $errorMsg]);
    }

    public function update(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/{$id}", $request->all());
        
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Data Karyawan berhasil diperbarui!');
        }

        // Detailed logging for failed updates
        \Log::error('[FRONTEND ERROR] Gagal memperbarui karyawan', [
            'nrp' => $id,
            'status' => $response->status(),
            'error_body' => $response->json(),
            'request_data' => $request->except(['_token', 'password'])
        ]);
        
        $errorMsg = 'Gagal memperbarui karyawan.';
        if ($response->json() && isset($response->json()['error'])) {
            $errorMsg .= ' ' . $response->json()['error'];
        }
        
        return redirect()->back()->withErrors(['error' => $errorMsg]);
    }

    public function destroy($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/{$id}");
        
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Karyawan berhasil dihapus sistem!');
        }
        
        $errorMsg = 'Gagal menghapus karyawan.';
        if ($response->json() && isset($response->json()['error'])) {
            $errorMsg = $response->json()['error'];
        }
        
        return redirect()->back()->withErrors(['error' => $errorMsg]);
    }
}

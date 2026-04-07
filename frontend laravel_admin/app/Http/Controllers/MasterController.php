<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MasterController extends Controller
{
    private $baseUrl = 'http://localhost:3000/api/master';

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . Session::get('api_token'),
            'Accept' => 'application/json',
        ];
    }

    // ==========================================
    // SHIFTS
    // ==========================================
    public function shifts()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/shifts");
        $shifts = $response->successful() ? $response->json() : [];
        
        return view('master.shifts', compact('shifts'));
    }

    public function storeShift(Request $request)
    {
        $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/shifts", $request->all());
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Shift berhasil ditambahkan!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal menambah shift: ' . $response->body()]);
    }

    public function updateShift(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/shifts/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Shift berhasil diperbarui!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal memperbarui shift: ' . $response->body()]);
    }

    public function destroyShift($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/shifts/{$id}");
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Shift berhasil dihapus permanen!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal menghapus shift: ' . $response->body()]);
    }

    // ==========================================
    // DEPARTMENTS
    // ==========================================
    public function departments()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/departments");
        $departments = $response->successful() ? $response->json() : [];
        
        return view('master.departments', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/departments", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Departemen berhasil ditambahkan!')
            : redirect()->back()->withErrors(['error' => 'Gagal menambah departemen: ' . $response->body()]);
    }

    public function updateDepartment(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/departments/{$id}", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Departemen berhasil diperbarui!')
            : redirect()->back()->withErrors(['error' => 'Gagal memperbarui departemen: ' . $response->body()]);
    }

    public function destroyDepartment($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/departments/{$id}");
        return $response->successful() 
            ? redirect()->back()->with('success', 'Departemen berhasil dihapus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus departemen: ' . $response->body()]);
    }

    public function divisions()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/divisions");
        $divisions = $response->successful() ? $response->json() : [];

        $resDeps = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/departments");
        $departments = $resDeps->successful() ? $resDeps->json() : [];
        
        return view('master.divisions', compact('divisions', 'departments'));
    }

    public function storeDivision(Request $request)
    {
        $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/divisions", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Divisi berhasil ditambahkan!')
            : redirect()->back()->withErrors(['error' => 'Gagal menambah divisi: ' . $response->body()]);
    }


    public function updateDivision(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/divisions/{$id}", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Divisi berhasil diperbarui!')
            : redirect()->back()->withErrors(['error' => 'Gagal memperbarui divisi: ' . $response->body()]);
    }

    public function destroyDivision($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/divisions/{$id}");
        return $response->successful() 
            ? redirect()->back()->with('success', 'Divisi berhasil dihapus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus divisi: ' . $response->body()]);
    }

    // ==========================================
    // POSITIONS
    // ==========================================
    public function positions()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/positions");
        $positions = $response->successful() ? $response->json() : [];
        
        return view('master.positions', compact('positions'));
    }

    public function storePosition(Request $request)
    {
        $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/positions", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Jabatan berhasil ditambahkan!')
            : redirect()->back()->withErrors(['error' => 'Gagal menambah jabatan: ' . $response->body()]);
    }

    public function updatePosition(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/positions/{$id}", $request->all());
        return $response->successful() 
            ? redirect()->back()->with('success', 'Jabatan berhasil diperbarui!')
            : redirect()->back()->withErrors(['error' => 'Gagal memperbarui jabatan: ' . $response->body()]);
    }

    public function destroyPosition($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/positions/{$id}");
        return $response->successful() 
            ? redirect()->back()->with('success', 'Jabatan berhasil dihapus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus jabatan: ' . $response->body()]);
    }

    // ==========================================
    // LOKASI KERJA (GEOFENCE SITES)
    // ==========================================
    public function locations()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/locations");
        $locations = $response->successful() ? $response->json() : [];
        
        return view('master.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/locations", $request->all());
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Lokasi Kerja berhasil ditambahkan!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal menambah lokasi: ' . $response->body()]);
    }

    public function updateLocation(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())->put("{$this->baseUrl}/locations/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Data Lokasi berhasil diperbarui!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal memperbarui lokasi: ' . $response->body()]);
    }

    public function destroyLocation($id)
    {
        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/locations/{$id}");
        if ($response->successful()) {
            return redirect()->back()->with('success', 'Lokasi Kerja berhasil dihapus!');
        }
        return redirect()->back()->withErrors(['error' => 'Gagal menghapus lokasi: ' . $response->body()]);
    }

}

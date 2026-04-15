<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MasterController extends Controller
{
    private $baseUrl;

    public function __construct()
    {
        // PINDAH KE 3001 untuk menghindari Server Hantu
        $host = trim(env('API_URL', 'http://localhost:3000'));
        $host = rtrim($host, '/');
        $this->baseUrl = $host . '/api/master';
        
        \Log::info("MasterController Initialized. Calling Backend at: " . $this->baseUrl);
    }

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

    public function bulkDestroyShifts(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada shift yang dipilih.']);
        }

        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/shifts/bulk", ['ids' => $ids]);
        return $response->successful() 
            ? redirect()->back()->with('success', count($ids) . ' Shift berhasil dihapus sekaligus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus shift massal: ' . ($response->json()['error'] ?? $response->body())]);
    }



    // ==========================================
    // DEPARTMENTS
    // ==========================================
    public function departments()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/departments");
        $departments = $response->successful() ? $response->json() : [];

        $resDivs = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/divisions");
        $divisions = $resDivs->successful() ? $resDivs->json() : [];
        
        return view('master.departments', compact('departments', 'divisions'));
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

    public function bulkDestroyDepartments(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada departemen yang dipilih.']);
        }

        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/departments/bulk", ['ids' => $ids]);
        return $response->successful() 
            ? redirect()->back()->with('success', count($ids) . ' Departemen berhasil dihapus sekaligus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus departemen massal: ' . $response->body()]);
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

    public function bulkDestroyDivisions(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada divisi yang dipilih.']);
        }

        $response = Http::withHeaders($this->getHeaders())->delete("{$this->baseUrl}/divisions/bulk", ['ids' => $ids]);
        return $response->successful() 
            ? redirect()->back()->with('success', count($ids) . ' Divisi berhasil dihapus sekaligus!')
            : redirect()->back()->withErrors(['error' => 'Gagal menghapus divisi massal: ' . $response->body()]);
    }



    // ==========================================
    // POSITIONS
    // ==========================================
    public function positions()
    {
        if (!Session::has('api_token')) return redirect('/login');
        
        $response = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/positions");
        $positions = $response->successful() ? $response->json() : [];

        $resLocs = Http::withHeaders($this->getHeaders())->get("{$this->baseUrl}/locations");
        $locations = $resLocs->successful() ? $resLocs->json() : [];
        
        return view('master.positions', compact('positions', 'locations'));
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

    public function bulkDestroyPositions(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada jabatan yang dipilih.']);
        }

        $response = Http::withHeaders($this->getHeaders())
            ->send('DELETE', "{$this->baseUrl}/positions/bulk", [
                'json' => ['ids' => $ids]
            ]);

        if ($response->successful()) {
            return redirect()->back()->with('success', $response->json()['message'] ?? 'Jabatan terpilih berhasil dihapus!');
        }

        return redirect()->back()->withErrors(['error' => 'Gagal menghapus jabatan terpilih: ' . $response->body()]);
    }





}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RosterController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        // Default to localhost:3000 if not set in config
        $this->apiUrl = config('api.base_url', 'http://localhost:3000/api');
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $token = session('api_token');

        // Fetch Employees
        $employeesRes = Http::withToken($token)->get("{$this->apiUrl}/employees");
        $employees = $employeesRes->successful() ? $employeesRes->json() : [];

        // Fetch Shifts
        $shiftsRes = Http::withToken($token)->get("{$this->apiUrl}/master/shifts");
        $shifts = $shiftsRes->successful() ? $shiftsRes->json() : [];

        // Fetch Rosters for selected month/year
        $rostersRes = Http::withToken($token)->get("{$this->apiUrl}/rosters", [
            'month' => $month,
            'year' => $year
        ]);
        $rosters = $rostersRes->successful() ? $rostersRes->json() : [];

        // Fetch Geofences (Lokasi Kerja)
        $locationsRes = Http::withToken($token)->get("{$this->apiUrl}/master/locations");
        $geofences = $locationsRes->successful() ? $locationsRes->json() : [];

        return view('rosters.index', compact('employees', 'shifts', 'rosters', 'month', 'year', 'geofences'));
    }


    public function exportCSV(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $token = session('api_token');

        // Fetch Data
        $emplRes = Http::withToken($token)->get("{$this->apiUrl}/employees");
        $employees = $emplRes->successful() ? $emplRes->json() : [];

        $rostersRes = Http::withToken($token)->get("{$this->apiUrl}/rosters", ['month' => $month, 'year' => $year]);
        $rosters = $rostersRes->successful() ? $rostersRes->json() : [];

        // Build Map
        $rosterMap = [];
        foreach($rosters as $r) {
            $day = (int)date('d', strtotime($r['date']));
            $code = $r['shift']['shift_code'] ?? 'OFF';
            $times = '';
            if ($code !== 'OFF' && isset($r['shift']['time_in_expected'])) {
                $tIn = date('H:i', strtotime($r['shift']['time_in_expected']));
                $tOut = date('H:i', strtotime($r['shift']['time_out_expected']));
                $times = " ($tIn-$tOut)";
            }
            $rosterMap[$r['nrp']][$day] = $code . $times;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $fileName = "Roster_{$year}_{$month}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NRP', 'Nama Karyawan'];
        for($d=1; $d<=$daysInMonth; $d++) { $columns[] = $d; }

        $callback = function() use($employees, $rosterMap, $daysInMonth, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // Excel BOM
            fputcsv($file, $columns, ';');
            foreach($employees as $emp) {
                $row = [$emp['nrp'], $emp['full_name']];
                for($d=1; $d<=$daysInMonth; $d++) {
                    $row[] = $rosterMap[$emp['nrp']][$d] ?? '-';
                }
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCSV(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);
        $token = session('api_token');

        // Fetch Shifts for mapping code to ID
        $shiftsRes = Http::withToken($token)->get("{$this->apiUrl}/master/shifts");
        $shifts = $shiftsRes->successful() ? $shiftsRes->json() : [];
        $shiftMap = [];
        foreach($shifts as $s) { $shiftMap[strtoupper($s['shift_code'])] = $s['shift_id']; }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 1000, ';'); // Assuming semicolon separator

        $importData = [];
        while(($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
            if(count($data) < 3) continue;
            
            $nrp = trim($data[0]);
            $date = trim($data[1]);
            $shiftCode = strtoupper(trim($data[2]));
            $loc = trim($data[3] ?? '');

            if(isset($shiftMap[$shiftCode]) || $shiftCode === 'OFF') {
                $importData[] = [
                    'nrp' => $nrp,
                    'date' => $date,
                    'shift_id' => $shiftMap[$shiftCode] ?? null,
                    'work_location' => $loc
                ];
            }
        }
        fclose($handle);

        if(!empty($importData)) {
            $response = Http::withToken($token)->post("{$this->apiUrl}/rosters/bulk", ['rosters' => $importData]);
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Import Roster berhasil: ' . count($importData) . ' baris diproses.');
            }
        }

        return redirect()->back()->withErrors(['error' => 'Gagal import roster atau data kosong.']);
    }

    public function store(Request $request)
    {
        $token = session('api_token');
        
        // Single or Bulk assignment based on multi_dates flag
        if ($request->has('multi_dates')) {
            $nrp = $request->nrp;
            $shift_id = $request->shift_id;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $dates = [];
            $current = strtotime($start_date);
            $last = strtotime($end_date);

            while ($current <= $last) {
                $dates[] = [
                    'nrp' => $nrp,
                    'date' => date('Y-m-d', $current),
                    'shift_id' => $shift_id ? (int)$shift_id : null,
                    'work_location' => $request->work_location
                ];
                $current = strtotime('+1 day', $current);
            }

            $response = Http::withToken($token)->post("{$this->apiUrl}/rosters/bulk", [
                'rosters' => $dates
            ]);
        } else {
            $response = Http::withToken($token)->post("{$this->apiUrl}/rosters", [
                'nrp' => $request->nrp,
                'date' => $request->date,
                'shift_id' => $request->shift_id ? (int)$request->shift_id : null,
                'work_location' => $request->work_location,
            ]);
        }


        if ($response->successful()) {
            return redirect()->back()->with('success', 'Roster berhasil disimpan');
        }

        return redirect()->back()->withErrors(['error' => 'Gagal menyimpan roster: ' . ($response->json()['error'] ?? 'Unknown error')]);
    }

    public function destroy($id)
    {
        $token = session('api_token');
        $response = Http::withToken($token)->delete("{$this->apiUrl}/rosters/{$id}");

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Roster berhasil dihapus');
        }

        return redirect()->back()->withErrors(['error' => 'Gagal menghapus roster']);
    }
}

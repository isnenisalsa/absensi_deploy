<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller
{
    public function historyAttendance(Request $request)
    {
        // Pastikan user sudah login
        if (!Session::has('api_token')) {
            return redirect('/login')->withErrors(['nrp' => 'Silakan login terlebih dahulu.']);
        }

        $token = Session::get('api_token');
        $query = http_build_query($request->all());
        
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/attendance/history?$query");
            $attendances = $response->successful() ? $response->json() : [];
            
            // Master data untuk dropdown
            $resDeps = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/master/departments");
            $deps = $resDeps->successful() ? $resDeps->json() : [];

            $resDivs = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/master/divisions");
            $divs = $resDivs->successful() ? $resDivs->json() : [];

            $resLocations = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/master/locations");
            $locations = $resLocations->successful() ? $resLocations->json() : [];


        } catch (\Exception $e) {
            $attendances = [];
            $deps = []; $divs = []; $locations = [];
        }
        
        // Default tanggal: awal bulan ini s/d hari ini
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        return view('report.history', compact('attendances', 'deps', 'divs', 'locations', 'startDate', 'endDate'));
    }

    public function exportExcel(Request $request)
    {
        if (!Session::has('api_token')) {
            return redirect('/login');
        }

        $token = Session::get('api_token');
        $query = http_build_query($request->all());
        try {
            // Ambil data terbaru dari Node.js untuk di export dengan filter
            $response = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/attendance/history?$query");
            $attendances = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $attendances = [];
        }

        $fileName = "Report_History_Attendance_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Attendance Date', 'Attendance Hour', 'NRP', 'Nama Lengkap', 
            'Posisi', 'Divisi', 'Trans', 'Lokasi', 'CP Location', 'Att Location'
        ];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM untuk kompabilitas Excel agar membaca karakter khusus dengan benar
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Gunakan separator titik koma (;) agar lebih natural terbuka berlajur di Excel Indonesia/Windows
            fputcsv($file, $columns, ';');
            
            foreach ($attendances as $row) {
                $waktuAbsen = isset($row['time_wita']) ? \Carbon\Carbon::parse($row['time_wita'])->format('H:i:s') : '-';
                $tanggalAbsen = isset($row['attendance_date']) ? \Carbon\Carbon::parse($row['attendance_date'])->format('Y-m-d') : '-';
                
                $isCheckIn = ($row['trans_type'] ?? '') === 'Check_in';
                $transLabel = $isCheckIn ? 'IN' : 'OUT';

                $namaKaryawan = $row['employee']['full_name'] ?? session('user.employee_data.full_name') ?? session('user.nrp') ?? '-';
                
                fputcsv($file, [
                    $tanggalAbsen,
                    $waktuAbsen,
                    $row['nrp'] ?? '-',
                    $namaKaryawan,
                    $row['employee']['position']['pos_name'] ?? '-',
                    $row['employee']['division']['div_name'] ?? '-',
                    $transLabel,
                    $row['work_location'] ?? 'WFO',
                    $row['cp_location'] ?? '-',
                    isset($row['att_latitude']) ? round($row['att_latitude'], 4) . ',' . round($row['att_longitude'], 4) : '-'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- GEOFENCE LOCATION MANAGEMENT ---
    public function geofence()
    {
        if (!Session::has('api_token')) return redirect('/login');

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(Session::get('api_token'))
                ->get('http://localhost:3000/api/master/locations');
            $locations = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $locations = [];
        }

        return view('report.geofence', compact('locations'));
    }

    public function updateGeofence(Request $request, $id)
    {
        if (!Session::has('api_token')) return redirect('/login');

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(Session::get('api_token'))
                ->put("http://localhost:3000/api/master/locations/{$id}", [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meters' => $request->radius_meters,
            ]);
            
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Lokasi target geofence berhasil diperbarui!');
            }
            return redirect()->back()->withErrors(['error' => 'Gagal mengubah geofence. (Node.js Response: ' . $response->body() . ')']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Koneksi ke backend terputus. API Node.js mati?']);
        }
    }

    public function storeGeofence(Request $request)
    {
        if (!Session::has('api_token')) return redirect('/login');

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(Session::get('api_token'))
                ->post("http://localhost:3000/api/master/locations", [
                'location_name' => $request->location_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius_meters' => $request->radius_meters,
            ]);
            
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Lokasi target geofence baru berhasil ditambahkan!');
            }
            return redirect()->back()->withErrors(['error' => 'Gagal menambah geofence. (Node.js Response: ' . $response->body() . ')']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Koneksi ke backend terputus. API Node.js mati?']);
        }
    }

    public function destroyGeofence($id)
    {
        if (!Session::has('api_token')) return redirect('/login');

        try {
            $response = \Illuminate\Support\Facades\Http::withToken(Session::get('api_token'))
                ->delete("http://localhost:3000/api/master/locations/{$id}");
            
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Lokasi geofence berhasil dihapus secara permanen!');
            }
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus geofence. (Node.js Response: ' . $response->body() . ')']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Koneksi ke backend terputus. API Node.js mati?']);
        }
    }

    // --- FTW REPORT ---
    public function ftw(Request $request)
    {
        if (!Session::has('api_token')) return redirect('/login');

        // Tarik data memakai HTTP API ke Node.js agar nyambung ke MySQL
        $token = Session::get('api_token');
        
        // Default ke hari ini jika tidak ada filter
        $filterDate = $request->query('filter_date', date('Y-m-d'));
        
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/attendance/ftw", [
                'filter_date' => $filterDate
            ]);
            $rawReports = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $rawReports = [];
            return redirect()->back()->withErrors(['error' => 'Koneksi ke backend FTW terputus. API Node.js mati?']);
        }

        // Terapkan evaluasi FTW Otomatis ke array dari response
        $totalFit = 0;
        $totalUnfit = 0;

        $reports = array_map(function($ftw) use (&$totalFit, &$totalUnfit) {
            
            $isGejala = filter_var($ftw['gejala_kesehatan'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isObat = filter_var($ftw['konsumsi_obat'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isMasalah = filter_var($ftw['punya_masalah'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $jam_tidur = strtoupper($ftw['jam_tidur_12_jam'] ?? '');
            $isFatigue = in_array($jam_tidur, ['KR 1 JAM', '2 JAM', '4 JAM']);
            
            $isUnfit = $isGejala || $isObat || $isMasalah || $isFatigue;
            $status = $isUnfit ? 'UNFIT' : 'FIT';
            
            if ($isUnfit) $totalUnfit++; else $totalFit++;

            $reasons = [];
            if ($isGejala) $reasons[] = 'Gejala/Sakit';
            if ($isObat) $reasons[] = 'Obat Kantuk';
            if ($isMasalah) $reasons[] = 'Masalah Personal';
            if ($isFatigue) $reasons[] = 'Kurang Tidur';
            
            // Return only needed fields
            return (object) [
                'nrp' => $ftw['nrp'],
                'full_name' => $ftw['employee']['full_name'] ?? '-',
                'ftw_date' => $ftw['ftw_date'] ?? '-',
                'jam_tidur_12_jam' => $ftw['jam_tidur_12_jam'] ?? '-',
                'jam_bangun' => $ftw['jam_bangun'] ?? '-',
                'gejala_kesehatan' => $isGejala,
                'konsumsi_obat' => $isObat,
                'unit_dioperasikan' => $ftw['unit_dioperasikan'] ?? '-',
                'calculated_status' => $status,
                'unfit_reasons' => !empty($reasons) ? implode(', ', $reasons) : '-',
                'created_at' => $ftw['created_at'] ?? '-',
            ];
        }, $rawReports);

        return view('report.ftw', compact('reports', 'totalFit', 'totalUnfit', 'filterDate'));
    }

    public function exportFtw(Request $request)
    {
        if (!Session::has('api_token')) return redirect('/login');
        $token = Session::get('api_token');
        $filterDate = $request->query('filter_date', date('Y-m-d'));
        
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)->get("http://localhost:3000/api/attendance/ftw", [
                'filter_date' => $filterDate
            ]);
            $rawReports = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal export FTW.']);
        }

        $fileName = "Report_FTW_{$filterDate}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NRP', 'Nama Karyawan', 'Tanggal FTW', 'Unit', 'Jam Tidur', 'Jam Bangun', 'Gejala', 'Obat', 'Masalah', 'Status', 'Alasan Unfit'];

        $callback = function() use($rawReports, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, (chr(0xEF) . chr(0xBB) . chr(0xBF))); // BOM
            fputcsv($file, $columns, ';');

            foreach ($rawReports as $ftw) {
                // Evaluasi status seperti di view
                $isGejala = filter_var($ftw['gejala_kesehatan'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $isObat = filter_var($ftw['konsumsi_obat'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $isMasalah = filter_var($ftw['punya_masalah'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $jam_tidur = strtoupper($ftw['jam_tidur_12_jam'] ?? '');
                $isFatigue = in_array($jam_tidur, ['KR 1 JAM', '2 JAM', '4 JAM']);
                
                $isUnfit = $isGejala || $isObat || $isMasalah || $isFatigue;
                $status = $isUnfit ? 'UNFIT' : 'FIT';

                $reasons = [];
                if ($isGejala) $reasons[] = 'Gejala/Sakit';
                if ($isObat) $reasons[] = 'Obat Kantuk';
                if ($isMasalah) $reasons[] = 'Masalah Personal';
                if ($isFatigue) $reasons[] = 'Kurang Tidur';

                fputcsv($file, [
                    $ftw['nrp'],
                    $ftw['employee']['full_name'] ?? '-',
                    $ftw['ftw_date'] ?? '-',
                    $ftw['unit_dioperasikan'] ?? '-',
                    $ftw['jam_tidur_12_jam'] ?? '-',
                    $ftw['jam_bangun'] ?? '-',
                    $isGejala ? 'YA' : 'TIDAK',
                    $isObat ? 'YA' : 'TIDAK',
                    $isMasalah ? 'YA' : 'TIDAK',
                    $status,
                    !empty($reasons) ? implode(', ', $reasons) : '-'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

@extends('layouts.app')

@section('title', 'Dashboard Monitoring')

@section('content')
<div class="px-6 py-8 md:px-10">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] font-[900] text-slate-800 tracking-tight leading-tight uppercase">Dashboard Overview</h1>
            <p class="text-[13px] text-slate-400 font-bold mt-1 tracking-wider uppercase">Monitor & Analytics Command Center</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm flex items-center">
                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse mr-3"></div>
                <span class="text-[12px] font-black text-slate-600 tracking-widest uppercase">Live Tracking: ON</span>
            </div>
            <button onclick="window.location.reload()" class="p-2.5 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 transition-all active:scale-95">
                <i class="fa-solid fa-rotate text-[14px]"></i>
            </button>
        </div>
    </div>

    <!-- 1. Summary Cards (6 in a row) -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-10">
        <!-- Total Karyawan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-user-group text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-blue-500 bg-blue-50 px-2 py-1 rounded-md uppercase tracking-tighter">Total Staff</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['total_employees'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Karyawan Terdaftar</p>
        </div>

        <!-- Hadir -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-user-check text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded-md uppercase tracking-tighter">On-Time</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['today_presence'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Hadir Hari Ini</p>
        </div>

        <!-- Tidak Hadir -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-user-xmark text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-rose-500 bg-rose-50 px-2 py-1 rounded-md uppercase tracking-tighter">Absent</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['not_present'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Belum Presensi</p>
        </div>

        <!-- Terlambat -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-stopwatch text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-amber-500 bg-amber-50 px-2 py-1 rounded-md uppercase tracking-tighter">Lateness</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['late_count'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Terlambat Masuk</p>
        </div>

        <!-- Outside Geofence -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-map-location-dot text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 bg-slate-50 px-2 py-1 rounded-md uppercase tracking-tighter">Violation</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['outside_geofence'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Diluar Geofence</p>
        </div>

        <!-- Mitra Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i class="fa-solid fa-handshake-angle text-[16px]"></i>
                </div>
                <span class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-md uppercase tracking-tighter">Partners</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-1 leading-none">{{ number_format($stats['total_mitras'] ?? 0) }}</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Mitra / Subcont</p>
        </div>
    </div>

    <!-- 2. Main Analytics Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        
        <!-- Left: Statistics Chart -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-slate-800 uppercase tracking-wider">Tren Absensi Mingguan</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Statistik Presensi 7 Hari Terakhir</p>
                </div>
                <div class="flex gap-2">
                    <div class="flex items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></div>
                        <span class="text-[10px] font-black text-slate-500 uppercase">Check-In</span>
                    </div>
                </div>
            </div>
            <div class="p-6 flex-1 min-h-[300px] relative">
                <canvas id="presenceChart"></canvas>
            </div>
        </div>

        <!-- Right: Alert Panel -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-[13px] font-black text-slate-800 uppercase tracking-wider">Notifikasi & Peringatan</h3>
                <span class="text-[10px] font-black text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full uppercase">Live</span>
            </div>
            <div class="p-4 flex flex-col gap-3 overflow-y-auto max-h-[350px]">
                
                @if($stats['late_count'] > 0)
                <div class="flex items-start p-3 bg-amber-50 rounded-2xl border border-amber-100/50 group hover:bg-amber-100 transition-colors">
                    <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-amber-500 shadow-sm shrink-0 mr-3">
                        <i class="fa-solid fa-clock text-[12px]"></i>
                    </div>
                    <div>
                        <p class="text-[12px] font-black text-slate-800 leading-tight">Peringatan Keterlambatan</p>
                        <p class="text-[11px] font-bold text-slate-500 mt-1 uppercase">{{ $stats['late_count'] }} karyawan terdeteksi terlambat masuk hari ini.</p>
                    </div>
                </div>
                @endif

                <div class="flex items-start p-3 bg-rose-50 rounded-2xl border border-rose-100/50 group hover:bg-rose-100 transition-colors">
                    <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-rose-500 shadow-sm shrink-0 mr-3">
                        <i class="fa-solid fa-triangle-exclamation text-[12px]"></i>
                    </div>
                    <div>
                        <p class="text-[12px] font-black text-slate-800 leading-tight">Isu Absensi</p>
                        <p class="text-[11px] font-bold text-slate-500 mt-1 uppercase">{{ $stats['not_present'] }} karyawan belum melakukan absensi harian.</p>
                    </div>
                </div>

                <div class="flex items-start p-3 bg-blue-50 rounded-2xl border border-blue-100/50 group hover:bg-blue-100 transition-colors">
                    <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-blue-500 shadow-sm shrink-0 mr-3">
                        <i class="fa-solid fa-info-circle text-[12px]"></i>
                    </div>
                    <div>
                        <p class="text-[12px] font-black text-slate-800 leading-tight">Update Sistem</p>
                        <p class="text-[11px] font-bold text-slate-500 mt-1 uppercase">Seluruh Master Data telah sinkron dengan server Node.js.</p>
                    </div>
                </div>

                @if($stats['late_count'] == 0 && $stats['not_present'] == 0)
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-check-double text-[32px]"></i>
                    </div>
                    <p class="text-[13px] font-black text-slate-800 uppercase tracking-widest">Sistem Normal</p>
                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Tidak ada anomali terdeteksi saat ini.</p>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- 3. Recent Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-10">
        <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-[15px] font-black text-slate-800 uppercase tracking-wider">Aktivitas Absensi Terbaru</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Akses log aktivitas 10 transaksi terakhir</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('report.history') }}" class="px-5 py-2.5 bg-slate-800 text-white rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all">Lihat Semua</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Karyawan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Jam Masuk</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi / Mitra</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Shift</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($stats['recent_presence'] ?? [] as $log)
                    <tr class="hover:bg-slate-50/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden shrink-0 border-2 border-white shadow-sm">
                                    <img src="{{ $log['employee']['photo_profile'] ?? 'https://ui-avatars.com/api/?name='.urlencode($log['employee']['full_name']).'&background=0052cc&color=fff&bold=true' }}" class="w-full h-full object-cover"/>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[13px] font-black text-slate-800 uppercase">{{ $log['employee']['full_name'] }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $log['employee']['department']['dept_name'] ?? 'DEPT N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-[13px] font-extrabold text-slate-600 tabular-nums">
                            {{ date('H:i:s', strtotime($log['time_wita'])) }} <span class="text-[10px] font-black text-slate-400">WITA</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ $log['trans_type'] == 'Check_in' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                {{ $log['trans_type'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-slate-700 uppercase">{{ $log['mitra_name'] ?? $log['employee']['mitra']['mitra_name'] ?? 'PAMA' }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $log['cp_location'] ?? 'Kantor Utama' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest border border-blue-100">
                                {{ $log['shift']['shift_code'] ?? 'NS' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="max-w-[200px] mx-auto text-slate-300">
                                <i class="fa-solid fa-inbox text-[48px] mb-3 opacity-20"></i>
                                <p class="text-[11px] font-black uppercase tracking-widest">Belum ada aktivitas presensi hari ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. System Summary Container -->
    <div class="mt-8">
        <h3 class="text-[15px] font-black text-slate-800 uppercase tracking-wider mb-5">Ringkasan Sistem</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 p-6 rounded-2xl border border-white/5 shadow-sm flex flex-col items-center justify-center text-center group hover:bg-slate-700 transition-colors">
                <p class="text-2xl font-black text-white leading-none mb-1">{{ $stats['master_summary']['departments'] ?? 0 }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Departemen</p>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-white/5 shadow-sm flex flex-col items-center justify-center text-center group hover:bg-slate-700 transition-colors">
                <p class="text-2xl font-black text-white leading-none mb-1">{{ $stats['master_summary']['divisions'] ?? 0 }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Divisi</p>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-white/5 shadow-sm flex flex-col items-center justify-center text-center group hover:bg-slate-700 transition-colors">
                <p class="text-2xl font-black text-white leading-none mb-1">{{ $stats['master_summary']['positions'] ?? 0 }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Jabatan</p>
            </div>
            <div class="bg-slate-800 p-6 rounded-2xl border border-white/5 shadow-sm flex flex-col items-center justify-center text-center group hover:bg-slate-700 transition-colors">
                <p class="text-2xl font-black text-white leading-none mb-1">{{ $stats['master_summary']['shifts'] ?? 0 }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Shift Aktif</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('presenceChart').getContext('2d');
        
        // Data from backend trend
        const trendData = @json($stats['trend'] ?? []);
        const labels = trendData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }).toUpperCase();
        });
        const values = trendData.map(item => item.count);

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kehadiran',
                    data: values,
                    borderColor: '#2563eb',
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { family: 'Inter', weight: 'bold', size: 12 },
                        bodyFont: { family: 'Inter', size: 12 },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { 
                            font: { family: 'Inter', weight: 'bold', size: 10 },
                            color: '#94a3b8',
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { family: 'Inter', weight: 'bold', size: 10 },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

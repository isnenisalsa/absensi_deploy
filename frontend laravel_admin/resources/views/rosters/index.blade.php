@extends('layouts.app')

@section('title', 'Manajemen Roster Kerja')

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Premium Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-user-group text-[12px]"></i> Operasional
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Roster Shift</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-calendar-days text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Roster Kerja</h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('rosters.index') }}" method="GET" class="flex items-center gap-2">
                <select name="month" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                    @for($y=date('Y')-1; $y<=date('Y')+2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all text-slate-600">
                    <i class="fa-solid fa-arrows-rotate text-[14px]"></i>
                </button>
            </form>
            
            <div class="h-10 w-[1px] bg-slate-200 mx-1 hidden md:block"></div>

            <div class="flex items-center gap-2">
                <a href="{{ route('rosters.export', ['month' => $month, 'year' => $year]) }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 hover:text-blue-600 font-extrabold text-[11px] rounded-xl transition-all flex items-center gap-2 shadow-sm uppercase tracking-wider">
                    <i class="fa-solid fa-file-export"></i> EXPORT
                </a>
            </div>

            <button onclick="openBulkModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-[11px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2 ml-2">
                <i class="fa-solid fa-calendar-plus"></i> PLOT JADWAL
            </button>
        </div>
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Shift Legend -->
    <div class="flex flex-wrap gap-4 mb-6">
        @foreach($shifts as $s)
        <div class="bg-white border border-slate-100 rounded-xl px-4 py-2 shadow-sm flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[11px] font-black text-blue-700 border border-blue-100">
                {{ $s['shift_code'] }}
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Jam Kerja</div>
                <div class="text-[11px] font-bold text-slate-700 leading-none">
                    {{ date('H:i', strtotime($s['time_in_expected'])) }} - {{ date('H:i', strtotime($s['time_out_expected'])) }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Roster Grid -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            @php
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                $rosterMap = [];
                foreach($rosters as $r) {
                    $day = (int)date('d', strtotime($r['date']));
                    $rosterMap[$r['nrp']][$day] = [
                        'id' => $r['id'],
                        'shift_code' => $r['shift']['shift_code'] ?? 'OFF',
                        'shift_id' => $r['shift_id'],
                        'work_location' => $r['work_location'] ?? '',
                        'time_in' => isset($r['shift']['time_in_expected']) ? date('H:i', strtotime($r['shift']['time_in_expected'])) : '',
                        'time_out' => isset($r['shift']['time_out_expected']) ? date('H:i', strtotime($r['shift']['time_out_expected'])) : ''
                    ];
                }
            @endphp
            <table class="w-full text-left border-collapse table-fixed min-w-[1500px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-4 px-5 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest w-[200px] sticky left-0 bg-slate-50 z-10 border-r border-slate-200">Nama Karyawan</th>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dateStr = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $isWeekend = in_array(date('N', strtotime($dateStr)), [6, 7]);
                            @endphp
                            <th class="py-3 px-1 text-[10px] font-extrabold text-center uppercase tracking-tighter text-slate-400 border-r border-slate-100 w-[60px]">
                                {{ $d }}
                            </th>
                        @endfor
                        <th class="py-4 px-3 text-[10px] font-black text-blue-600 bg-blue-50 border-l border-blue-100 uppercase tracking-widest text-center w-[80px]">TOT ON</th>
                        <th class="py-4 px-3 text-[10px] font-black text-rose-600 bg-rose-50 border-l border-rose-100 uppercase tracking-widest text-center w-[80px]">TOT OFF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                    @php
                        $totOn = 0;
                        $totOff = 0;
                    @endphp
                    <tr class="hover:bg-blue-50/20 transition-colors group">
                        <td class="py-3 px-5 sticky left-0 bg-white group-hover:bg-blue-50/50 z-10 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                            <div class="text-[12px] font-bold text-slate-800 truncate">{{ $emp['full_name'] }}</div>
                            <div class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $emp['nrp'] }}</div>
                        </td>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $rData = $rosterMap[$emp['nrp']][$d] ?? null;
                                $isWeekend = in_array(date('N', strtotime("$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT))), [6, 7]);
                                if ($rData) {
                                    if ($rData['shift_code'] == 'OFF') { $totOff++; } else { $totOn++; }
                                } else {
                                    $totOff++;
                                }
                            @endphp
                            <td class="py-1 px-0.5 text-center border-r border-slate-100">
                                <button type="button" 
                                        onclick="openSingleModal('{{ $emp['nrp'] }}', '{{ $emp['full_name'] }}', '{{ $year }}-{{ $month }}-{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}', '{{ $rData['work_location'] ?? '' }}', '{{ $rData['shift_id'] ?? '' }}')"
                                        class="w-full min-h-[50px] flex flex-col items-center justify-center p-1 rounded-lg transition-all border border-transparent hover:border-blue-200
                                        {{ $rData ? ($rData['shift_code'] == 'OFF' ? 'bg-red-50/30' : 'bg-blue-50/50') : 'bg-slate-50/20' }}">
                                    @if($rData)
                                        <span class="text-[10px] font-black {{ $rData['shift_code'] == 'OFF' ? 'text-rose-500' : 'text-blue-700' }}">{{ $rData['shift_code'] }}</span>
                                        @if($rData['shift_code'] != 'OFF')
                                            <span class="text-[8px] font-bold text-slate-400 mt-0.5 leading-none">{{ $rData['time_in'] }}-{{ $rData['time_out'] }}</span>
                                        @endif
                                        @if($rData['work_location'])
                                            <span class="text-[7px] font-extrabold text-blue-600 truncate max-w-full px-1 bg-white border border-blue-100 rounded mt-1 shadow-sm">{{ $rData['work_location'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-[10px] font-black text-rose-300">OFF</span>
                                    @endif
                                </button>
                            </td>
                        @endfor
                        <td class="py-2 px-3 text-center bg-blue-50/30 border-l border-blue-100">
                            <span class="text-[12px] font-black text-blue-700">{{ $totOn }}</span>
                        </td>
                        <td class="py-2 px-3 text-center bg-rose-50/30 border-l border-rose-100">
                            <span class="text-[12px] font-black text-rose-700">{{ $totOff }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 3 }}" class="py-20 text-center text-slate-400 font-bold text-sm">
                            Tidak ada data karyawan ditemukan. Harap pastikan Master Karyawan sudah terisi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bulk/Batch Assignment -->
<div id="bulkModalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-indigo-500"></i>
                <span>Plot Jadwal Massal</span>
            </h3>
            <button type="button" onclick="closeBulkModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-[18px]"></i>
            </button>
        </div>
        
        <form action="{{ route('rosters.store') }}" method="POST" class="p-8">
            @csrf
            <input type="hidden" name="multi_dates" value="1">
            
            <div class="space-y-5">
                <!-- Employee Selection -->
                <div>
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Pilih Karyawan</label>
                    <select name="nrp" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-[12px] text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer" required>
                        <option value="">-- Pilih --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp['nrp'] }}">{{ $emp['full_name'] }} ({{ $emp['nrp'] }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $year }}-{{ $month }}-01" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-[12px] font-bold text-slate-800 outline-none" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $year }}-{{ $month }}-{{ $daysInMonth }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-[12px] font-bold text-slate-800 outline-none" required>
                    </div>
                </div>

                <!-- Work Location -->
                <div>
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Lokasi Kerja (Geofence)</label>
                    <select name="work_location" class="searchable-select w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-[12px] font-bold text-slate-800 outline-none focus:bg-white focus:border-blue-500 transition-all cursor-pointer">
                        <option value="">- Silahkan pilih Lokasi -</option>
                        @foreach($geofences as $g)
                        <option value="{{ $g['location_name'] }}">{{ $g['location_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift Selection -->
                <div>
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Pilih Shift</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($shifts as $s)
                        <label class="relative group cursor-pointer">
                            <input type="radio" name="shift_id" value="{{ $s['shift_id'] }}" id="radio-bulk-{{ $s['shift_id'] }}" class="peer hidden" required>
                            <div class="p-2 border border-slate-200 rounded-xl text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all hover:bg-slate-50">
                                <div class="text-[11px] font-black uppercase">{{ $s['shift_code'] }}</div>
                                <div class="text-[8px] font-bold opacity-70">{{ date('H:i', strtotime($s['time_in_expected'])) }} - {{ date('H:i', strtotime($s['time_out_expected'])) }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-[900] text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> SIMPAN JADWAL MASSAL
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Single Quick Update -->
<div id="singleModalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeSingleModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="p-6">
            <div id="singleDateLabel" class="text-[10px] font-black text-blue-600 uppercase tracking-widest text-center mb-1"></div>
            <div id="singleEmpName" class="text-[14px] font-[900] text-slate-800 truncate text-center mb-6"></div>
            
            <form action="{{ route('rosters.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="nrp" id="singleNrp">
                <input type="hidden" name="date" id="singleDate">
                
                <div>
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1 block">Lokasi Kerja (Geofence)</label>
                    <select name="work_location" id="singleLocationDisplay" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-[12px] font-bold text-slate-800 outline-none">
                        <option value="">- Silahkan pilih Lokasi -</option>
                        @foreach($geofences as $g)
                        <option value="{{ $g['location_name'] }}">{{ $g['location_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1 block">Silahkan Pilih Shift</label>
                    <div class="relative group">
                        <select name="shift_id" id="singleShiftSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3.5 px-4 text-[13px] font-bold text-slate-800 outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all cursor-pointer appearance-none shadow-sm">
                            <option value="">-- LIBUR (OFF) --</option>
                            @foreach($shifts as $s)
                            <option value="{{ $s['shift_id'] }}">
                                {{ $s['shift_code'] }} ({{ date('H:i', strtotime($s['time_in_expected'])) }} - {{ date('H:i', strtotime($s['time_out_expected'])) }})
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-[900] text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    const bulkModal = document.getElementById('bulkModalContainer');
    const singleModal = document.getElementById('singleModalContainer');
    const importModal = document.getElementById('importModalContainer');

    function openBulkModal() {
        bulkModal.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function closeBulkModal() {
        bulkModal.classList.add('hidden');
        toggleSidebarBlur(false);
    }

    function openImportModal() {
        importModal.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function closeImportModal() {
        importModal.classList.add('hidden');
        toggleSidebarBlur(false);
    }

    function openSingleModal(nrp, name, date, currentWorkLocation, currentShiftId) {
        document.getElementById('singleNrp').value = nrp;
        document.getElementById('singleDate').value = date;
        document.getElementById('singleEmpName').textContent = name;
        document.getElementById('singleDateLabel').textContent = new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
        
        const locSelect = document.getElementById('singleLocationDisplay');
        locSelect.value = currentWorkLocation || '';
        
        const shiftSelect = document.getElementById('singleShiftSelect');
        shiftSelect.value = currentShiftId || '';

        singleModal.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function closeSingleModal() {
        singleModal.classList.add('hidden');
        toggleSidebarBlur(false);
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeBulkModal();
            closeSingleModal();
        }
    });
</script>
@endpush

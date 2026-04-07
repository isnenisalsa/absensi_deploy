@extends('layouts.app')

@section('title', 'Report History Attendance')

@section('content')
<div class="px-6 py-4 md:px-10 md:py-6 w-full max-w-full mx-auto animate-fade-in-up space-y-6">
    
    <!-- Top Breadcrumbs & Title -->
    <div class="flex flex-col gap-1.5 pt-2">
        <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400">
            <span class="hover:text-blue-500 cursor-pointer">Report</span>
            <span class="text-slate-300 font-normal">›</span>
            <span class="text-slate-400">Report History Attendance</span>
        </div>
        <h1 class="text-[28px] font-black text-slate-800 tracking-tight leading-none">Report History Attendance</h1>
    </div>

    <!-- Filter Parameter Card -->
    <div class="bg-white rounded-xl shadow-lg shadow-slate-200/20 border border-slate-100 overflow-hidden min-w-[800px]">
        <!-- Solid Blue Header -->
        <div class="bg-[#1e63d3] px-6 py-4 flex items-center gap-3">
            <i class="fa-solid fa-filter text-white text-[14px]"></i>
            <span class="text-white font-[900] text-[12px] uppercase tracking-wider">Filter Parameter</span>
        </div>
        
        <form action="{{ route('report.history') }}" method="GET" class="p-8">
            <!-- Row 1: Forced Horizontal Selects -->
            <div class="flex flex-row flex-nowrap gap-6">
                @php
                    $filters = [
                        ['label' => 'Divisi', 'name' => 'divisi', 'data' => $divs, 'key' => 'div_id', 'val' => 'div_name'],
                        ['label' => 'Dept', 'name' => 'dept', 'data' => $deps, 'key' => 'dept_id', 'val' => 'dept_name'],
                        ['label' => 'Lokasi Kerja', 'name' => 'lokasi', 'data' => $locations, 'key' => 'location_id', 'val' => 'location_name'],
                    ];
                @endphp

                @foreach($filters as $f)
                <div class="flex-1 min-w-0 flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 whitespace-nowrap">{{ $f['label'] }}</label>
                    <select name="{{ $f['name'] }}" class="w-full h-11 bg-[#f8fafc] border border-slate-200 rounded-lg px-4 text-[12px] font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none">
                        <option value="ALL">ALL</option>
                        @foreach($f['data'] as $item)
                        <option value="{{ $item[$f['key']] }}" {{ request($f['name']) == $item[$f['key']] ? 'selected' : '' }}>{{ $item[$f['val']] }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>

            <!-- Row 2: Dates and Buttons -->
            <div class="flex flex-row flex-nowrap items-end gap-6 mt-6">
                <!-- Start Date -->
                <div class="w-1/4 min-w-[200px] flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 whitespace-nowrap">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}" class="w-full h-11 bg-[#f8fafc] border border-slate-200 rounded-lg px-4 text-[12px] font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none"/>
                </div>
                
                <!-- End Date -->
                <div class="w-1/4 min-w-[200px] flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 whitespace-nowrap">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}" class="w-full h-11 bg-[#f8fafc] border border-slate-200 rounded-lg px-4 text-[12px] font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none"/>
                </div>

                <!-- Spacer -->
                <div class="flex-1"></div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <a href="{{ route('report.history') }}" class="px-10 h-11 flex items-center justify-center bg-white border border-slate-200 hover:bg-slate-50 text-slate-500 font-[900] text-[12px] rounded-lg transition-all uppercase tracking-widest shadow-sm">
                        RESET
                    </a>
                    <button type="submit" class="px-12 h-11 bg-[#1e63d3] hover:bg-[#1652b1] text-white font-[900] text-[12px] rounded-lg shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest active:scale-95">
                        VIEW
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Attendance Data Records Card -->
    <div class="bg-white rounded-xl shadow-lg shadow-slate-200/20 border border-slate-100 overflow-hidden min-w-[800px]">
        <div class="px-8 py-5 flex items-center justify-between border-b border-slate-50">
            <h2 class="text-[15px] font-[900] text-slate-800 tracking-tight">Attendance Data Records</h2>
            
            <a href="{{ route('report.history.export', request()->all()) }}" class="px-6 py-2.5 bg-[#107c41] hover:bg-[#0d6b38] text-white font-black text-[11px] rounded-lg shadow-lg shadow-emerald-500/10 transition-all uppercase tracking-widest flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-file-excel text-[13px]"></i> Export to Excel
            </a>
        </div>

        <!-- Table Container -->
        <div id="history-table-container" data-react-component="history-table" data-props="{{ json_encode(['data' => $attendances]) }}">
            <div class="w-full h-[400px] flex items-center justify-center bg-slate-50/10">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-8 h-8 border-2 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
                    <span class="text-[10px] text-slate-400 font-extrabold tracking-widest uppercase">Syncing Records with Server...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

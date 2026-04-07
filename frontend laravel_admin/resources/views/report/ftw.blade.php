@extends('layouts.app')

@section('title', 'Laporan Fit To Work (FTW)')

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-chart-line text-[12px]"></i> Report
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Laporan FTW</span>
            </div>
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-heart-pulse text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Laporan Fit To Work</h1>
            </div>
        </div>
        
        <!-- Summary Dashboard Cards -->
        <div class="flex gap-4 w-full md:w-auto mt-4 md:mt-0">
            <div class="flex-1 md:flex-none bg-white rounded-xl shadow-lg border border-slate-100 p-4 min-w-[140px]">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1 block">Total Laporan</span>
                <h2 class="text-2xl font-black text-slate-800">{{ count($reports) }} <span class="text-[11px] text-slate-400">orang</span></h2>
            </div>
            <div class="flex-1 md:flex-none bg-emerald-50 rounded-xl shadow-lg border border-emerald-100 p-4 min-w-[140px]">
                <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider mb-1 block">Karyawan FIT</span>
                <h2 class="text-2xl font-black text-emerald-700">{{ $totalFit }} <span class="text-[11px] text-emerald-600">orang</span></h2>
            </div>
            <div class="flex-1 md:flex-none bg-red-50 rounded-xl shadow-lg border border-red-100 p-4 min-w-[140px]">
                <span class="text-[10px] font-extrabold text-red-600 uppercase tracking-wider mb-1 block">Karyawan UNFIT</span>
                <h2 class="text-2xl font-black text-red-700">{{ $totalUnfit }} <span class="text-[11px] text-red-600">orang</span></h2>
            </div>
        </div>
    </div>

    <!-- Filter Block -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-visible mb-10">
        <form action="{{ route('report.ftw') }}" method="GET" class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                <div class="flex flex-col">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">TANGGAL MONITORING</label>
                    <input type="date" name="filter_date" value="{{ $filterDate }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-[13px] font-bold outline-none focus:bg-white focus:border-blue-500 transition-all"/>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="flex-1 md:flex-none px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> TAMPILKAN
                    </button>
                    <a href="{{ route('report.ftw.export', ['filter_date' => $filterDate]) }}" class="flex-1 md:flex-none px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-emerald-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-excel"></i> EXPORT EXCEL
                    </a>
                    <a href="{{ route('report.ftw') }}" class="flex-1 md:flex-none px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-[12px] rounded-xl transition-all uppercase tracking-widest text-center">
                        RESET
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- React Table Mount Point -->
    <div 
        id="ftw-table-container" 
        data-react-component="ftw-table" 
        data-props="{{ json_encode(['data' => $reports]) }}"
    >
        <div class="w-full h-64 bg-white rounded-2xl border border-slate-100 flex items-center justify-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                <span class="text-slate-400 font-bold text-sm tracking-widest uppercase">Loading FTW Data...</span>
            </div>
        </div>
    </div>
</div>
@endsection

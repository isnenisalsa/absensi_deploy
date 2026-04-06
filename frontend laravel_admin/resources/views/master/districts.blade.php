@extends('layouts.app')

@section('title', 'Master Distrik')

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1200px] mx-auto animate-fade-in-up">
    
    <!-- Premium Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-database text-[12px]"></i> Master Data
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Master Distrik</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-map-location-dot text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Distrik</h1>
            </div>
        </div>

        <button onclick="openModal('add')" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2 group">
            <i class="fa-solid fa-circle-plus group-hover:rotate-90 transition-transform duration-300"></i>
            <span>Tambah Distrik</span>
        </button>
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Data Card -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest w-[80px]">ID</th>
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest">Nama Distrik</th>
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest text-center w-[150px]">Status</th>
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest text-right w-[150px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($districts as $d)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="py-4 px-6">
                            <span class="text-[13px] font-black text-slate-400">#{{ $d['dist_id'] }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-[14px] font-extrabold text-slate-800 uppercase tracking-tight">{{ $d['dist_name'] }}</div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-lg border border-emerald-100">ACTIVE</span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="openModal('edit', {{ json_encode($d) }})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-pen-to-square text-[14px]"></i>
                                </button>
                                <form action="{{ route('master.districts.destroy', $d['dist_id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus distrik ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                        <i class="fa-solid fa-trash-can text-[14px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-map-location text-slate-200 text-2xl"></i>
                                </div>
                                <div class="text-slate-400 font-bold text-sm">Belum ada data distrik.</div>
                                <button onclick="openModal('add')" class="mt-4 text-blue-600 font-black text-xs hover:underline uppercase tracking-widest">Tambah Sekarang</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="modalDistrict" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modalTitle" class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i class="fa-solid fa-circle-plus text-indigo-500"></i>
                <span>Tambah Distrik Baru</span>
            </h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-[18px]"></i>
            </button>
        </div>
        
        <form id="districtForm" method="POST" class="p-8">
            @csrf
            <div id="methodField"></div>
            
            <div class="space-y-6">
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Nama Distrik</label>
                    <input type="text" name="dist_name" id="dist_name" placeholder="Contoh: ARIA, KCM, dll" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] font-bold text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all" required>
                </div>
            </div>

            <div class="mt-10 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 py-3.5 bg-white border border-slate-200 text-slate-500 font-extrabold text-[11px] rounded-xl hover:bg-slate-50 transition-all uppercase tracking-widest">Batal</button>
                <button type="submit" class="flex-[2] py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-[11px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('modalDistrict');
    const form = document.getElementById('districtForm');
    const title = document.getElementById('modalTitle');
    const inputName = document.getElementById('dist_name');
    const methodField = document.getElementById('methodField');

    function openModal(mode, data = null) {
        if (mode === 'add') {
            title.innerHTML = '<i class="fa-solid fa-circle-plus text-indigo-500 text-lg"></i> Tambah Distrik Baru';
            form.action = "{{ route('master.districts.store') }}";
            inputName.value = '';
            methodField.innerHTML = '';
        } else {
            title.innerHTML = '<i class="fa-solid fa-pen-to-square text-blue-500 text-lg"></i> Edit Data Distrik';
            form.action = `/master/districts/${data.dist_id}`;
            inputName.value = data.dist_name;
            methodField.innerHTML = '@method("PUT")';
        }
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    // Close on ESC
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush

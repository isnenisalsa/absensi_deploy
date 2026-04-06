@extends('layouts.app')

@section('title', 'Master Divisi')

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
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Master Divisi</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-folder-tree text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Divisi</h1>
            </div>
        </div>

        <button onclick="openModal('add')" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2 group">
            <i class="fa-solid fa-circle-plus group-hover:rotate-90 transition-transform duration-300"></i>
            <span>Tambah Divisi</span>
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
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest">Nama Divisi</th>
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest">Department Induk</th>
                        <th class="py-5 px-6 text-[11px] font-black text-slate-500 uppercase tracking-widest text-right w-[150px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($divisions as $d)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="py-4 px-6">
                            <span class="text-[13px] font-black text-slate-400">#{{ $d['div_id'] }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-[14px] font-extrabold text-slate-800 uppercase tracking-tight">{{ $d['div_name'] }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span class="text-[13px] font-bold text-slate-600 uppercase">{{ $d['department']['dept_name'] ?? 'Undefined' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="openModal('edit', {{ json_encode($d) }})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-pen-to-square text-[14px]"></i>
                                </button>
                                <form action="{{ route('master.divisions.destroy', $d['div_id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi ini?')">
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
                                    <i class="fa-solid fa-folder-open text-slate-200 text-2xl"></i>
                                </div>
                                <div class="text-slate-400 font-bold text-sm">Belum ada data divisi.</div>
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
<div id="modalDivision" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modalTitle" class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i class="fa-solid fa-circle-plus text-indigo-500"></i>
                <span>Tambah Divisi Baru</span>
            </h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-[18px]"></i>
            </button>
        </div>
        
        <form id="divisionForm" method="POST" class="p-8">
            @csrf
            <div id="methodField"></div>
            
            <div class="space-y-6">
                <!-- Select Department -->
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Pilih Department Induk</label>
                    <select name="dept_id" id="dept_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] font-bold text-slate-800 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all cursor-pointer" required>
                        <option value="">-- Silahkan Pilih Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept['dept_id'] }}">{{ $dept['dept_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Division Name -->
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Nama Divisi</label>
                    <input type="text" name="div_name" id="div_name" placeholder="Contoh: HR & Admin, Engineering, dll" 
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
    const modal = document.getElementById('modalDivision');
    const form = document.getElementById('divisionForm');
    const title = document.getElementById('modalTitle');
    const inputName = document.getElementById('div_name');
    const inputDept = document.getElementById('dept_id');
    const methodField = document.getElementById('methodField');

    function openModal(mode, data = null) {
        if (mode === 'add') {
            title.innerHTML = '<i class="fa-solid fa-circle-plus text-indigo-500 text-lg"></i> Tambah Divisi Baru';
            form.action = "{{ route('master.divisions.store') }}";
            inputName.value = '';
            inputDept.value = '';
            methodField.innerHTML = '';
        } else {
            title.innerHTML = '<i class="fa-solid fa-pen-to-square text-blue-500 text-lg"></i> Edit Data Divisi';
            form.action = `/master/divisions/${data.div_id}`;
            inputName.value = data.div_name;
            inputDept.value = data.dept_id;
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

@extends('layouts.app')

@section('title', 'Master Mitra Kerja (Subcontractor)')

@section('content')
<style>
    .active-row {
        background-color: rgba(59, 130, 246, 0.05) !important;
        border-left: 4px solid #0052cc !important;
    }
    .active-row .subcon-name {
        color: #0052cc !important;
    }
</style>
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-handshake text-[22px]"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-[2px] mb-1">Master Data</div>
                <h1 class="text-2xl md:text-[32px] font-black text-slate-800 tracking-tight leading-none">Mitra Kerja <span class="text-blue-600">Subcontractor</span></h1>
            </div>
        </div>
        
        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <button onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2 active:scale-[0.98]">
            <i class="fa-solid fa-plus text-sm"></i> TAMBAH DATA SUBCON
        </button>
        @endif
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-5 rounded-2xl mb-8 font-bold text-sm border border-emerald-200 shadow-sm animate-fade-in-up flex items-center gap-4">
            <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check"></i>
            </div>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-5 rounded-2xl mb-8 font-bold text-sm border border-red-200 shadow-sm animate-fade-in-up flex items-center gap-4">
            <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center shrink-0">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <!-- Table Section -->
    <div class="bg-white rounded-[32px] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
        <!-- Table Toolbar (Simplified) -->
        <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-50/20">
            <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-2xl border border-blue-100">
                <i class="fa-solid fa-circle-info text-blue-500 text-[10px]"></i>
                <span class="text-[11px] font-black text-blue-700 uppercase tracking-widest leading-none">Total: {{ count($mitras) }} Mitra Terdaftar</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="mitraTable">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-6 text-[11px] font-extrabold text-black tracking-widest border-b border-slate-100">No</th>
                        <th class="px-8 py-6 text-[11px] font-extrabold text-black tracking-widest border-b border-slate-100">Nama Mitra</th>
                        <th class="px-8 py-6 text-[11px] font-extrabold text-black tracking-widest border-b border-slate-100">Kontak PAMA</th>
                        <th class="px-8 py-6 text-[11px] font-extrabold text-black tracking-widest border-b border-slate-100 text-center">Total Karyawan</th>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="px-8 py-6 text-[11px] font-extrabold text-black tracking-widest border-b border-slate-100 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($mitras as $m)
                    <tr id="row-{{ $m['mitra_id'] }}" 
                        onclick="selectRow({{ $m['mitra_id'] }})"
                        class="hover:bg-slate-50/80 transition-all duration-200 group cursor-pointer">
                        <td class="px-8 py-6">
                            <span class="text-xs font-bold text-black bg-slate-100 px-2 py-1 rounded-md">#{{ $m['mitra_id'] }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm">
                                    {{ substr($m['mitra_name'], 0, 1) }}
                                </div>
                                <div class="subcon-name font-bold text-black text-[15px] transition-colors">{{ $m['mitra_name'] }}</div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-[13px] font-bold text-black">
                            {{ $m['contact_pama'] ?? '-' }}
                        </td>
                        <td class="px-8 py-6 text-center">
                            <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-black">
                                <i class="fa-solid fa-users text-[10px]"></i>
                                {{ $m['_count']['employees'] ?? 0 }}
                            </div>
                        </td>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='openEditModal({{ json_encode($m) }})' class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 rounded-xl transition-all shadow-sm">
                                    <i class="fa-solid fa-pen-to-square text-[14px]"></i>
                                </button>
                                <button onclick="openDeleteModal({{ $m['mitra_id'] }}, '{{ $m['mitra_name'] }}')" class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 rounded-xl transition-all shadow-sm">
                                    <i class="fa-solid fa-trash-can text-[14px]"></i>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200">
                                    <i class="fa-solid fa-handshake-slash text-[32px]"></i>
                                </div>
                                <p class="text-slate-400 font-bold text-sm">Belum ada data Mitra Kerja.</p>
                                <button onclick="openCreateModal()" class="text-blue-600 font-extrabold text-[11px] hover:underline uppercase tracking-widest mt-1">Tambah Data Subcon Sekarang</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div id="mitraModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 id="modalTitle" class="text-lg font-black text-slate-800 tracking-tight">Tambah Mitra Baru</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="mitraForm" method="POST" action="{{ route('master.mitra.store') }}" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            
            <div class="space-y-4">
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">NAMA MITRA KERJA</label>
                    <input type="text" name="mitra_name" id="mitraNameInput" required placeholder="Contoh: PT. Maju Bersama" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-5 text-[13px] font-bold outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all transition-all"/>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">KONTAK PAMA (OPSIONAL)</label>
                    <input type="text" name="contact_pama" id="contactPamaInput" placeholder="Nama pengawas/PIC PAMA" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-5 text-[13px] font-bold outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all transition-all"/>
                </div>

                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-info text-sm"></i>
                    </div>
                    <p class="text-[11px] font-bold text-amber-800 leading-relaxed">
                        Sistem akan otomatis membuat akun Admin untuk Mitra ini. Mohon catat password yang muncul di notifikasi setelah berhasil disimpan.
                    </p>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-black text-white font-black text-[13px] rounded-2xl shadow-xl shadow-indigo-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-3">
                SIMPAN DATA MITRA
            </button>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-6">
            <i class="fa-solid fa-triangle-exclamation text-[32px]"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Hapus Mitra Kerja?</h3>
        <p class="text-slate-500 text-[13px] font-bold mb-8 leading-relaxed">
            Anda akan menghapus mitra <span id="delMitraName" class="text-indigo-600 uppercase font-black"></span>. Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-xs rounded-2xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="deleteForm" method="POST" action="" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-4 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-2xl transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest">
                    YA, HAPUS
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const mitraModal = document.getElementById('mitraModal');
    const deleteModal = document.getElementById('deleteModal');
    const mitraForm = document.getElementById('mitraForm');
    const methodField = document.getElementById('methodField');
    const modalTitle = document.getElementById('modalTitle');
    const mitraNameInput = document.getElementById('mitraNameInput');
    const contactPamaInput = document.getElementById('contactPamaInput');
    const deleteForm = document.getElementById('deleteForm');
    const delMitraName = document.getElementById('delMitraName');

    const baseUpdateUrl = "{{ url('master/mitra') }}";

    function openCreateModal() {
        modalTitle.innerText = "Tambah Mitra Baru";
        mitraForm.action = "{{ route('master.mitra.store') }}";
        methodField.value = "POST";
        mitraNameInput.value = "";
        contactPamaInput.value = "";
        mitraModal.classList.remove('hidden');
    }

    function openEditModal(mitra) {
        modalTitle.innerText = "Edit Mitra Kerja";
        mitraForm.action = `${baseUpdateUrl}/${mitra.mitra_id}`;
        methodField.value = "PUT";
        mitraNameInput.value = mitra.mitra_name;
        contactPamaInput.value = mitra.contact_pama || "";
        mitraModal.classList.remove('hidden');
    }

    function closeModal() {
        mitraModal.classList.add('hidden');
    }

    function openDeleteModal(id, name) {
        deleteForm.action = `${baseUpdateUrl}/${id}`;
        delMitraName.innerText = name;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }

    function selectRow(id) {
        // Remove active class from all rows
        document.querySelectorAll('tr[id^="row-"]').forEach(row => {
            row.classList.remove('active-row');
        });
        
        // Add active class to clicked row
        const selectedRow = document.getElementById(`row-${id}`);
        if (selectedRow) {
            selectedRow.classList.add('active-row');
        }
    }
</script>
@endsection

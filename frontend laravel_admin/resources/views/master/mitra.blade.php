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
    
    <!-- Premium Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="database" class="w-3.5 h-3.5"></i> Data Master
                </span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Mitra Kerja</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i data-lucide="handshake" class="w-5 h-5"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-black tracking-tight leading-none uppercase">Mitra Kerja / Subcontractor</h1>
            </div>
        </div>
        
        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <div class="flex items-center gap-3">
            <button id="btnBulkDelete" type="button" onclick="openBulkDeleteModal()" class="hidden px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-extrabold text-[12px] rounded-xl shadow-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2 border border-red-200 animate-fade-in">
                <i data-lucide="trash-2" class="w-4 h-4"></i> HAPUS TERPILIH (<span id="selectedCount">0</span>)
            </button>

            <button onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> TAMBAH SUBCONT
            </button>
        </div>
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

    <div class="flex flex-col gap-4">
        <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
            <i data-lucide="list" class="w-5 h-5 text-blue-500"></i> Daftar Struktur
        </h3>
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <!-- Table Toolbar Area (Header) -->
            <div class="p-4 border-b border-slate-50 bg-slate-50/20">
                <!-- Space kept for card structural consistency -->
            </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="mitraTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="py-4 px-5 w-10 text-center">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        @endif
                        <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest w-[80px]">No</th>
                        <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest">Nama Mikat / Subcont</th>
                        <th class="py-4 px-8 text-[11px] font-extrabold text-black tracking-widest">Nama PIC</th>
                        <th class="py-4 px-8 text-[11px] font-extrabold text-black tracking-widest text-center">Total Karyawan</th>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="py-4 px-8 text-[11px] font-extrabold text-black tracking-widest text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($mitras as $m)
                     <tr id="row-{{ $m['mitra_id'] }}" 
                        onclick="selectRow({{ $m['mitra_id'] }})"
                        class="hover:bg-blue-50/30 transition-colors group cursor-pointer">
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="py-4 px-5 text-center" onclick="event.stopPropagation()">
                            <input type="checkbox" name="ids[]" value="{{ $m['mitra_id'] }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        @endif
                        <td class="py-4 px-5 text-[13px] font-bold text-black">{{ $loop->iteration }}</td>
                        <td class="py-4 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-sm">
                                    {{ substr($m['mitra_name'], 0, 1) }}
                                </div>
                                <div class="subcon-name font-bold text-black text-[14px] tracking-tight transition-colors">{{ $m['mitra_name'] }}</div>
                            </div>
                        </td>
                        <td class="py-4 px-8 text-[13px] font-bold text-black">
                            {{ $m['contact_pama'] ?? '-' }}
                        </td>
                        <td class="py-4 px-8 text-center">
                            <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black border border-blue-100 shadow-sm transition-all hover:bg-blue-100">
                                <i data-lucide="users" class="w-3 h-3 opacity-60"></i>
                                {{ $m['_count']['employees'] ?? 0 }}
                            </div>
                        </td>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="py-4 px-8 text-right whitespace-nowrap">
                            <button onclick='openEditModal({{ json_encode($m) }})' class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2" title="Edit Mitra">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openDeleteModal({{ $m['mitra_id'] }}, '{{ $m['mitra_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Hapus Mitra">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
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
                                <p class="text-slate-400 font-bold text-sm">Tidak ada data yang ditemukan</p>
                                <button onclick="openCreateModal()" class="text-blue-600 font-extrabold text-[11px] hover:underline uppercase tracking-widest mt-1">TAMBAH DATA MIKAT / SUBCONT SEKARANG</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 italic-none">
            <p class="text-[10px] font-bold text-black uppercase tracking-widest">Total {{ count($mitras) }} Mikat / Subcont Terdaftar</p>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div id="mitraModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 id="modalTitle" class="text-lg font-black text-slate-800 tracking-tight">Tambah Mikat / Subcont Baru</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="mitraForm" method="POST" action="{{ route('master.mitra.store') }}" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            
            <div class="space-y-4">
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">NAMA MIKAT / SUBCONT</label>
                    <input type="text" name="mitra_name" id="mitraNameInput" required placeholder="Contoh: PT. Maju Bersama" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-5 text-[13px] font-bold outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all transition-all"/>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">NAMA PIC (OPSIONAL)</label>
                    <input type="text" name="contact_pama" id="contactPamaInput" placeholder="Nama pengawas/PIC" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-5 text-[13px] font-bold outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all transition-all"/>
                </div>

                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">NAMA ADMINISTRATOR MIKAT / SUBCONT</label>
                    <input type="text" name="admin_name" id="adminNameInput" placeholder="Contoh: Budi Santoso" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 px-5 text-[13px] font-bold outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all transition-all"/>
                    <p class="text-[9px] text-slate-400 font-bold mt-2 ml-1 italic">*Nama ini akan muncul di menu Akses User</p>
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
        <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight uppercase">Hapus Mitra Kerja?</h3>
        <p class="text-slate-500 text-[13px] font-bold mb-8 leading-relaxed">
            Menghapus mitra <span id="delMitraName" class="text-indigo-600 uppercase font-black"></span> akan menghapus <span class="text-red-600 font-extrabold">SEMUA DATA KARYAWAN, USER, DAN LAPORAN</span> yang bersangkutan secara permanen.
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

<!-- Modal Bulk Delete Confirmation -->
<div id="bulkDeleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i class="fa-solid fa-triangle-exclamation text-[32px]"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 mb-2 uppercase tracking-tighter">Hapus Terpilih?</h3>
        <p class="text-slate-500 text-sm font-bold mb-8 leading-relaxed uppercase">
            HAPUS <span id="bulkCountDisplay" class="text-red-600 font-extrabold"></span> MITRA? <br/>
            <span class="text-red-600 font-black">PERINGATAN:</span> SEMUA KARYAWAN & DATA TERKAIT AKAN HAPUS PERMANEN!
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('master.mitra.bulk-destroy') }}" class="flex-1">
                @csrf
                @method('DELETE')
                <div id="selectedIdsInputs"></div>
                <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest">
                    YA, HAPUS
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const mitraModal = document.getElementById('mitraModal');
    const deleteModal = document.getElementById('deleteModal');
    const bulkDeleteModalContainer = document.getElementById('bulkDeleteModalContainer');
    const bulkCountDisplay = document.getElementById('bulkCountDisplay');
    const selectedIdsInputs = document.getElementById('selectedIdsInputs');
    
    const mitraForm = document.getElementById('mitraForm');
    const methodField = document.getElementById('methodField');
    const modalTitle = document.getElementById('modalTitle');
    const mitraNameInput = document.getElementById('mitraNameInput');
    const contactPamaInput = document.getElementById('contactPamaInput');
    const adminNameInput = document.getElementById('adminNameInput');
    const deleteForm = document.getElementById('deleteForm');
    const delMitraName = document.getElementById('delMitraName');

    const baseUpdateUrl = "{{ url('master/mitra') }}";

    function openCreateModal() {
        modalTitle.innerText = "Tambah Mitra Baru";
        mitraForm.action = "{{ route('master.mitra.store') }}";
        methodField.value = "POST";
        mitraNameInput.value = "";
        contactPamaInput.value = "";
        adminNameInput.value = "";
        mitraModal.classList.remove('hidden');
    }

    function openEditModal(mitra) {
        modalTitle.innerText = "Edit Mitra Kerja";
        mitraForm.action = `${baseUpdateUrl}/${mitra.mitra_id}`;
        methodField.value = "PUT";
        mitraNameInput.value = mitra.mitra_name;
        contactPamaInput.value = mitra.contact_pama || "";
        
        // Extract Admin Name from users relation
        const adminUser = mitra.users && mitra.users.length > 0 ? mitra.users[0] : null;
        if (adminUser && adminUser.employee) {
            adminNameInput.value = adminUser.employee.full_name;
        } else {
            adminNameInput.value = "";
        }
        
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

    // --- Bulk Delete & Import Logic ---
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');


    function updateBulkButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            btnBulkDelete.classList.remove('hidden');
            selectedCount.innerText = checkedCount;
        } else {
            btnBulkDelete.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkButton();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', (e) => {
            e.stopPropagation();
            updateBulkButton();
        });
    });

    function openBulkDeleteModal() {
        const checkedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        bulkCountDisplay.innerText = checkedIds.length;
        selectedIdsInputs.innerHTML = '';
        checkedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            selectedIdsInputs.appendChild(input);
        });
        bulkDeleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeBulkDeleteModal() {
        bulkDeleteModalContainer.classList.add('hidden');
    }



    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteModal();
            closeBulkDeleteModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection

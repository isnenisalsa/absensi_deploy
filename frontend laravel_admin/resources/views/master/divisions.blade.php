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
                    <i data-lucide="database" class="w-3.5 h-3.5"></i> Master Data
                </span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Master Divisi</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-700 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i data-lucide="folder-tree" class="w-5 h-5"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Divisi</h1>
            </div>
        </div>

        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <div class="flex gap-3">
             <!-- Bulk Delete Button -->
             <button type="button" id="btnBulkDelete" onclick="openBulkDeleteModal()" class="hidden px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-extrabold text-[12px] rounded-xl border border-red-100 transition-all uppercase tracking-widest flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="trash-2" class="w-4 h-4"></i> HAPUS TERPILIH (<span id="selectedCount">0</span>)
            </button>
            
            <!-- Import Excel Button -->
            <button type="button" onclick="openImportModal()" class="px-5 py-2.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-extrabold text-[12px] rounded-xl transition-all uppercase tracking-widest flex items-center justify-center gap-2 shadow-sm border border-emerald-200">
                <i data-lucide="file-up" class="w-4 h-4"></i> IMPORT EXCEL
            </button>

            <button onclick="openModal('add')" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2 group">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Divisi</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Integrated Data Table Card -->
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden animate-fade-in-up">
        <!-- Table Toolbar (Simplified) -->
        <div class="p-6 border-b border-slate-50 flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-50/30">
            <div class="flex items-center gap-2 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100 italic-none">
                <i class="fa-solid fa-circle-info text-blue-500 text-[10px]"></i>
                <span class="text-[10px] font-black text-blue-700 uppercase tracking-widest leading-none italic-none">Total: {{ count($divisions) }} Divisi Terdaftar</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="divisionTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="py-5 px-6 w-10 text-center">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        @endif
                        <th class="py-5 px-6 text-[11px] font-extrabold text-black tracking-widest w-[80px]">No</th>
                        <th class="py-5 px-6 text-[10px] font-extrabold text-black tracking-[0.15em] cursor-pointer group/sort transition-all relative overflow-hidden active:bg-slate-100/30" onclick="sortTableByName()">
                            <div class="flex items-center gap-2 select-none group-hover/sort:text-indigo-600 transition-colors">
                                <span>Nama Divisi</span>
                                <div class="relative w-4 h-4 flex items-center justify-center">
                                    <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 opacity-100 transition-all duration-300 text-black" id="sortIcon"></i>
                                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-black rounded-full scale-0 transition-transform duration-300" id="sortIndicator"></span>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 w-0 h-[2px] bg-indigo-500 transition-all duration-500 group-hover/sort:w-full opacity-50"></div>
                        </th>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="py-5 px-6 text-[11px] font-extrabold text-black tracking-widest text-right w-[150px]">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($divisions as $d)
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="py-4 px-6 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $d['div_id'] }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        @endif
                        <td class="py-4 px-6">
                            <span class="text-[13px] font-bold text-black">{{ $loop->iteration }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="text-[14px] font-bold text-black uppercase tracking-tight">{{ $d['div_name'] }}</div>
                        </td>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <button onclick="openModal('edit', {{ json_encode($d) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2 shadow-sm">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="confirmDeleteDiv('{{ $d['div_id'] }}', '{{ $d['div_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="folder-open" class="w-8 h-8 text-slate-200"></i>
                                </div>
                                <div class="text-slate-400 font-bold text-sm">Belum ada data divisi.</div>
                                <button onclick="openModal('add')" class="mt-4 text-blue-600 font-black text-xs hover:underline uppercase tracking-widest">Tambah Divisi Sekarang</button>
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
                <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-500"></i>
                <span>Tambah Divisi Baru</span>
            </h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="divisionForm" method="POST" class="p-8">
            @csrf
            <div id="methodField"></div>
            
            <div class="space-y-6">
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
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div id="importModalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i data-lucide="file-up" class="w-5 h-5 text-emerald-500"></i>
                <span>Import Data Divisi</span>
            </h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('master.divisions.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="mb-6">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 block ml-1">Pilih File Excel (.xlsx / .xls)</label>
                <div class="relative group">
                    <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all border border-slate-200 rounded-xl p-1.5 font-bold cursor-pointer" />
                </div>
                <p class="mt-3 text-[11px] text-slate-400 font-bold italic leading-relaxed">
                    * Format Excel wajib memiliki header <span class="text-blue-600 font-black underline">"Nama Divisi"</span> pada baris pertama.
                </p>
            </div>
            <button type="submit" class="w-full px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i> MULAI IMPORT
            </button>
        </form>
    </div>
</div>

<!-- Modal Bulk Delete Confirmation -->
<div id="bulkDeleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Terpilih?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus <span id="bulkCountDisplay" class="text-red-600 font-extrabold"></span> divisi yang dipilih secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('master.divisions.bulk-destroy') }}" class="flex-1">
                @csrf
                @method('DELETE')
                <div id="selectedIdsInputs"></div>
                <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest">
                    YA, HAPUS SEMUA
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation (Single) -->
<div id="divisionDeleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteDivModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Divisi?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus <span id="delDivName" class="text-indigo-600 font-extrabold"></span> ini secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeDeleteDivModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="divisionDeleteForm" method="POST" action="" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest">
                    YA, HAPUS
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('modalDivision');
    const form = document.getElementById('divisionForm');
    const title = document.getElementById('modalTitle');
    const inputName = document.getElementById('div_name');
    const methodField = document.getElementById('methodField');

    const importModalContainer = document.getElementById('importModalContainer');
    const bulkDeleteModalContainer = document.getElementById('bulkDeleteModalContainer');
    const divisionDeleteModalContainer = document.getElementById('divisionDeleteModalContainer');
    const divisionDeleteForm = document.getElementById('divisionDeleteForm');
    const delDivName = document.getElementById('delDivName');

    const baseUrl = "/master/divisions";

    // Initialize Icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        sortTableByName(); // Auto-sort A-Z on load
    });

    function openModal(mode, data = null) {
        if (mode === 'add') {
            title.innerHTML = '<i data-lucide="plus-circle" class="w-5 h-5 text-indigo-500"></i> Tambah Divisi Baru';
            form.action = "{{ route('master.divisions.store') }}";
            inputName.value = '';
            methodField.innerHTML = '';
        } else {
            title.innerHTML = '<i data-lucide="edit-3" class="w-5 h-5 text-blue-500"></i> Edit Data Divisi';
            form.action = `/master/divisions/${data.div_id}/update`; // Corrected based on standard
            // Actually the route is named 'master.divisions.update' which is PUT /master/divisions/{id}
            form.action = `${baseUrl}/${data.div_id}`;
            inputName.value = data.div_name;
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function confirmDeleteDiv(id, name) {
        divisionDeleteForm.action = `${baseUrl}/${id}`;
        delDivName.innerText = name;
        divisionDeleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDeleteDivModal() {
        divisionDeleteModalContainer.classList.add('hidden');
    }

    function openImportModal() {
        importModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }
    function closeImportModal() {
        importModalContainer.classList.add('hidden');
    }

    // --- Bulk Delete Logic ---
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');
    const bulkCountDisplay = document.getElementById('bulkCountDisplay');
    const selectedIdsInputs = document.getElementById('selectedIdsInputs');

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
        cb.addEventListener('change', updateBulkButton);
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

    // --- Sorting Logic ---
    let sortDirection = 'desc'; // Set to desc so first call toggles to 'asc' (A-Z)
    function sortTableByName() {
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-data)'));
        
        // Cek jika tidak ada data
        if (rows.length === 0 || rows[0].innerText.includes('Belum ada data')) return;

        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        
        rows.sort((a, b) => {
            const nameA = a.querySelector('.text-\\[14px\\]').innerText.trim().toUpperCase();
            const nameB = b.querySelector('.text-\\[14px\\]').innerText.trim().toUpperCase();
            
            if (sortDirection === 'asc') {
                return nameA.localeCompare(nameB);
            } else {
                return nameB.localeCompare(nameA);
            }
        });

        // Re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach((row, index) => {
            // Update Numbering
            const numSpan = row.querySelector('.text-\\[13px\\]');
            if (numSpan) numSpan.innerText = index + 1;
            tbody.appendChild(row);
        });

        // Update Premium Icons & Indicator UI
        const sortIcon = document.getElementById('sortIcon');
        const sortIndicator = document.getElementById('sortIndicator');
        
        if (sortDirection === 'asc') {
            sortIcon.setAttribute('data-lucide', 'sort-asc');
            sortIcon.classList.add('text-black', 'opacity-100');
            sortIndicator.classList.add('scale-100', 'bg-black');
            sortIndicator.classList.remove('bg-indigo-600');
        } else {
            sortIcon.setAttribute('data-lucide', 'sort-desc');
            sortIcon.classList.add('text-black', 'opacity-100');
            sortIndicator.classList.add('scale-100', 'bg-black');
            sortIndicator.classList.remove('bg-indigo-600');
        }

        lucide.createIcons();
    }

    // Close on ESC
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteDivModal();
            closeImportModal();
            closeBulkDeleteModal();
        }
    });

</script>
@endpush

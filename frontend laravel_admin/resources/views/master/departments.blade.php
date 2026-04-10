@extends('layouts.app')

@section('title', 'Manajemen Departemen')

@push('styles')
<style>
    /* Premium TomSelect Customization */
    .ts-control.premium-select {
        border-radius: 12px !important;
        padding: 5px 12px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        min-height: 48px !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        transition: all 0.3s ease !important;
    }
    
    .ts-control.premium-select.focus {
        box-shadow: 0 0 0 4px rgba(0, 82, 204, 0.1) !important;
        border-color: #0052cc !important;
        background-color: #ffffff !important;
    }

    .ts-control.premium-select .item {
        background: #eff6ff !important;
        color: #1e40af !important;
        border: 1px solid #dbeafe !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 3px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        animation: ts-in 0.2s ease-out;
    }

    @keyframes ts-in {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    .ts-control.premium-select .item .remove {
        border-left: 1px solid #dbeafe !important;
        margin-left: 6px !important;
        padding-left: 6px !important;
        color: #60a5fa !important;
    }

    .ts-control.premium-select .item .remove:hover {
        background: #fee2e2 !important;
        color: #ef4444 !important;
        border-radius: 0 7px 7px 0 !important;
    }

    .ts-dropdown.premium-dropdown {
        border-radius: 16px !important;
        margin-top: 8px !important;
        border: 1px solid #f1f5f9 !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
        padding: 6px !important;
        z-index: 100 !important;
        animation: ts-down 0.2s ease-out;
    }

    @keyframes ts-down {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .ts-dropdown.premium-dropdown .option {
        padding: 10px 14px !important;
        border-radius: 10px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin-bottom: 2px !important;
        transition: all 0.2s ease !important;
    }

    .ts-dropdown.premium-dropdown .active {
        background-color: #f0f7ff !important;
        color: #0052cc !important;
    }
</style>
@endpush

@section('content')
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
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Departemen</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i data-lucide="briefcase" class="w-5 h-5"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Departemen Institusi</h1>
            </div>
        </div>

        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <div class="flex gap-3">
            <!-- New: Bulk Delete Button -->
            <button type="button" id="btnBulkDelete" onclick="openBulkDeleteModal()" class="hidden px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-extrabold text-[12px] rounded-xl border border-red-100 transition-all uppercase tracking-widest flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="trash-2" class="w-4 h-4"></i> HAPUS TERPILIH (<span id="selectedCount">0</span>)
            </button>
            
            <!-- New: Import Excel Button -->
            <button type="button" onclick="openImportModal()" class="px-5 py-2.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-extrabold text-[12px] rounded-xl transition-all uppercase tracking-widest flex items-center justify-center gap-2 shadow-sm border border-emerald-200">
                <i data-lucide="file-up" class="w-4 h-4"></i> IMPORT EXCEL
            </button>

            <button type="button" onclick="openCreateDeptModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> TAMBAH DEPARTEMEN
            </button>
        </div>
        @endif
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
            {{ $errors->first() }}
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
                <table class="w-full text-left border-collapse" id="departmentTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <th class="py-4 px-5 w-10 text-center">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            @endif
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest w-[80px]">No</th>
                            <th class="py-4 px-5 text-[10px] font-extrabold text-black tracking-[0.15em] cursor-pointer group/sort transition-all relative overflow-hidden active:bg-slate-100/30" onclick="sortTableByName()">
                                <div class="flex items-center gap-2 select-none group-hover/sort:text-indigo-600 transition-colors">
                                    <span>Nama Departemen</span>
                                    <div class="relative w-4 h-4 flex items-center justify-center">
                                        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 opacity-100 transition-all duration-300 text-black" id="sortIcon"></i>
                                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-black rounded-full scale-0 transition-transform duration-300" id="sortIndicator"></span>
                                    </div>
                                </div>
                                <div class="absolute bottom-0 left-0 w-0 h-[2px] bg-indigo-500 transition-all duration-500 group-hover/sort:w-full opacity-50"></div>
                            </th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest">Divisi Terkait</th>
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($departments as $dept)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <td class="py-4 px-5 text-center">
                                <input type="checkbox" name="ids[]" value="{{ $dept['dept_id'] }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            @endif
                            <td class="py-4 px-5 text-[13px] font-bold text-black">{{ $loop->iteration }}</td>
                            <td class="py-4 px-5">
                                <div class="text-[14px] font-bold text-black tracking-tight">{{ $dept['dept_name'] }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex flex-wrap gap-1.5">
                                    @if(!empty($dept['divisions']) && count($dept['divisions']) > 0)
                                        @foreach($dept['divisions'] as $dDiv)
                                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black border border-blue-100 shadow-sm transition-all hover:bg-blue-100">
                                            <i data-lucide="folder-tree" class="w-3 h-3 opacity-60"></i>
                                            {{ $dDiv['div_name'] }}
                                        </span>
                                        @endforeach
                                    @else
                                        <span class="text-[11px] font-bold text-slate-300 italic">Belum Ada Divisi</span>
                                    @endif
                                </div>
                            </td>
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <button type="button" onclick="editDepartment({{ json_encode($dept) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2" title="Edit Departemen">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="confirmDeleteDept('{{ $dept['dept_id'] }}', '{{ $dept['dept_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="folder-open" class="w-8 h-8 text-slate-200"></i>
                                    </div>
                                    <div class="text-slate-400 font-bold text-sm">Belum ada data departemen.</div>
                                    <button onclick="openCreateDeptModal()" class="mt-4 text-blue-600 font-black text-xs hover:underline uppercase tracking-widest">Tambah Departemen Sekarang</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 italic-none">
                <p class="text-[10px] font-bold text-black uppercase tracking-widest">Total {{ count($departments) }} Departemen Terdaftar</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dept Form -->
<div id="deptModalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeptModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i id="deptModalIcon" data-lucide="plus-circle" class="w-5 h-5 text-emerald-500"></i>
                <span id="deptModalTitle">Tambah Departemen</span>
            </h3>
            <button type="button" onclick="closeDeptModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="deptForm" method="POST" action="{{ route('master.departments.store') }}" class="p-6 bg-white relative overflow-hidden">
            @csrf
            <input type="hidden" name="_method" value="POST" id="deptMethodField">
            
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="flex flex-col mb-4 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Departemen</label>
                <input type="text" id="deptNameInput" name="dept_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="Silahkan isi Nama Departemen"/>
            </div>

            <div class="flex flex-col mb-6 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Pilih Divisi Induk (Multi-select)</label>
                <select id="deptDivSelect" name="div_ids[]" class="w-full" placeholder="Silahkan Pilih Divisi..." autocomplete="off" multiple>
                    @foreach($divisions as $div)
                    <option value="{{ $div['div_id'] }}">{{ $div['div_name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" id="btnSubmitDept" class="w-full px-6 py-3.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> SIMPAN DEPARTEMEN
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
                <span>Import Data Departemen</span>
            </h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('master.departments.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="mb-6">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 block ml-1">Pilih File Excel (.xlsx / .xls)</label>
                <div class="relative group">
                    <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all border border-slate-200 rounded-xl p-1.5 font-bold cursor-pointer" />
                </div>
                <p class="mt-3 text-[11px] text-slate-400 font-bold italic leading-relaxed">
                    * Format Excel wajib memiliki header <span class="text-blue-600 font-black underline">"Nama Departemen"</span> dan <span class="text-blue-600 font-black underline">"Nama Divisi"</span> (Opsional).
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
            Apakah Anda yakin ingin menghapus <span id="bulkCountDisplay" class="text-red-600 font-extrabold"></span> departemen yang dipilih secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('master.departments.bulk-destroy') }}" class="flex-1">
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
<div id="deleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Data?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus <span id="delItemName" class="text-blue-600 font-extrabold"></span> ini secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="deleteForm" method="POST" action="" class="flex-1">
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
    const deptModalContainer = document.getElementById('deptModalContainer');
    const deleteModalContainer = document.getElementById('deleteModalContainer');
    const importModalContainer = document.getElementById('importModalContainer');
    const bulkDeleteModalContainer = document.getElementById('bulkDeleteModalContainer');
    
    // Dept Form
    const deptForm = document.getElementById('deptForm');
    const deptMethodField = document.getElementById('deptMethodField');
    const deptNameInput = document.getElementById('deptNameInput');
    const deptDivSelect = document.getElementById('deptDivSelect');
    const deptModalTitle = document.getElementById('deptModalTitle');
    const btnSubmitDept = document.getElementById('btnSubmitDept');
    
    const deleteForm = document.getElementById('deleteForm');
    const delItemName = document.getElementById('delItemName');

    const updateDeptUrlBase = "{{ url('master/departments') }}";

    let divisionSelectInstance;

    // --- TomSelect & Icons Initialization ---
    function initModule() {
        lucide.createIcons();
        if (typeof TomSelect !== 'undefined' && document.getElementById('deptDivSelect')) {
            divisionSelectInstance = new TomSelect('#deptDivSelect', {
                plugins: ['remove_button'],
                persist: false,
                create: false,
                maxItems: null,
                allowEmptyOption: true,
                closeAfterSelect: false,
                render: {
                    option: function(data, escape) {
                        return `<div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-500">
                                <i class="fa-solid fa-folder-tree text-[10px]"></i>
                            </div>
                            <span>${escape(data.text)}</span>
                        </div>`;
                    },
                    item: function(data, escape) {
                        return `<div class="flex items-center gap-2">
                             <i class="fa-solid fa-folder-tree opacity-50"></i>
                             <span>${escape(data.text)}</span>
                        </div>`;
                    }
                },
                onInitialize: function() {
                    this.control.classList.add('premium-select');
                    this.dropdown.classList.add('premium-dropdown');
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initModule();
        sortTableByName(); // Auto-sort A-Z on load
    });

    function openCreateDeptModal() {
        deptMethodField.value = 'POST';
        deptForm.action = "{{ route('master.departments.store') }}";
        deptNameInput.value = '';
        
        if (divisionSelectInstance) {
            divisionSelectInstance.clear();
        }
        
        deptModalTitle.innerText = "Tambah Departemen";
        btnSubmitDept.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> SIMPAN DEPARTEMEN`;
        deptModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function editDepartment(dept) {
        deptForm.action = `${updateDeptUrlBase}/${dept.dept_id}`;
        deptMethodField.value = 'PUT';
        deptNameInput.value = dept.dept_name;
        
        if (divisionSelectInstance) {
            // New structure uses 'divisions' array of objects
            const linkedDivIds = (dept.divisions || []).map(d => String(d.div_id));
            divisionSelectInstance.setValue(linkedDivIds);
        }

        deptModalTitle.innerText = `Edit Departemen: ${dept.dept_name}`;
        btnSubmitDept.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> UPDATE`;
        deptModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDeptModal() {
        deptModalContainer.classList.add('hidden');
    }

    function confirmDeleteDept(id, name) {
        deleteForm.action = `${updateDeptUrlBase}/${id}`;
        delItemName.innerText = `Dept: ${name}`;
        deleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDeleteModal() {
        deleteModalContainer.classList.add('hidden');
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

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeptModal();
            closeDeleteModal();
            closeImportModal();
            closeBulkDeleteModal();
        }
    });

    // --- Sorting Logic ---
    let sortDirection = 'desc'; // Set to desc so first call toggles to 'asc' (A-Z)
    function sortTableByName() {
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-data)'));
        
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

        tbody.innerHTML = '';
        rows.forEach((row, index) => {
            const numSpan = row.querySelector('.text-\\[13px\\]');
            if (numSpan) numSpan.innerText = index + 1;
            tbody.appendChild(row);
        });

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

</script>
@endpush

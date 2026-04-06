@extends('layouts.app')

@section('title', 'Manajemen Departemen')

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Premium Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i  class="fa-solid fa-database w-3.5 h-3.5" ></i> Data Master
                </span>
                <i  class="fa-solid fa-chevron-right w-3 h-3 text-slate-300" ></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Departemen & Divisi</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i  class="fa-solid fa-briefcase w-5 h-5" ></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Departemen Institusi</h1>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="openCreateDeptModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i  class="fa-solid fa-plus w-4 h-4" ></i> DEPARTEMEN
            </button>
            <button type="button" onclick="openCreateDivModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-[#0052cc] hover:from-indigo-700 hover:to-indigo-800 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-indigo-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i  class="fa-solid fa-plus w-4 h-4" ></i> DIVISI
            </button>
        </div>
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
            <i  class="fa-solid fa-list w-5 h-5 text-blue-500" ></i> Daftar Struktur (<span id="totalDept">{{ count($departments) }}</span>)
        </h3>
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Nama Departemen</th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($departments as $dept)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="py-4 px-5">
                                <div class="text-[14px] font-bold text-slate-800 tracking-tight mb-1">{{ $dept['dept_name'] }}</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($dept['divisions'] ?? [] as $div)
                                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md text-[10px] font-bold border border-slate-200 cursor-pointer hover:bg-slate-200" onclick='editDivision(@json($div), @json($dept))'>
                                            {{ $div['div_name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-5 text-right align-top">
                                <button type="button" onclick="editDepartment({{ json_encode($dept) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2" title="Edit Departemen">
                                    <i  class="fa-solid fa-pen-to-square w-4 h-4" ></i>
                                </button>
                                <button type="button" onclick="confirmDeleteDept('{{ $dept['dept_id'] }}', '{{ $dept['dept_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                    <i  class="fa-solid fa-trash-can w-4 h-4" ></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="py-10 text-center text-slate-400 font-bold text-xs">Belum ada data departemen.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
                <i  id="deptModalIcon" class="fa-solid fa-circle-plus w-5 h-5 text-emerald-500" ></i>
                <span id="deptModalTitle">Tambah Departemen</span>
            </h3>
            <button type="button" onclick="closeDeptModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i  class="fa-solid fa-xmark w-5 h-5" ></i>
            </button>
        </div>
        
        <form id="deptForm" method="POST" action="{{ route('master.departments.store') }}" class="p-6 bg-white relative overflow-hidden">
            @csrf
            <input type="hidden" name="_method" value="POST" id="deptMethodField">
            
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="flex flex-col mb-5 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Departemen</label>
                <input type="text" id="deptNameInput" name="dept_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="Silahkan isi Nama Departemen"/>

            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" id="btnSubmitDept" class="w-full px-6 py-3.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i  class="fa-solid fa-save w-4 h-4" ></i> SIMPAN DEPARTEMEN
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Div Form -->
<div id="divModalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDivModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i  id="divModalIcon" class="fa-solid fa-circle-plus w-5 h-5 text-indigo-500" ></i>
                <span id="divModalTitle">Tambah Divisi Baru</span>
            </h3>
            <button type="button" onclick="closeDivModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i  class="fa-solid fa-xmark w-5 h-5" ></i>
            </button>
        </div>
        
        <form id="divForm" method="POST" action="{{ route('master.divisions.store') }}" class="p-6 bg-white relative overflow-hidden">
            @csrf
            <input type="hidden" name="_method" value="POST" id="divMethodField">
            
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="flex flex-col mb-4 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Pilih Departemen Induk</label>
                <select id="parentDeptSelect" name="dept_id" class="searchable-select w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm cursor-pointer" required>

                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept['dept_id'] }}">{{ $dept['dept_name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col mb-5 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Divisi</label>
                <input type="text" id="divNameInput" name="div_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm" required placeholder="Silahkan isi Nama Divisi"/>

            </div>

            <div class="flex flex-row gap-3">
                <button type="submit" id="btnSubmitDiv" class="flex-1 w-full px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-[#0052cc] hover:from-indigo-700 hover:to-indigo-800 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i  class="fa-solid fa-save w-4 h-4" ></i> SIMPAN DIVISI
                </button>
                <button type="button" id="btnDeleteDiv" onclick="deleteDivision()" class="hidden px-4 py-3 bg-red-50 hover:bg-red-100 text-red-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest items-center justify-center gap-2 border border-red-100">
                    <i  class="fa-solid fa-trash-can w-4 h-4" ></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div id="deleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i  class="fa-solid fa-triangle-exclamation w-8 h-8" ></i>
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
    const divModalContainer = document.getElementById('divModalContainer');
    const deleteModalContainer = document.getElementById('deleteModalContainer');
    
    // Dept Form
    const deptForm = document.getElementById('deptForm');
    const deptMethodField = document.getElementById('deptMethodField');
    const deptNameInput = document.getElementById('deptNameInput');
    const deptModalTitle = document.getElementById('deptModalTitle');
    const btnSubmitDept = document.getElementById('btnSubmitDept');
    
    // Div Form
    const divForm = document.getElementById('divForm');
    const divMethodField = document.getElementById('divMethodField');
    const divNameInput = document.getElementById('divNameInput');
    const parentDeptSelect = document.getElementById('parentDeptSelect');
    const divModalTitle = document.getElementById('divModalTitle');
    const btnSubmitDiv = document.getElementById('btnSubmitDiv');
    const btnDeleteDiv = document.getElementById('btnDeleteDiv');

    const deleteForm = document.getElementById('deleteForm');
    const delItemName = document.getElementById('delItemName');

    const updateDeptUrlBase = "{{ url('master/departments') }}";
    const updateDivUrlBase = "{{ url('master/divisions') }}"; 

    function openCreateDeptModal() {
        deptMethodField.value = 'POST';
        deptForm.action = "{{ route('master.departments.store') }}";
        deptNameInput.value = '';
        deptModalTitle.innerText = "Tambah Departemen";
        deptModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function editDepartment(dept) {
        deptForm.action = `${updateDeptUrlBase}/${dept.dept_id}`;
        deptMethodField.value = 'PUT';
        deptNameInput.value = dept.dept_name;
        deptModalTitle.innerText = `Edit Departemen: ${dept.dept_name}`;
        deptModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDeptModal() {
        deptModalContainer.classList.add('hidden');
    }

    function openCreateDivModal() {
        divMethodField.value = 'POST';
        divForm.action = "{{ route('master.divisions.store') }}";
        divNameInput.value = '';
        parentDeptSelect.value = '';
        divModalTitle.innerText = "Tambah Divisi Baru";
        btnSubmitDiv.innerHTML = `<i  class="fa-solid fa-save w-4 h-4" ></i> SIMPAN DIVISI`;
        btnDeleteDiv.classList.add('hidden');
        divModalContainer.classList.remove('hidden');

        setTimeout(() => {
            if (typeof initSearchableSelects === 'function') initSearchableSelects();
        }, 100);

        lucide.createIcons();
    }


    function editDivision(div, dept) {
        divForm.action = `${updateDivUrlBase}/${div.div_id}`;
        divMethodField.value = 'PUT';
        divNameInput.value = div.div_name;
        parentDeptSelect.value = dept.dept_id;
        divModalTitle.innerText = `Edit Divisi: ${div.div_name}`;
        btnSubmitDiv.innerHTML = `<i  class="fa-solid fa-save w-4 h-4" ></i> UPDATE`;
        btnDeleteDiv.classList.remove('hidden');
        divModalContainer.classList.remove('hidden');

        setTimeout(() => {
            if (typeof initSearchableSelects === 'function') initSearchableSelects();
        }, 100);

        lucide.createIcons();
    }


    function closeDivModal() {
        divModalContainer.classList.add('hidden');
    }

    function confirmDeleteDept(id, name) {
        deleteForm.action = `${updateDeptUrlBase}/${id}`;
        delItemName.innerText = `Dept: ${name}`;
        deleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function deleteDivision() {
        if(confirm("Apakah Anda yakin ingin menghapus divisi ini?")) {
            divMethodField.value = 'DELETE';
            divForm.submit();
        }
    }

    function closeDeleteModal() {
        deleteModalContainer.classList.add('hidden');
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeptModal();
            closeDivModal();
            closeDeleteModal();
        }
    });

</script>
@endpush

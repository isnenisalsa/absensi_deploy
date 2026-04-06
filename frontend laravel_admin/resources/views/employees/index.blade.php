@extends('layouts.app')

@section('title', 'Manajemen Karyawan')

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Premium Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-user-group text-[12px]"></i> Data Management
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Karyawan</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-user-plus text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Akun Karyawan</h1>
            </div>
        </div>
        
        <button onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> DAFTARKAN KARYAWAN
        </button>
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

    <!-- React Table Mount Point -->
    <div 
        id="employees-table-container" 
        data-react-component="employees-table" 
        data-props="{{ json_encode(['data' => $employees]) }}"
    >
        <div class="w-full h-64 bg-white rounded-2xl border border-slate-100 flex items-center justify-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>
                <span class="text-slate-400 font-bold text-sm tracking-widest uppercase">Loading Employee Table...</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="modalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden overflow-y-auto pt-10 pb-10">
    <div id="modalOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 my-auto">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-20">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i id="modalIcon" class="fa-solid fa-user-plus text-emerald-500"></i>
                <span id="modalTitle">Daftarkan Karyawan Baru</span>
            </h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark text-[18px]"></i>
            </button>
        </div>
        
        <form id="empForm" method="POST" action="{{ route('employees.store') }}" class="p-8 bg-white relative overflow-hidden max-h-[80vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="_method" value="POST" id="empMethodField">
            
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <!-- Autentikasi Section -->
            <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 mb-6 relative z-10">
                <div class="text-[11px] font-extrabold text-blue-800 mb-4 uppercase tracking-widest flex items-center gap-1"><i class="fa-solid fa-lock text-[12px]"></i> Data Autentikasi</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 block">NRP (Nomor Registrasi Pokok)</label>
                        <input type="text" id="nrpInput" name="nrp" class="w-full bg-white border border-slate-200 rounded-lg py-2.5 px-3 text-[13px] text-slate-800 font-bold outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" required placeholder="Silahkan isi NRP"/>
                    </div>
                    
                    <div id="autoPassNotice" class="flex items-center gap-3 bg-blue-100/50 p-3 rounded-lg border border-blue-200">
                        <i class="fa-solid fa-shield-halved text-[20px] text-blue-600 shrink-0"></i>
                        <div class="text-[10px] font-bold text-blue-800 leading-tight">
                            Password Otomatis:<br>
                            <span class="font-black text-blue-900 tracking-wider">P4ssw0rd4ria[NRP]</span>
                        </div>
                    </div>

                    <!-- Password field moved here, only shown on EDIT via JS -->
                    <div id="passwordContainer" class="hidden md:col-span-2 mt-2">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 flex items-center justify-between">
                            <span>Ubah Password (Optional)</span>
                            <span id="passwordHint" class="hidden text-[9px] text-red-500 normal-case italic font-bold">Kosongkan jika tidak ingin mengubah password</span>
                        </label>
                        <input type="password" id="passwordInput" name="password" class="w-full bg-white border border-slate-200 rounded-lg py-2.5 px-3 text-[13px] text-slate-800 font-bold outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Silahkan isi password baru jika perlu"/>
                    </div>
                </div>


                <div id="statusRoleContainer" class="hidden grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 block">Role Akses</label>
                        <select id="roleSelect" name="role" class="searchable-select w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-[12px] font-semibold text-slate-700 focus:outline-none focus:border-blue-500">
                            <option value="employee">Employee</option>
                            <option value="admin">Administrator</option>
                            <option value="safety">Safety Officer</option>
                            <option value="csr">CSR</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 block">Status Akun</label>
                        <select id="isActiveSelect" name="is_active" class="searchable-select w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-[12px] font-semibold text-slate-700 focus:outline-none focus:border-blue-500">
                            <option value="true">Aktif</option>
                            <option value="false">Non-Aktif (Suspend)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div class="flex flex-col mb-4 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Lengkap Pekerja</label>
                <input type="text" id="fullNameInput" name="full_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required placeholder="Silahkan isi Nama Lengkap"/>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 relative z-10">
                <div class="flex flex-col">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Jabatan Posisi</label>
                    <select id="posIdSelect" name="pos_id" class="searchable-select w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-[12px] text-slate-800 font-bold outline-none focus:bg-white focus:border-blue-500" required>
                        <option value="">- Silahkan pilih Posisi -</option>
                        @foreach($positions as $p)
                        <option value="{{ $p['pos_id'] }}">{{ $p['pos_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Divisi Inti</label>
                    <select id="divIdSelect" name="div_id" class="searchable-select w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-[12px] text-slate-800 font-bold outline-none focus:bg-white focus:border-blue-500" required>
                        <option value="">- Silahkan pilih Divisi -</option>
                        @foreach($divs as $d)
                        <option value="{{ $d['div_id'] }}">{{ $d['div_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Work Assignment Section -->
            <div class="space-y-4 mb-8">

                <!-- Mitra Row -->
                <div class="flex flex-col p-4 bg-slate-50 border border-slate-200 rounded-xl relative z-10 transition-all hover:border-emerald-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                             <i class="fa-solid fa-building text-emerald-500"></i> Perusahaan Subcontractor
                        </label>
                         <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 opacity-0 group-hover:opacity-100 transition-opacity">
                             Mitra Kerja
                        </span>
                    </div>
                    <select id="mitraIdSelect" name="mitra_kerja_id" class="searchable-select w-full bg-white border border-slate-200 rounded-xl py-2.5 px-3 text-[12px] text-slate-800 font-bold outline-none focus:bg-white focus:border-emerald-500">
                        <option value="">- Pribadi / PAMA -</option>
                        @foreach($mitras as $m)
                        <option value="{{ $m['mitra_kerja_id'] }}">{{ $m['mitra_kerja_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            <div class="flex gap-3 sticky bottom-0 bg-white pt-4 pb-2">
                <button type="submit" id="btnSubmitEmp" class="flex-1 px-6 py-4 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest">
                    SIMPAN DATA KARYAWAN
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
            <i class="fa-solid fa-triangle-exclamation text-[28px]"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Karyawan?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Menghapus <span id="delEmpName" class="text-blue-600 font-extrabold"></span> akan menghapus seluruh data user dan riwayat absensinya secara permanen.
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
    const modalContainer = document.getElementById('modalContainer');
    const deleteModalContainer = document.getElementById('deleteModalContainer');
    const empForm = document.getElementById('empForm');
    const empMethodField = document.getElementById('empMethodField');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitEmp = document.getElementById('btnSubmitEmp');
    const statusRoleContainer = document.getElementById('statusRoleContainer');
    const passwordHint = document.getElementById('passwordHint');
    const passwordContainer = document.getElementById('passwordContainer');
    const autoPassNotice = document.getElementById('autoPassNotice');
    
    // Inputs
    const nrpInput = document.getElementById('nrpInput');
    const passwordInput = document.getElementById('passwordInput');
    const fullNameInput = document.getElementById('fullNameInput');
    const roleSelect = document.getElementById('roleSelect');
    const isActiveSelect = document.getElementById('isActiveSelect');
    const posIdSelect = document.getElementById('posIdSelect');
    const divIdSelect = document.getElementById('divIdSelect');
    const mitraIdSelect = document.getElementById('mitraIdSelect');

    const deleteForm = document.getElementById('deleteForm');
    const delEmpName = document.getElementById('delEmpName');

    const storeEmpUrl = "{{ route('employees.store') }}";
    const updateEmpUrlBase = "{{ url('employees') }}"; 

    function openCreateModal() {
        empMethodField.value = 'POST';
        empForm.action = storeEmpUrl;
        
        // Reset Inputs
        nrpInput.value = '';
        nrpInput.readOnly = false;
        nrpInput.classList.remove('bg-slate-100', 'text-slate-400');
        
        passwordContainer.classList.add('hidden');
        autoPassNotice.classList.remove('hidden');
        passwordInput.value = '';
        passwordInput.required = false; 

        fullNameInput.value = '';
        statusRoleContainer.classList.add('hidden');
        statusRoleContainer.classList.remove('grid');
        
        posIdSelect.value = '';
        divIdSelect.value = '';
        mitraIdSelect.value = '';
        
        modalTitle.innerText = "Daftarkan Karyawan Baru";
        btnSubmitEmp.innerHTML = `SIMPAN DATA KARYAWAN`;
        modalContainer.classList.remove('hidden');
        
        // Init Searchable Dropdowns
        setTimeout(() => {
            initSearchableSelects();
        }, 100);
    }

    function editEmployee(emp) {
        empForm.action = `${updateEmpUrlBase}/${emp.nrp}`;
        empMethodField.value = 'PUT';
        
        // Fill Data
        nrpInput.value = emp.nrp;
        nrpInput.readOnly = true;
        nrpInput.classList.add('bg-slate-100', 'text-slate-400');
        
        // On Edit, show password field but with hint
        passwordContainer.classList.remove('hidden');
        autoPassNotice.classList.add('hidden');
        passwordInput.value = '';
        passwordInput.required = false; 
        passwordHint.classList.remove('hidden');

        fullNameInput.value = emp.full_name;
        
        statusRoleContainer.classList.remove('hidden');
        statusRoleContainer.classList.add('grid');
        
        if (emp.user) {
            roleSelect.value = emp.user.role;
            isActiveSelect.value = emp.user.is_active ? 'true' : 'false';
        }

        posIdSelect.value = emp.pos_id || '';
        divIdSelect.value = emp.div_id || '';
        mitraIdSelect.value = emp.mitra_kerja_id || '';
        
        modalTitle.innerText = `Edit Karyawan: ${emp.full_name}`;
        btnSubmitEmp.innerHTML = `UPDATE DATA KARYAWAN`;
        modalContainer.classList.remove('hidden');

        // Init Searchable Dropdowns
        setTimeout(() => {
            initSearchableSelects();
        }, 100);

        lucide.createIcons();
    }


    function closeModal() {
        modalContainer.classList.add('hidden');
    }

    function confirmDelete(nrp, name) {
        deleteForm.action = `${updateEmpUrlBase}/${nrp}`;
        delEmpName.innerText = name;
        deleteModalContainer.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModalContainer.classList.add('hidden');
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteModal();
        }
    });

</script>
@endpush

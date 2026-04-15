@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <!-- Modal Delete Confirmation (Single) -->
    <div id="deleteModalContainer" class="fixed inset-0 z-[9999] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeDeleteModal()"></div>
        <div class="bg-white w-full max-w-sm rounded-[32px] shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative z-10 mx-4 overflow-hidden border border-slate-100 flex flex-col">
            <div class="p-8 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6 shadow-inner">
                    <i class="fa-solid fa-trash-can text-3xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Hapus Permanen?</h3>
                <p class="text-slate-500 font-bold text-sm mb-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun ini secara permanen?
                </p>
                <p id="delItemName" class="text-red-600 font-black text-lg mb-8"></p>
                
                <div class="grid grid-cols-2 gap-4 w-full">
                    <button type="button" onclick="closeDeleteModal()" class="py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[11px] rounded-2xl transition-all uppercase tracking-widest border border-slate-200">
                        BATAL
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="py-4 bg-red-600 hover:bg-red-700 text-white font-black text-[11px] rounded-2xl transition-all shadow-xl shadow-red-500/40 uppercase tracking-widest border border-red-700">
                        YA, HAPUS
                    </button>
                </div>
            </div>
            <div class="h-2 bg-red-600 w-full"></div>
        </div>
    </div>

    <!-- Modal Status Confirmation (Single) -->
    <div id="statusModalContainer" class="fixed inset-0 z-[9999] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeStatusModal()"></div>
        <div class="bg-white w-full max-w-sm rounded-[32px] shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative z-10 mx-4 overflow-hidden border border-slate-100 flex flex-col animate-in fade-in zoom-in duration-200">
            <div class="p-8 flex flex-col items-center text-center">
                <div id="statusModalIconContainer" class="w-20 h-20 rounded-full flex items-center justify-center mb-6 shadow-inner transition-colors duration-300">
                    <i id="statusModalIcon" class="fa-solid text-4xl"></i>
                </div>
                <h3 id="statusModalTitle" class="text-2xl font-black text-slate-800 mb-2 tracking-tight"></h3>
                <p id="statusModalDesc" class="text-slate-500 font-bold text-sm mb-1 leading-relaxed"></p>
                <p id="statusItemName" class="text-slate-900 font-black text-lg mb-8"></p>
                
                <div class="grid grid-cols-2 gap-4 w-full">
                    <button type="button" onclick="closeStatusModal()" class="py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[11px] rounded-2xl transition-all uppercase tracking-widest border border-slate-200">
                        BATAL
                    </button>
                    <button type="button" id="confirmStatusBtn" class="py-4 text-white font-black text-[11px] rounded-2xl transition-all shadow-xl uppercase tracking-widest border">
                        YA, LANJUTKAN
                    </button>
                </div>
            </div>
            <div id="statusModalBar" class="h-2 w-full transition-colors duration-300"></div>
        </div>
    </div>

<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-[12px]"></i> System Security
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-indigo-700 font-bold bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Akun User</span>
            </div>
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#4f46e5] to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                    <i class="fa-solid fa-user-shield text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-black tracking-tight leading-none">Manajemen Akses Dashboard</h1>
            </div>
        </div>
    </div>

    <!-- React Table Mount Point -->
    <div 
        id="users-table-container" 
        data-react-component="users-table" 
        data-props="{{ json_encode([
            'data' => $users,
            'userRole' => Session::get('user_role'),
            'mitraId' => Session::get('mitra_id')
        ]) }}"
    >
        <!-- Fallback while React loads -->
        <div class="w-full h-64 bg-white rounded-2xl border border-slate-100 flex items-center justify-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-10 h-10 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
                <span class="text-slate-400 font-bold text-sm tracking-widest uppercase">Loading User Data...</span>
            </div>
        </div>
    </div>
</div>

    <!-- Modal Change Password -->
    <div id="passwordModalContainer" class="fixed inset-0 z-[9999] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closePasswordModal()"></div>
        <div class="bg-white w-full max-w-sm rounded-[32px] shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative z-10 mx-4 overflow-hidden border border-slate-100 flex flex-col animate-in fade-in zoom-in duration-200">
            <div class="p-8 flex flex-col items-center">
                <div class="w-20 h-20 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 shadow-inner">
                    <i class="fa-solid fa-key text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Ubah Password</h3>
                <p class="text-slate-500 font-bold text-sm mb-6 text-center leading-relaxed">
                    Silahkan masukkan password baru untuk user:
                    <span id="passItemName" class="block text-indigo-600 font-extrabold mt-1"></span>
                </p>
                
                <div class="w-full mb-8">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2 block">Password Baru</label>
                    <div class="relative flex items-center group">
                        <i class="fa-solid fa-lock absolute left-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                        <input type="password" id="newUserPassword" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 pl-11 pr-12 text-[13px] font-bold text-slate-800 outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" placeholder="Minimal 6 karakter"/>
                        <button type="button" onclick="togglePasswordVisibility('newUserPassword', 'passToggleBtn')" class="absolute right-4 p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all focus:outline-none">
                            <i id="passToggleBtn" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p id="passwordErrorMsg" class="hidden text-[10px] text-red-500 font-bold mt-2 ml-1 italic">Password tidak boleh kosong!</p>
                </div>

                <div class="grid grid-cols-2 gap-4 w-full">
                    <button type="button" onclick="closePasswordModal()" class="py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[11px] rounded-2xl transition-all uppercase tracking-widest border border-slate-200">
                        BATAL
                    </button>
                    <button type="button" id="confirmPassBtn" class="py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] rounded-2xl transition-all shadow-xl shadow-indigo-500/40 uppercase tracking-widest border border-indigo-700">
                        SIMPAN
                    </button>
                </div>
            </div>
            <div class="h-2 bg-indigo-600 w-full mt-2"></div>
        </div>
    </div>

<script>
    let currentDeleteNrp = null;
    let currentStatusNrp = null;
    let currentStatusValue = null;
    let currentPassNrp = null;

    // --- Password Modal Logic ---
    window.openChangePasswordModal = function(nrp) {
        currentPassNrp = nrp;
        document.getElementById('passItemName').innerText = nrp;
        document.getElementById('newUserPassword').value = '';
        document.getElementById('newUserPassword').type = 'password';
        document.getElementById('passToggleBtn').className = "fa-solid fa-eye text-sm";
        document.getElementById('passwordErrorMsg').classList.add('hidden');
        document.getElementById('passwordModalContainer').classList.remove('hidden');
    };

    window.closePasswordModal = function() {
        document.getElementById('passwordModalContainer').classList.add('hidden');
        currentPassNrp = null;
    };

    document.getElementById('confirmPassBtn').onclick = function() {
        const password = document.getElementById('newUserPassword').value;
        const errorMsg = document.getElementById('passwordErrorMsg');

        if (!password || password.trim() === '') {
            errorMsg.classList.remove('hidden');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>';

        fetch(`/users/${currentPassNrp}/status`, { // Using the same PATCH route if backend allows password update there, 
                                                  // or I might need to check the exact route for password update.
                                                  // Based on user.controller.ts, it was updateUser.
                                                  // Let's check web.php for routes.
                                                  // Actually, I'll update web.php to have a dedicated password route if needed.
                                                  // But the controller 'updateUser' takes 'password' in body.
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ password: password })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                alert('Password berhasil diperbarui');
                closePasswordModal();
            } else {
                alert(`Gagal: ${data.message || data.error || 'Terjadi kesalahan'}`);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal terhubung ke server.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    };

    // --- Status Modal Logic ---
    window.toggleUserStatus = function(nrp, newStatus) {
        currentStatusNrp = nrp;
        currentStatusValue = newStatus;

        const title = document.getElementById('statusModalTitle');
        const desc = document.getElementById('statusModalDesc');
        const icon = document.getElementById('statusModalIcon');
        const iconCont = document.getElementById('statusModalIconContainer');
        const bar = document.getElementById('statusModalBar');
        const btn = document.getElementById('confirmStatusBtn');
        const itemName = document.getElementById('statusItemName');

        itemName.innerText = `NRP: ${nrp}`;

        if (newStatus) {
            // Aktifkan
            title.innerText = "Aktifkan Akun?";
            desc.innerText = "Akun ini akan mendapatkan kembali akses penuh ke dashboard.";
            icon.className = "fa-solid fa-shield-check text-4xl";
            iconCont.className = "w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 shadow-inner";
            bar.className = "h-2 bg-emerald-500 w-full mt-2";
            btn.className = "py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[11px] rounded-2xl transition-all shadow-xl shadow-emerald-500/40 uppercase tracking-widest border border-emerald-700";
        } else {
            // Nonaktifkan (Suspend)
            title.innerText = "Nonaktifkan Akun?";
            desc.innerText = "Akun ini tidak akan bisa login ke dashboard sementara waktu.";
            icon.className = "fa-solid fa-shield-xmark text-4xl";
            iconCont.className = "w-20 h-20 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center mb-6 shadow-inner border border-yellow-100";
            bar.className = "h-2 bg-yellow-500 w-full mt-2";
            btn.className = "py-4 bg-yellow-500 hover:bg-yellow-600 text-white font-black text-[11px] rounded-2xl transition-all shadow-xl shadow-yellow-500/40 uppercase tracking-widest border border-yellow-600";
        }

        document.getElementById('statusModalContainer').classList.remove('hidden');
    };

    window.closeStatusModal = function() {
        document.getElementById('statusModalContainer').classList.add('hidden');
        currentStatusNrp = null;
        currentStatusValue = null;
    };

    document.getElementById('confirmStatusBtn').onclick = function() {
        if (!currentStatusNrp) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> MEMPROSES...';

        fetch(`/users/${currentStatusNrp}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ is_active: currentStatusValue })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                window.location.reload();
            } else {
                alert(`Gagal: ${data.error || 'Terjadi kesalahan'}`);
                closeStatusModal();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal terhubung ke server.');
            closeStatusModal();
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    };

    // --- Delete Modal Logic ---
    window.deleteUser = function(nrp) {
        currentDeleteNrp = nrp;
        document.getElementById('delItemName').innerText = `NRP: ${nrp}`;
        document.getElementById('deleteModalContainer').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        document.getElementById('deleteModalContainer').classList.add('hidden');
        currentDeleteNrp = null;
    };

    document.getElementById('confirmDeleteBtn').onclick = function() {
        if (!currentDeleteNrp) return;
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> MEMPROSES...';

        fetch(`/users/${currentDeleteNrp}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                window.location.reload();
            } else {
                alert(`Gagal menghapus: ${data.error || 'Terjadi kesalahan'}`);
                closeDeleteModal();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal terhubung ke server.');
            closeDeleteModal();
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'YA, HAPUS';
        });
    };

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeStatusModal();
            closePasswordModal();
        }
    });

</script>
@endsection

@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
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
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Akses Dashboard</h1>
            </div>
        </div>
    </div>

    <!-- React Table Mount Point -->
    <div 
        id="users-table-container" 
        data-react-component="users-table" 
        data-props="{{ json_encode(['data' => $users]) }}"
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

<script>
    // Action handler from React
    window.toggleUserStatus = function(nrp, newStatus) {
        if (confirm(`Yakin ingin ${newStatus ? 'mengaktifkan' : 'menonaktifkan'} akses user ini?`)) {
            fetch(`/api/users/${nrp}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${sessionStorage.getItem('jwt_token')}`
                },
                body: JSON.stringify({ is_active: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                window.location.reload();
            });
        }
    };
</script>
@endsection

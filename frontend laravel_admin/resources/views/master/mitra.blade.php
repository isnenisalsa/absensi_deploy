@extends('layouts.app')

@section('title', 'Manajemen Perusahaan Subcontractor')

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
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Perusahaan Subcont</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i  class="fa-solid fa-building w-5 h-5" ></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Nama Perusahaan</h1>
            </div>
            <p class="mt-3 text-slate-500 font-medium tracking-wide text-[13px] ml-1">Kelola data list nama Perusahaan Subcontractor atau Mitra Kerja.</p>
        </div>

        <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
            <i  class="fa-solid fa-plus w-4 h-4" ></i> TAMBAH MITRA
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

    <div class="flex flex-col gap-4">
        <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
            <i  class="fa-solid fa-list w-5 h-5 text-blue-500" ></i> Daftar Mitra Subcontractor (<span id="totalMitra">{{ count($mitras) }}</span>)
        </h3>
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">ID</th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Nama Perusahaan Subcontractor</th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-center">Status Area Geofence</th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-slate-500 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($mitras as $mitra)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="py-4 px-5">
                                <span class="bg-slate-100 text-slate-600 font-extrabold text-xs px-2 py-1 rounded">#{{ str_pad($mitra['mitra_kerja_id'], 3, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="py-4 px-5 text-[13px] font-bold text-slate-800">
                                {{ $mitra['mitra_kerja_name'] }}
                            </td>
                            <td class="py-4 px-5 flex items-center justify-center">
                                @if($mitra['latitude'] && $mitra['longitude'])
                                    <span class="bg-emerald-50 border border-emerald-100 text-emerald-600 font-extrabold text-[10px] px-2.5 py-1 rounded-md flex items-center gap-1">
                                        <i  class="fa-solid fa-map-pin w-3 h-3" ></i> Terpasang
                                    </span>
                                @else
                                    <span class="bg-red-50 border border-red-100 text-red-600 font-extrabold text-[10px] px-2.5 py-1 rounded-md flex items-center gap-1">
                                        <i  class="fa-solid fa-circle-xmark w-3 h-3" ></i> Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right flex justify-end gap-2">
                                <button type="button" onclick="editMitra({{ json_encode($mitra) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2">
                                    <i  class="fa-solid fa-pen-to-square w-4 h-4" ></i>
                                </button>
                                <button type="button" onclick="confirmDelete('{{ $mitra['mitra_kerja_id'] }}', '{{ $mitra['mitra_kerja_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">
                                    <i  class="fa-solid fa-trash-can w-4 h-4" ></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-400 font-bold text-xs">Belum ada data Perusahaan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="modalContainer" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div id="modalOverlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i  id="modalIcon" class="fa-solid fa-circle-plus w-5 h-5 text-emerald-500" ></i>
                <span id="modalTitle">Tambah Mitra Baru</span>
            </h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i  class="fa-solid fa-xmark w-5 h-5" ></i>
            </button>
        </div>
        
        <form id="mitraForm" method="POST" action="{{ route('master.mitra.store') }}" class="p-6 bg-white relative overflow-hidden">
            @csrf
            <input type="hidden" name="_method" value="POST" id="methodField">
            
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="flex flex-col mb-6 relative z-10">
                <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Perusahaan</label>
                <input type="text" id="mitraNameInput" name="mitra_kerja_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="Silahkan isi Nama Mitra Kerja"/>

            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" id="btnSubmit" class="w-full px-6 py-3.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i  class="fa-solid fa-save w-4 h-4" ></i> SIMPAN PERUSAHAAN
                </button>
            </div>
            
            <p class="text-[10px] text-slate-400 font-medium text-center mt-5">Geofence (Radius & Lokasi) diatur di menu Geofence Lokasi.</p>
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
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Perusahaan?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Hapus master <span id="delMitraName" class="text-blue-600 font-extrabold"></span> secara permanen? Seluruh data Geofence terkait akan ikut hilang.
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
    const form = document.getElementById('mitraForm');
    const methodField = document.getElementById('methodField');
    const mitraNameInput = document.getElementById('mitraNameInput');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmit = document.getElementById('btnSubmit');
    const delMitraName = document.getElementById('delMitraName');
    const deleteForm = document.getElementById('deleteForm');
    
    const storeUrl = "{{ route('master.mitra.store') }}";
    const updateUrlBase = "{{ url('master/mitra') }}"; 

    function openCreateModal() {
        methodField.value = 'POST';
        form.action = storeUrl;
        mitraNameInput.value = '';
        
        modalTitle.innerText = "Tambah Mitra Baru";
        btnSubmit.innerHTML = `<i  class="fa-solid fa-save w-4 h-4" ></i> SIMPAN PERUSAHAAN`;
        modalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function editMitra(mitra) {
        form.action = `${updateUrlBase}/${mitra.mitra_kerja_id}`;
        methodField.value = 'PUT';
        
        mitraNameInput.value = mitra.mitra_kerja_name;
        
        modalTitle.innerText = `Edit: #${mitra.mitra_kerja_name}`;
        btnSubmit.innerHTML = `<i  class="fa-solid fa-save w-4 h-4" ></i> PERBARUI NAMA`;
        modalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeModal() {
        modalContainer.classList.add('hidden');
    }

    function confirmDelete(id, name) {
        deleteForm.action = `${updateUrlBase}/${id}`;
        delMitraName.innerText = name;
        deleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
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

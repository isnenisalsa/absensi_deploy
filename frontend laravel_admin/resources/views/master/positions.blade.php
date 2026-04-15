@extends('layouts.app')

@section('title', 'Manajemen Posisi Karyawan')

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
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Posisi Karir</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i  class="fa-solid fa-award w-5 h-5" ></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Manajemen Posisi Karyawan</h1>
            </div>
        </div>

        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <div class="flex items-center gap-3">
            <button id="btnBulkDelete" type="button" onclick="openBulkDeleteModal()" class="hidden px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-extrabold text-[12px] rounded-xl shadow-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2 border border-red-200 animate-fade-in">
                <i data-lucide="trash-2" class="w-4 h-4"></i> HAPUS TERPILIH (<span id="selectedCount">0</span>)
            </button>

            <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> TAMBAH POSISI
            </button>
        </div>
        @endif
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
    <div class="flex flex-col gap-4">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <!-- Updated Table Toolbar -->
            <div class="p-4 border-b border-slate-50 bg-slate-50/20">
                <div class="flex flex-wrap gap-4 items-center justify-between px-1">
                    <div class="flex items-center gap-3">
                        <div class="relative group">
                            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Cari Posisi..." 
                                   class="pl-9 w-72 h-10 bg-slate-50 border-slate-200 rounded-xl text-[13px] font-bold text-black focus:bg-white focus:ring-4 focus:ring-blue-100 transition-all placeholder:font-medium">
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="positionTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <th class="py-4 px-5 w-10 text-center">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            @endif
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest w-[80px]">No</th>
                            <th class="py-4 px-5 text-[10px] font-extrabold text-black tracking-[0.15em]">Nama Posisi</th>
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest">Akses Lokasi Geofence</th>
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <th class="py-4 px-5 text-[11px] font-extrabold text-black tracking-widest text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($positions as $pos)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <td class="py-4 px-5 text-center">
                                <input type="checkbox" name="ids[]" value="{{ $pos['pos_id'] }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            @endif
                            <td class="py-4 px-5 text-[13px] font-bold text-black">{{ $loop->iteration }}</td>
                            <td class="py-4 px-5">
                                <div class="text-[14px] font-bold text-black tracking-tight">{{ $pos['pos_name'] }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($pos['allow_any_location'])
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black border border-emerald-100 shadow-sm">
                                            <i data-lucide="globe" class="w-3 h-3"></i> SEMUA LOKASI
                                        </span>
                                    @elseif(!empty($pos['allowed_locations']) && count($pos['allowed_locations']) > 0)
                                        @foreach($pos['allowed_locations'] as $aloc)
                                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black border border-blue-100 shadow-sm transition-all hover:bg-blue-100">
                                            <i data-lucide="map-pin" class="w-3 h-3 opacity-60"></i>
                                            {{ $aloc['location']['location_name'] }}
                                        </span>
                                        @endforeach
                                    @else
                                        <span class="text-[11px] font-bold text-slate-300 italic">Belum Diatur (Restricted)</span>
                                    @endif
                                </div>
                            </td>
                            @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <button type="button" onclick="editPosition({{ json_encode($pos) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all mr-2" title="Edit Posisi">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="confirmDelete('{{ $pos['pos_id'] }}', '{{ $pos['pos_name'] }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">
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
                                    <div class="text-slate-400 font-bold text-sm">Tidak ada data yang ditemukan</div>
                                    <button onclick="openCreateModal()" class="mt-4 text-blue-600 font-black text-xs hover:underline uppercase tracking-widest">Tambah Posisi Sekarang</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 italic-none">
                <p class="text-[10px] font-extrabold text-black uppercase tracking-widest">Total <span id="recordCount">{{ count($positions) }}</span> records detected</p>
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
                <span id="modalTitle">Tambah Posisi Baru</span>
            </h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i  class="fa-solid fa-xmark w-5 h-5" ></i>
            </button>
        </div>
        
        <form id="positionForm" method="POST" action="{{ route('master.positions.store') }}" class="p-6 bg-white relative overflow-hidden">
            @csrf
            <input type="hidden" name="_method" value="POST" id="methodField">
            
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

            <div class="space-y-4 relative z-10">
                <div class="flex flex-col">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Posisi</label>
                    <input type="text" id="posNameInput" name="pos_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="Silahkan isi Nama Posisi"/>
                </div>

                <div class="flex items-center gap-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                    <input type="checkbox" id="allowAnyInput" name="allow_any_location" value="1" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    <label for="allowAnyInput" class="text-[12px] font-black text-blue-800 cursor-pointer select-none">BOLEH ABSEN DI SEMUA LOKASI (GEOFENCE BYPASS)</label>
                </div>

                <div id="locationPicker" class="flex flex-col">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Mapping Lokasi Kerja (Geofence Terkait)</label>
                    <select id="locationSelect" name="location_ids[]" class="w-full" multiple placeholder="Pilih Lokasi yang Diizinkan...">
                        @foreach($locations as $loc)
                            <option value="{{ $loc['location_id'] }}">{{ $loc['location_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3">
                <button type="submit" id="btnSubmit" class="w-full px-6 py-3.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> SIMPAN POSISI
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
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Posisi?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus master posisi <span id="delPosName" class="text-blue-600 font-extrabold"></span> ini secara permanen?
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

<!-- Modal Bulk Delete Confirmation -->
<div id="bulkDeleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Terpilih?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus <span id="bulkCountDisplay" class="text-red-600 font-extrabold"></span> posisi yang dipilih secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('master.positions.bulk-destroy') }}" class="flex-1">
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


@endsection

@push('scripts')
<script>
    const modalContainer = document.getElementById('modalContainer');
    const deleteModalContainer = document.getElementById('deleteModalContainer');
    const form = document.getElementById('positionForm');
    const methodField = document.getElementById('methodField');
    const posNameInput = document.getElementById('posNameInput');
    const allowAnyInput = document.getElementById('allowAnyInput');
    const locationSelect = document.getElementById('locationSelect');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmit = document.getElementById('btnSubmit');
    const delPosName = document.getElementById('delPosName');
    const deleteForm = document.getElementById('deleteForm');
    
    const storeUrl = "{{ route('master.positions.store') }}";
    const updateUrlBase = "{{ url('master/positions') }}"; 

    let locationSelectInstance;

    // Initialize Icons on page load
    function initModule() {
        lucide.createIcons();
        if (typeof TomSelect !== 'undefined' && document.getElementById('locationSelect')) {
            locationSelectInstance = new TomSelect('#locationSelect', {
                plugins: ['remove_button'],
                persist: false,
                create: false,
                maxItems: null,
                allowEmptyOption: true,
                closeAfterSelect: false,
                render: {
                    option: function(data, escape) {
                        return `<div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-[10px] text-slate-400"></i>
                            <span>${escape(data.text)}</span>
                        </div>`;
                    },
                    item: function(data, escape) {
                        return `<div class="flex items-center gap-2">
                             <i class="fa-solid fa-map-pin opacity-50"></i>
                             <span>${escape(data.text)}</span>
                        </div>`;
                    }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initModule();
    });

    allowAnyInput.addEventListener('change', function() {
        const picker = document.getElementById('locationPicker');
        if (this.checked) {
            picker.classList.add('opacity-40', 'pointer-events-none');
            if (locationSelectInstance) locationSelectInstance.clear();
        } else {
            picker.classList.remove('opacity-40', 'pointer-events-none');
        }
    });

    function openCreateModal() {
        methodField.value = 'POST';
        form.action = storeUrl;
        posNameInput.value = '';
        allowAnyInput.checked = false;
        if (locationSelectInstance) locationSelectInstance.clear();
        document.getElementById('locationPicker').classList.remove('opacity-40', 'pointer-events-none');
        
        modalTitle.innerText = "Tambah Posisi Baru";
        btnSubmit.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> SIMPAN POSISI`;
        modalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function editPosition(pos) {
        form.action = `${updateUrlBase}/${pos.pos_id}`;
        methodField.value = 'PUT';
        
        posNameInput.value = pos.pos_name;
        allowAnyInput.checked = pos.allow_any_location;
        
        if (locationSelectInstance) {
            const locIds = (pos.allowed_locations || []).map(al => al.location_id.toString());
            locationSelectInstance.setValue(locIds);
        }

        const picker = document.getElementById('locationPicker');
        if (pos.allow_any_location) {
            picker.classList.add('opacity-40', 'pointer-events-none');
        } else {
            picker.classList.remove('opacity-40', 'pointer-events-none');
        }
        
        modalTitle.innerText = `Edit: ${pos.pos_name}`;
        btnSubmit.innerHTML = `<i data-lucide="save" class="w-4 h-4"></i> PERBARUI POSISI`;
        modalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeModal() {
        modalContainer.classList.add('hidden');
    }

    function confirmDelete(id, name) {
        deleteForm.action = `${updateUrlBase}/${id}`;
        delPosName.innerText = name;
        deleteModalContainer.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeDeleteModal() {
        deleteModalContainer.classList.add('hidden');
    }



    // --- Bulk Delete Logic ---
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDeleteModalContainer = document.getElementById('bulkDeleteModalContainer');
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
        
        // Clear previous and add new hidden inputs
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

    // --- Search Logic ---
    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('positionTable');
        const tr = table.getElementsByTagName('tr');
        const emptyState = document.getElementById('emptyStateRow');
        const recordCount = document.getElementById('recordCount');
        let visibleCount = 0;

        for (let i = 1; i < tr.length; i++) {
            if (tr[i].id === 'emptyStateRow') continue;
            
            const nameTd = tr[i].getElementsByTagName('td')[2];
            
            if (nameTd) {
                const nameText = nameTd.textContent || nameTd.innerText;
                
                if (nameText.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    visibleCount++;
                    const numTd = tr[i].getElementsByTagName('td')[1];
                    if (numTd) numTd.innerText = visibleCount;
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        // Update record count
        if (recordCount) recordCount.innerText = visibleCount;

        if (visibleCount === 0) {
            if (!emptyState) {
                const tbody = table.querySelector('tbody');
                const newEmpty = document.createElement('tr');
                newEmpty.id = 'emptyStateRow';
                newEmpty.innerHTML = `
                    <td colspan="4" class="py-20 text-center bg-slate-50/20">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="search-x" class="w-8 h-8 text-slate-200"></i>
                            </div>
                            <div class="text-slate-400 font-bold text-sm">Tidak ada data yang ditemukan</div>
                        </div>
                    </td>
                `;
                tbody.appendChild(newEmpty);
                lucide.createIcons();
            } else {
                emptyState.style.display = "";
            }
        } else if (emptyState) {
            emptyState.style.display = "none";
        }
    }

</script>
@endpush

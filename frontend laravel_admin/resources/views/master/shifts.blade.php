@extends('layouts.app')

@section('title', 'Manajemen Shift Kerja')

@push('styles')
<style>
    .row-data:hover {
        background-color: #f1f5f9 !important;
        transition: all 0.2s ease;
    }
    .badge-shift {
        font-weight: 900;
        font-size: 10px;
        padding: 5px 12px;
        border-radius: 99px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
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
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5 font-bold">
                    <i class="fa-solid fa-database text-[12px]"></i> Data Master
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Shift Kerja</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-clock text-[18px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-black tracking-tight leading-none">Manajemen Shift Kerja</h1>
            </div>
        </div>

        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
        <div class="flex items-center gap-3">
             <button id="btnBulkDelete" type="button" onclick="openBulkDeleteModal()" class="hidden px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-extrabold text-[12px] rounded-xl shadow-sm transition-all uppercase tracking-widest flex items-center justify-center gap-2 border border-red-200 animate-fade-in">
                <i class="fa-solid fa-trash-can text-sm"></i> HAPUS TERPILIH (<span id="selectedCount">0</span>)
            </button>

            <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-sm"></i> TAMBAH SHIFT
            </button>
        </div>
        @endif
    </div>

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm border border-emerald-200 animate-fade-in-up">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 animate-fade-in-up">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $errors->first() }}
        </div>
    @endif

    <!-- Integrated Data Table Card -->
    <div class="bg-white rounded-[24px] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden animate-fade-in-up">
        <!-- Table Toolbar Area (Header) -->
        <div class="p-4 border-b border-slate-50 bg-slate-50/20">
            <!-- Space kept for card structural consistency -->
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="shiftTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-5 py-4 w-10 text-center">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-5 py-4 w-20 text-center text-[10px] font-extrabold text-black uppercase tracking-widest">No</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-black uppercase tracking-widest">Kode Shift</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-black uppercase tracking-widest">Jam Masuk</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-black uppercase tracking-widest">Jam Pulang</th>
                        <th class="px-6 py-4 text-center text-[10px] font-extrabold text-black uppercase tracking-widest">Status Shift</th>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <th class="px-8 py-4 text-right w-40 text-[10px] font-extrabold text-black uppercase tracking-widest">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-none">
                    @forelse($shifts as $shift)
                    @php
                        $isNight = $shift['date_out'] != $shift['date_in'];
                    @endphp
                    <tr class="row-data group">
                        <td class="px-5 py-5 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $shift['shift_id'] }}" class="row-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="px-5 py-5 text-center font-bold text-slate-400 text-[13px] italic-none">{{ $loop->iteration }}</td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-black font-black text-[11px]">
                                    {{ substr($shift['shift_code'], 0, 1) }}
                                </div>
                                <span class="font-bold text-black text-[14px] uppercase italic-none">{{ $shift['shift_code'] }}</span>
                            </div>
                        </td>
                        @php
                            $timeIn = \Carbon\Carbon::parse($shift['time_in_expected'])->format('H:i');
                            $timeOut = \Carbon\Carbon::parse($shift['time_out_expected'])->format('H:i');
                            $isNight = isset($shift['is_night']) ? $shift['is_night'] : ($shift['date_out'] != $shift['date_in']);
                        @endphp
                        <td class="px-6 py-5 italic-none font-bold text-black group-hover:text-blue-600 transition-colors">
                            <i class="fa-regular fa-clock text-emerald-500 mr-2 opacity-50"></i>
                            {{ $timeIn }} <span class="text-[10px] text-black font-extrabold ml-1">WITA</span>
                        </td>
                        <td class="px-6 py-5 italic-none font-bold text-black group-hover:text-blue-600 transition-colors">
                            <i class="fa-regular fa-clock text-rose-500 mr-2 opacity-50"></i>
                            {{ $timeOut }} <span class="text-[10px] text-black font-extrabold ml-1">WITA</span>
                        </td>
                        <td class="px-6 py-5 text-center italic-none">
                            @if($isNight)
                                <span class="badge-shift bg-black text-white italic-none">
                                    <i class="fa-solid fa-moon mr-1 text-yellow-400"></i> Shift Malam
                                </span>
                            @else
                                <span class="badge-shift bg-blue-50 text-blue-700 border border-blue-100 italic-none">
                                    <i class="fa-solid fa-sun mr-1 text-orange-400"></i> Shift Normal
                                </span>
                            @endif
                        </td>
                        @if(Session::get('user_role') === 'admin' && Session::get('mitra_id') === null)
                        <td class="px-8 py-5 text-right italic-none">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick='editShift(@json($shift))' class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-pen-to-square text-[13px]"></i>
                                </button>
                                <button onclick="confirmDelete('{{ $shift['shift_id'] }}', '{{ $shift['shift_code'] }}')" class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-trash-can text-[13px]"></i>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-200">
                                    <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
                                </div>
                                <p class="text-slate-400 font-bold text-[14px]">Tidak ada data yang ditemukan</p>
                                <button onclick="openCreateModal()" class="mt-4 text-blue-600 font-black text-xs hover:underline uppercase tracking-widest">Tambah Shift Sekarang</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 italic-none">
            <p class="text-[10px] font-bold text-black uppercase tracking-widest">Total {{ count($shifts) }} Shift Terdaftar</p>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="shiftModal" class="fixed inset-0 z-50 flex items-center justify-center hidden pt-20">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeShiftModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-8 py-6 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 id="modalTitle" class="font-black text-slate-800 text-[18px]">Tambah Shift Baru</h3>
                <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Atur Waktu Kerja</p>
            </div>
            <button type="button" onclick="closeShiftModal()" class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-slate-800 transition-all shadow-sm flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form id="shiftForm" method="POST" action="{{ route('master.shifts.store') }}" class="p-8">
            @csrf
            <div id="methodField"></div>
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Kode Shift -->
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">
                        <i class="fa-solid fa-tag text-blue-500 mr-1"></i> Nama / Kode Shift
                    </label>
                    <input type="text" id="shift_code" name="shift_code" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-[14px] text-slate-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required placeholder="Contoh: S1, NIGHT, SHIFT 2..."/>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">
                            <i class="fa-solid fa-clock text-emerald-500 mr-1"></i> Jam Masuk
                        </label>
                        <input type="time" id="time_in_expected" name="time_in_expected" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-[14px] text-slate-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required/>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">
                            <i class="fa-solid fa-clock text-rose-500 mr-1"></i> Jam Pulang
                        </label>
                        <input type="time" id="time_out_expected" name="time_out_expected" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-[14px] text-slate-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" required/>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Pengaturan Lintas Hari</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-400 mb-2 uppercase">Status Pulang:</span>
                            <select name="is_night" id="is_night" class="bg-white border border-slate-200 rounded-xl py-2 px-3 text-[11px] font-black text-slate-700 outline-none focus:border-blue-500">
                                <option value="0">DI HARI YANG SAMA</option>
                                <option value="1">HARI BERIKUTNYA (+1)</option>
                            </select>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold leading-tight flex items-center">
                            Gunakan (+1) jika jam pulang melewati pukul 00:00 malam.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <button type="submit" class="w-full py-4 bg-black text-white font-black text-[12px] rounded-2xl shadow-xl shadow-black/20 hover:bg-slate-800 transition-all active:scale-[0.98] uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Data Shift
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Bulk Delete Confirmation -->
<div id="bulkDeleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeBulkDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-6">
            <i class="fa-solid fa-triangle-exclamation text-[32px]"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Terpilih?</h3>
        <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed">
            Apakah Anda yakin ingin menghapus <span id="bulkCountDisplay" class="text-red-600 font-extrabold"></span> shift yang dipilih secara permanen?
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-extrabold text-xs rounded-xl transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="bulkDeleteForm" method="POST" action="{{ route('master.shifts.bulk-destroy') }}" class="flex-1">
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



@push('scripts')
<script>
    const form = document.getElementById('shiftForm');
    const modal = document.getElementById('shiftModal');
    const updateUrlBase = "{{ url('master/shifts') }}";

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Shift Baru';
        form.action = "{{ route('master.shifts.store') }}";
        form.reset();
        document.getElementById('methodField').innerHTML = '';
        modal.classList.remove('hidden');
    }

    function closeShiftModal() {
        modal.classList.add('hidden');
    }

    // --- Bulk Delete & Import Logic ---
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDeleteModalContainer = document.getElementById('bulkDeleteModalContainer');
    const bulkCountDisplay = document.getElementById('bulkCountDisplay');
    const selectedIdsInputs = document.getElementById('selectedIdsInputs');
    const importModalContainer = document.getElementById('importModalContainer');

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
    }

    function closeBulkDeleteModal() {
        bulkDeleteModalContainer.classList.add('hidden');
    }



    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeShiftModal();
            closeBulkDeleteModal();
        }
    });

    function editShift(shift) {
        document.getElementById('modalTitle').innerText = `Edit: ${shift.shift_code}`;
        form.action = `${updateUrlBase}/${shift.shift_id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('shift_code').value = shift.shift_code;
        
        // Robust time extraction (handles HH:mm:ss, ISO strings, or simple HH:mm)
        const formatTime = (timeStr) => {
            if (!timeStr) return '';
            // If it contains 'T', it's likely an ISO string or full datetime
            if (timeStr.includes('T')) {
                return timeStr.split('T')[1].substring(0, 5);
            }
            // If it contains space, it might be 'YYYY-MM-DD HH:mm:ss'
            if (timeStr.includes(' ')) {
                return timeStr.split(' ')[1].substring(0, 5);
            }
            return timeStr.substring(0, 5);
        };

        document.getElementById('time_in_expected').value = formatTime(shift.time_in_expected);
        document.getElementById('time_out_expected').value = formatTime(shift.time_out_expected);
        
        // Logical check for night shift
        const isNight = shift.is_night ?? (shift.date_out != shift.date_in);
        document.getElementById('is_night').value = isNight ? '1' : '0';
        
        modal.classList.remove('hidden');
    }

    function confirmDelete(id, code) {
        Swal.fire({
            title: 'Hapus Shift?',
            text: `Apakah Anda yakin ingin menghapus shift ${code}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000000',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            borderRadius: '24px'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = `${updateUrlBase}/${id}`;
                deleteForm.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            }
        });
    }

    window.onclick = function(event) {
        if (event.target == modal) closeShiftModal();
    }
</script>
@endpush
@endsection

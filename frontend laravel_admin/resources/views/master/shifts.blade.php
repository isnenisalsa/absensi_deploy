@extends('layouts.app')

@section('title', 'Manajemen Shift Kerja')

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
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Shift Kerja</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i  class="fa-solid fa-clock w-5 h-5" ></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Pengaturan Shift</h1>
            </div>
        </div>

        <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
            <i  class="fa-solid fa-plus w-4 h-4" ></i> TAMBAH SHIFT
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
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                <i class="fa-solid fa-list w-5 h-5 text-blue-500"></i> Daftar Shift (<span id="totalShift">{{ count($shifts) }}</span>)
            </h3>
            <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border border-blue-100 shadow-sm">
                Live Data
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="py-5 px-6 text-[10px] font-black text-slate-500 uppercase tracking-widest">KODE</th>
                            <th class="py-5 px-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">JADWAL MASUK</th>
                            <th class="py-5 px-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">JADWAL PULANG</th>
                            <th class="py-5 px-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">STATUS HARI</th>
                            <th class="py-5 px-6 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($shifts as $shift)
                        @php
                            $dIn = \Carbon\Carbon::parse($shift['time_in_expected'])->timezone('UTC')->format('H:i');
                            $dOut = \Carbon\Carbon::parse($shift['time_out_expected'])->timezone('UTC')->format('H:i');
                            $isNight = isset($shift['date_out']) && $shift['date_out'] != $shift['date_in'];
                        @endphp
                        <tr class="hover:bg-blue-50/20 transition-all duration-300 group">
                            <td class="py-5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-[10px] border border-indigo-100">
                                        {{ substr($shift['shift_code'], 0, 2) }}
                                    </div>
                                    <span class="text-[13px] font-black text-slate-800 tracking-tight">{{ $shift['shift_code'] }}</span>
                                </div>
                            </td>
                            <td class="py-5 px-6 text-center">
                                <span class="bg-emerald-50 text-emerald-700 font-black text-[11px] px-3 py-1.5 rounded-xl border border-emerald-100 shadow-sm">
                                    <i class="fa-solid fa-right-to-bracket mr-1.5 opacity-50"></i>{{ $dIn }}
                                </span>
                            </td>
                            <td class="py-5 px-6 text-center">
                                <span class="bg-rose-50 text-rose-700 font-black text-[11px] px-3 py-1.5 rounded-xl border border-rose-100 shadow-sm">
                                    <i class="fa-solid fa-right-from-bracket mr-1.5 opacity-50"></i>{{ $dOut }}
                                </span>
                            </td>
                            <td class="py-5 px-6 text-center">
                                @if($isNight)
                                    <span class="inline-flex items-center gap-1.5 bg-slate-800 text-white font-black text-[9px] px-2.5 py-1 rounded-full shadow-md uppercase tracking-wider">
                                        <i class="fa-solid fa-moon text-yellow-400"></i> Lintas Hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 font-black text-[9px] px-2.5 py-1 rounded-full border border-blue-100 shadow-sm uppercase tracking-wider">
                                        <i class="fa-solid fa-sun text-orange-400"></i> Hari Sama
                                    </span>
                                @endif
                            </td>
                            <td class="py-5 px-6 text-right">
                                <button type="button" onclick="editShift({{ json_encode($shift) }}, '{{ $dIn }}', '{{ $dOut }}')" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-600 hover:bg-blue-50 transition-all duration-300 shadow-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" onclick="confirmDelete('{{ $shift['shift_id'] }}', '{{ $shift['shift_code'] }}')" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-600 hover:bg-rose-50 transition-all duration-300 shadow-sm ml-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-2">
                                        <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
                                    </div>
                                    <p class="text-slate-400 font-bold text-sm">Belum ada data shift kerja.</p>
                                    <button onclick="openCreateModal()" class="text-blue-600 font-black text-xs hover:underline decoration-2">TAMBAH SHIFT PERTAMA</button>
                                </div>
                            </td>
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
    <div id="modalOverlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4">
        <div class="px-8 py-6 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 id="modalTitle" class="font-black text-slate-800 text-[18px]">Tambah Shift Baru</h3>
                <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Informasi Jadwal Kerja</p>
            </div>
            <button type="button" onclick="closeModal()" class="w-10 h-10 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-slate-800 transition-all shadow-sm flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form id="shiftForm" method="POST" action="{{ route('master.shifts.store') }}" class="p-8">
            @csrf
            <input type="hidden" name="_method" value="POST" id="methodField">
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Kode Shift -->
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1 flex items-center gap-2">
                        <i class="fa-solid fa-tag text-blue-500"></i> Kode Shift
                    </label>
                    <input type="text" id="shiftCodeInput" name="shift_code" class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-5 text-[14px] text-slate-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-inner" required placeholder="Contoh: S1, NIGHT, ROSTER..."/>
                </div>

                <!-- Waktu & Offset -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-6">
                        <div class="flex flex-col">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1 flex items-center gap-2">
                                <i class="fa-solid fa-clock-arrow-up text-emerald-500"></i> Jam Masuk
                            </label>
                            <input type="time" id="timeInInput" name="time_in_expected" class="w-full bg-emerald-50/30 border border-emerald-100 rounded-2xl py-4 px-5 text-[14px] text-emerald-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner" required/>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex flex-col">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1 flex items-center gap-2">
                                <i class="fa-solid fa-clock-arrow-down text-rose-500"></i> Jam Pulang
                            </label>
                            <input type="time" id="timeOutInput" name="time_out_expected" class="w-full bg-rose-50/30 border border-rose-100 rounded-2xl py-4 px-5 text-[14px] text-rose-800 font-black outline-none focus:bg-white focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all shadow-inner" required/>
                        </div>
                    </div>
                </div>

                <!-- Night Shift Settings -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 block">Pengaturan Lintas Hari (Shift Malam)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-400 mb-2 uppercase">Status Pulang:</span>
                            <select name="is_night" id="nightShiftToggle" class="bg-white border border-slate-200 rounded-xl py-2 px-3 text-[11px] font-black text-slate-700 outline-none focus:border-blue-500">
                                <option value="0">DI HARI YANG SAMA</option>
                                <option value="1">DI HARI BERIKUTNYA</option>
                            </select>
                        </div>
                        <div class="flex items-end text-[10px] text-slate-400 font-bold leading-tight pb-1">
                            Pilih "Hari Berikutnya" jika jam pulang melewati pukul 00:00.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex gap-3">
                <button type="submit" class="flex-1 py-4 bg-slate-900 text-white font-black text-[12px] rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all active:scale-[0.98] uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div id="deleteModalContainer" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeDeleteModal()"></div>
    <div class="bg-white w-full max-w-sm rounded-[32px] shadow-2xl relative z-10 overflow-hidden animate-fade-in-up mx-4 p-10 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-3xl bg-rose-50 text-rose-600 flex items-center justify-center mb-6 shadow-inner">
            <i class="fa-solid fa-trash-can text-3xl"></i>
        </div>
        <h3 class="text-xl font-[900] text-slate-800 mb-2">Hapus Shift?</h3>
        <p class="text-slate-500 text-[13px] font-bold mb-10 leading-relaxed">
            Data shift <span id="delShiftCode" class="text-rose-600"></span> akan dihapus dari sistem untuk selamanya.
        </p>
        <div class="flex w-full gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 font-black text-[11px] rounded-2xl hover:bg-slate-200 transition-all uppercase tracking-widest">
                BATAL
            </button>
            <form id="deleteForm" method="POST" action="" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-4 bg-rose-600 text-white font-black text-[11px] rounded-2xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/30 uppercase tracking-widest">
                    HAPUS
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
    const form = document.getElementById('shiftForm');
    const methodField = document.getElementById('methodField');
    const shiftCodeInput = document.getElementById('shiftCodeInput');
    const timeInInput = document.getElementById('timeInInput');
    const timeOutInput = document.getElementById('timeOutInput');
    const nightShiftToggle = document.getElementById('nightShiftToggle');
    const modalTitle = document.getElementById('modalTitle');
    const delShiftCode = document.getElementById('delShiftCode');
    const deleteForm = document.getElementById('deleteForm');
    
    const storeUrl = "{{ route('master.shifts.store') }}";
    const updateUrlBase = "{{ url('master/shifts') }}"; 

    function openCreateModal() {
        methodField.value = 'POST';
        form.action = storeUrl;
        shiftCodeInput.value = '';
        timeInInput.value = '';
        timeOutInput.value = '';
        nightShiftToggle.value = '0';
        
        modalTitle.innerText = "Tambah Shift Baru";
        modalContainer.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function editShift(shift, tIn, tOut) {
        form.action = `${updateUrlBase}/${shift.shift_id}`;
        methodField.value = 'PUT';
        
        shiftCodeInput.value = shift.shift_code;
        timeInInput.value = tIn;
        timeOutInput.value = tOut;
        
        // Logical check for night shift
        const isNight = shift.date_out && shift.date_out !== shift.date_in;
        nightShiftToggle.value = isNight ? '1' : '0';
        
        modalTitle.innerText = `Edit: ${shift.shift_code}`;
        modalContainer.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function closeModal() {
        modalContainer.classList.add('hidden');
        toggleSidebarBlur(false);
    }

    function confirmDelete(id, code) {
        deleteForm.action = `${updateUrlBase}/${id}`;
        delShiftCode.innerText = code;
        deleteModalContainer.classList.remove('hidden');
        toggleSidebarBlur(true);
    }

    function closeDeleteModal() {
        deleteModalContainer.classList.add('hidden');
        toggleSidebarBlur(false);
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteModal();
        }
    });
</script>
@endpush

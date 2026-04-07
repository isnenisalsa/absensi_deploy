@extends('layouts.app')

@section('title', 'Geofence & Lokasi')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 400px; border-radius: 1rem; z-index: 10; cursor: crosshair; }
</style>
@endpush

@section('content')
<div class="p-6 md:p-10 w-full max-w-[1400px] mx-auto animate-fade-in-up">
    
    <!-- Premium Header & Breadcrumbs -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="text-[11px] font-medium text-slate-500 mb-3 flex items-center gap-2">
                <span class="hover:text-blue-600 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-chart-line text-[12px]"></i> Report
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-blue-700 font-bold bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] tracking-wide shadow-sm">Pengaturan Lokasi & Geofence</span>
            </div>
            <!-- Title -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#0052cc] to-blue-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-map-location-dot text-[20px]"></i>
                </div>
                <h1 class="text-2xl md:text-[28px] font-[900] text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight leading-none">Radius Geofence Presensi</h1>
            </div>
        </div>
        
        <div class="hidden md:block max-w-sm text-xs font-medium text-slate-500 text-right leading-relaxed">
            Sesuaikan titik pusat koordinat (Latitude/Longitude) dan leluasa ubah batas radius Absensi Karyawan Anda yang terhubung dengan API batas jangkauan aplikasi Flutter.
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: List Locations -->
        <div class="lg:col-span-4 flex flex-col gap-4">
            <h3 class="font-extrabold text-slate-800 text-[15px] flex justify-between items-center">
                <span class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-emerald-500"></i> Daftar Lokasi Kerja</span>
                <button onclick="createNewLocation()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah
                </button>
            </h3>
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden flex flex-col">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-100 flex flex-col gap-3">
                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest flex justify-between items-center">
                        <span>Pilih Lokasi</span>
                        <span class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full transition-all" id="locationCountbadge">{{ count($locations) }} Data</span>
                    </div>
                    <!-- Search Input -->
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-[12px]"></i>
                        <input type="text" id="searchLocationInput" placeholder="Cari nama lokasi/area..." class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-9 pr-3 text-[12px] font-semibold text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                    </div>
                </div>
                <div class="flex flex-col max-h-[550px] overflow-y-auto divide-y divide-slate-100">
                    @forelse($locations as $loc)
                        <button type="button" onclick='selectLocation({{ json_encode($loc) }})' class="location-item-btn px-5 py-4 hover:bg-blue-50/50 transition-colors text-left group focus:bg-blue-50 focus:outline-none w-full block">
                            <h4 class="location-title-text font-extrabold text-[#111827] text-[13px] group-hover:text-[#0052cc] transition-colors mb-1">{{ $loc['location_name'] }}</h4>
                            <div class="flex items-center justify-between text-[11px] font-medium text-slate-500">
                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-map-pin text-rose-500 text-[12px]"></i> 
                                    @if($loc['latitude'] && $loc['longitude'])
                                        {{ Str::limit($loc['latitude'].', '.$loc['longitude'], 18) }}
                                    @else
                                        <span class="italic text-rose-400">Belum diatur</span>
                                    @endif
                                </span>
                                <span class="flex items-center gap-1.5 font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                    <i class="fa-solid fa-expand text-[10px]"></i> {{ $loc['radius_meters'] ?? 50 }}m
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs font-bold">Belum ada data Lokasi Kerja.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Map & Form -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <!-- Map Card -->
            <div class="bg-white p-2 rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100">
                <div class="w-full relative rounded-xl overflow-hidden shadow-inner border border-slate-100 group">
                    
                    <!-- Search Bar overlay -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-[400] w-11/12 max-w-md flex items-center bg-white rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.12)] p-1.5 border border-slate-200/80 transition-all focus-within:ring-4 focus-within:ring-blue-500/20" id="mapSearchWrapper" style="display: none;">
                        <input type="text" id="mapSearchInput" placeholder="Cari kota, alamat, atau daerah tujuan PETA..." class="w-full bg-transparent border-none text-[13px] font-bold text-slate-800 outline-none px-3 focus:ring-0 placeholder:font-semibold placeholder:text-slate-400">
                        <button type="button" id="mapSearchBtn" class="bg-gradient-to-r from-blue-600 to-[#0052cc] hover:from-blue-700 hover:to-blue-800 text-white p-2.5 rounded-lg transition-colors flex-shrink-0 shadow-md">
                            <i class="fa-solid fa-magnifying-glass text-[14px]"></i>
                        </button>
                    </div>

                    <div id="map" class="w-full h-[400px] relative"></div>
                    
                    <!-- Overlay if none selected -->
                    <div id="mapOverlay" class="absolute inset-0 z-[1000] bg-slate-900/5 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
                        <div class="bg-white px-6 py-4 rounded-xl shadow-2xl border border-slate-200/60 font-extrabold text-slate-700 text-sm flex items-center gap-3 animate-pulse">
                            <div class="p-2 bg-blue-50 rounded-lg"><i class="fa-solid fa-arrow-pointer text-[#0052cc] text-[18px]"></i></div>
                            Pilih daftar lokasi kerja di sebelah kiri terlebih dahulu!
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form id="geofenceForm" method="POST" action="" class="bg-white p-7 md:p-8 rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 transition-all opacity-50 pointer-events-none relative overflow-hidden">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="methodField">
                
                <!-- Background Decoration -->
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-5">
                    <h3 class="font-extrabold text-slate-800 text-[17px] flex items-center gap-2 tracking-tight">
                        <i class="fa-solid fa-crosshairs text-[#0052cc] text-[18px]"></i>
                        Konfigurasi: <span id="locNameDisplay" class="text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100 ml-1">...</span>
                    </h3>
                </div>

                <!-- Input Nama Khusus Add New -->
                <div class="mb-6 hidden" id="nameInputWrapper">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Nama Lokasi Kerja</label>
                    <input type="text" id="nameInput" name="location_name" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" placeholder="Contoh: AREA BLOK A / MESS KARYAWAN"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8 relative z-10">
                    <div class="flex flex-col md:col-span-4">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Latitude</label>
                        <input type="text" id="latInput" name="latitude" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="-6.175110"/>
                    </div>
                    <div class="flex flex-col md:col-span-4">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Longitude</label>
                        <input type="text" id="lngInput" name="longitude" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 px-4 text-[13px] text-slate-800 font-bold outline-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm" required placeholder="106.827153"/>
                    </div>
                    <div class="flex flex-col md:col-span-4">
                        <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Radius (Meter)</label>
                        <div class="relative flex items-center gap-4 bg-slate-50/50 border border-slate-200 rounded-xl py-2 px-3 shadow-sm focus-within:ring-4 focus-within:ring-blue-500/10 focus-within:border-blue-500 focus-within:bg-white transition-all">
                            <input type="range" id="radiusSlider" min="10" max="2000" step="5" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600"/>
                            <input type="number" id="radiusInput" name="radius_meters" class="w-16 bg-white border border-slate-200 rounded-lg py-1 text-center text-[12px] font-extrabold text-[#0052cc] shadow-sm outline-none" required/>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-2">
                    <button type="button" id="btnDeleteGeofence" onclick="deleteCurrentGeofence()" class="hidden w-full sm:w-auto px-6 py-3.5 bg-red-50 hover:bg-red-100 text-red-600 font-extrabold text-[12px] rounded-xl transition-all uppercase tracking-widest items-center justify-center gap-2 border border-red-100">
                        <i class="fa-solid fa-trash-can text-[14px]"></i> HAPUS AREAL
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-[#0052cc] to-blue-600 hover:from-[#0047b3] hover:to-blue-700 text-white font-extrabold text-[12px] rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 active:scale-95 uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fa-solid fa-satellite text-[14px]"></i> SIMPAN KOORDINAT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map = L.map('map').setView([-6.175110, 106.827153], 15);
    
    const mapDefault = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap', subdomains: 'abcd', maxZoom: 20
    });
    
    const mapSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri', maxZoom: 20
    });
    
    mapDefault.addTo(map);

    const baseMaps = {
        "🗺️ Peta Standard": mapDefault,
        "🌍 Mode Satelit": mapSatellite
    };
    L.control.layers(baseMaps, null, { position: 'bottomright' }).addTo(map);

    let currentMarker;
    let currentCircle;
    const overlay = document.getElementById('mapOverlay');
    const form = document.getElementById('geofenceForm');
    const storeUrlBase = "{{ route('report.geofence.store') }}"; 
    const updateUrlBase = "{{ url('report/geofence') }}";
    const methodField = document.getElementById('methodField');
    
    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');
    const radiusInput = document.getElementById('radiusInput');
    const radiusSlider = document.getElementById('radiusSlider');
    const locNameDisplay = document.getElementById('locNameDisplay');

    const customIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    });

    function updateMapFeatures(lat, lng, radius) {
        if(currentMarker) map.removeLayer(currentMarker);
        if(currentCircle) map.removeLayer(currentCircle);

        let pos = [lat, lng];
        currentMarker = L.marker(pos, {draggable: true, icon: customIcon}).addTo(map);
        currentCircle = L.circle(pos, {
            color: '#10b981', weight: 2, fillColor: '#34d399', fillOpacity: 0.25, radius: radius
        }).addTo(map);

        map.setView(pos, 16);

        currentMarker.on('dragend', function (e) {
            let position = currentMarker.getLatLng();
            latInput.value = position.lat.toFixed(8);
            lngInput.value = position.lng.toFixed(8);
            currentCircle.setLatLng(position);
        });
    }

    map.on('click', function(e) {
        if(!form.action || form.action.endsWith('geofence')) return;
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;
        let radius = parseInt(radiusInput.value) || 50;

        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        updateMapFeatures(lat, lng, radius);
    });

    [radiusInput, radiusSlider].forEach(el => {
        el.addEventListener('input', function(e) {
            let val = parseInt(e.target.value);
            if(isNaN(val)) return;
            radiusInput.value = val;
            radiusSlider.value = val;
            if(currentCircle) currentCircle.setRadius(val);
        });
    });
    
    [latInput, lngInput].forEach(el => {
        el.addEventListener('change', function() {
            let lat = parseFloat(latInput.value);
            let lng = parseFloat(lngInput.value);
            let radius = parseInt(radiusInput.value) || 50;
            if(!isNaN(lat) && !isNaN(lng)) updateMapFeatures(lat, lng, radius);
        });
    });

    const mapSearchInput = document.getElementById('mapSearchInput');
    const mapSearchBtn = document.getElementById('mapSearchBtn');
    const mapSearchWrapper = document.getElementById('mapSearchWrapper');

    function performMapSearch() {
        let query = mapSearchInput.value.trim();
        if(!query) return;
        mapSearchBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i>';
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if(data && data.length > 0) {
                    let lat = parseFloat(data[0].lat);
                    let lng = parseFloat(data[0].lon);
                    let radius = parseInt(radiusInput.value) || 50;
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                    updateMapFeatures(lat, lng, radius);
                    map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });
                }
            })
            .finally(() => { mapSearchBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>'; });
    }

    mapSearchBtn.addEventListener('click', performMapSearch);
    mapSearchInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') performMapSearch(); });

    const searchLocationInput = document.getElementById('searchLocationInput');
    const locationItems = document.querySelectorAll('.location-item-btn');
    
    if(searchLocationInput) {
        searchLocationInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            locationItems.forEach(item => {
                const title = item.querySelector('.location-title-text').innerText.toLowerCase();
                item.style.display = title.includes(searchTerm) ? 'block' : 'none';
            });
        });
    }

    window.selectLocation = function(locData) {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.classList.add('hidden'), 300);
        form.classList.remove('opacity-50', 'pointer-events-none');
        mapSearchWrapper.style.display = 'flex';
        
        let delBtn = document.getElementById('btnDeleteGeofence');
        delBtn.classList.remove('hidden');
        delBtn.classList.add('flex');
        
        if(methodField) {
            methodField.disabled = false;
            methodField.value = 'PUT';
        }
        document.getElementById('nameInputWrapper').classList.add('hidden');
        document.getElementById('nameInput').required = false;

        form.action = `${updateUrlBase}/${locData.location_id}`;
        locNameDisplay.textContent = locData.location_name;

        let lat = locData.latitude ? parseFloat(locData.latitude) : -6.175110;
        let lng = locData.longitude ? parseFloat(locData.longitude) : 106.827153;
        let radius = locData.radius_meters ? parseInt(locData.radius_meters) : 50;

        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        radiusInput.value = radius;
        radiusSlider.value = radius;

        updateMapFeatures(lat, lng, radius);
        setTimeout(() => map.invalidateSize(), 400);
    }

    window.createNewLocation = function() {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.classList.add('hidden'), 300);
        form.classList.remove('opacity-50', 'pointer-events-none');
        mapSearchWrapper.style.display = 'flex';
        document.getElementById('btnDeleteGeofence').classList.add('hidden');
        
        if(methodField) methodField.disabled = true;
        form.action = storeUrlBase;
        
        document.getElementById('nameInputWrapper').classList.remove('hidden');
        document.getElementById('nameInput').required = true;
        document.getElementById('nameInput').value = '';

        locNameDisplay.textContent = "BUAT LOKASI BARU";
        let lat = -6.175110, lng = 106.827153, radius = 50;
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        radiusInput.value = radius; radiusSlider.value = radius;
        updateMapFeatures(lat, lng, radius);
        setTimeout(() => map.invalidateSize(), 400);
    };

    window.deleteCurrentGeofence = function() {
        if(confirm("Yakin ingin menghapus lokasi ini?")) {
            methodField.disabled = false;
            methodField.value = "DELETE";
            form.submit();
        }
    };
</script>
@endpush

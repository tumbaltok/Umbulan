@extends('layouts.app')
@section('title', 'Daftar Stasiun Kerja')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Mengunci ukuran container peta */
    #stationMap {
        height: 320px !important;
        width: 100% !important;
        z-index: 10 !important;
        background-color: #f8fafc;
    }

    /* Mencegah Tailwind CSS merusak style tile gambar Leaflet */
    .leaflet-container img {
        max-width: none !important;
        max-height: none !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4">

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
            <i class="fa-solid fa-circle-xmark mr-1.5 text-rose-600"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Sektor / Stasiun Kerja</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manajemen titik lokasi wilayah kerja untuk distribusi karyawan dan validasi absensi GPS.</p>
            </div>
            <button type="button" onclick="bukaModalTambahStasiun()" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Tambah Stasiun Baru
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4 w-24">Kode</th>
                        <th class="px-6 py-4">Nama Sektor / Stasiun</th>
                        <th class="px-6 py-4 text-center">Radius GPS</th>
                        <th class="px-6 py-4 text-center">Total Penempatan Staf</th>
                        <th class="px-6 py-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarStasiun as $stasiun)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600 uppercase">{{ $stasiun->kode_stasiun }}</td>

                            {{-- KLIK NAMA STASIUN UNTUK MEMBUKA POPUP PETA GPS --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <div class="flex items-center space-x-2.5 cursor-pointer hover:text-sky-600 transition-colors btn-view-map group"
                                     data-name="{{ $stasiun->name }}" 
                                     data-lat="{{ $stasiun->latitude }}" 
                                     data-lng="{{ $stasiun->longitude }}">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500 group-hover:scale-125 transition-transform"></div>
                                    <span>{{ $stasiun->name }}</span>
                                    <i class="fa-solid fa-map-location-dot text-xs text-sky-500 ml-1 opacity-70 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center font-mono text-xs font-semibold text-slate-600">
                                {{ $stasiun->radius_meters }} Meter
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span data-id="{{ $stasiun->id }}" data-name="{{ $stasiun->name }}"
                                    class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200
                                    {{ $stasiun->total_karyawan > 0 ? 'bg-sky-50 text-sky-700 border border-sky-100 hover:bg-sky-100 hover:text-sky-800 cursor-pointer shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                    {{ $stasiun->total_karyawan }} Orang
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-stasiun='@json($stasiun)'
                                            onclick="bukaModalEditStasiun(this)"
                                            class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs transition-colors" 
                                            title="Edit Stasiun">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.stations.destroy', $stasiun->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus stasiun ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs transition-colors" title="Hapus Stasiun">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-map-location-dot text-3xl mb-2 block text-slate-200"></i>
                                Belum ada data stasiun kerja yang diinput ke dalam database erp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL FORM TAMBAH / EDIT STASIUN --}}
<div id="modalFormStasiun" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalFormStasiun()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-base" id="judulModalForm">Tambah Stasiun Kerja</h3>
            <button type="button" onclick="tutupModalFormStasiun()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formStasiunAction" action="{{ route('admin.stations.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="methodFormStasiun" value="POST">

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Stasiun</label>
                    <input type="text" name="kode_stasiun" id="input_kode_stasiun" required placeholder="UMB" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs uppercase focus:outline-none focus:border-sky-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Stasiun / Sektor</label>
                    <input type="text" name="name" id="input_name" required placeholder="Stasiun Umbulan" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500">
                </div>
            </div>

            {{-- INPUT PASTE URL GOOGLE MAPS --}}
            <div class="p-3 bg-sky-50/50 border border-sky-100 rounded-xl space-y-2">
                <label class="block text-xs font-bold text-sky-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-link"></i> Tempel (Paste) Link Google Maps
                </label>
                <input type="url" id="input_maps_url" name="maps_url" oninput="autoParseMapsUrl(this.value)" placeholder="https://www.google.com/maps/place/.../@-7.182341,113.241512,17z/..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500">
                <p class="text-[10px] text-slate-500 leading-relaxed">* Tempelkan link lokasi dari Google Maps untuk mengekstrak latitude & longitude secara otomatis.</p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                    <input type="text" name="latitude" id="input_latitude" required placeholder="-7.123456" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                    <input type="text" name="longitude" id="input_longitude" required placeholder="112.654321" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Radius (M)</label>
                    <input type="number" name="radius_meters" id="input_radius" required value="1000" min="10" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500 font-mono">
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalFormStasiun()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm">Simpan Stasiun</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL POPUP PETA LOKASI STASIUN --}}
<div id="stationMapModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="mapModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative z-10 transform transition-all m-4 flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-base" id="mapModalTitle">Peta Lokasi Stasiun</h3>
                <p id="mapModalCoords" class="text-xs text-sky-600 font-mono mt-0.5">-</p>
            </div>
            <button type="button" id="closeMapModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Container Peta --}}
        <div class="my-4 rounded-xl overflow-hidden border border-slate-200 shadow-inner">
            <div id="stationMap" class="w-full h-80 z-0"></div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
            <a id="btnOpenGoogleMaps" href="#" target="_blank" class="px-3.5 py-2 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-location-arrow"></i> Buka di Google Maps
            </a>
            <button type="button" id="closeMapModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP VIEW DAFTAR KARYAWAN PER STASIUN --}}
<div id="staffStationModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="staffModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative z-10 transform transition-all m-4 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Daftar Anggota Staf</h3>
                <p id="modalStationTitle" class="text-xs text-sky-600 font-medium mt-0.5"></p>
            </div>
            <button type="button" id="closeStaffModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalStaffLoading" class="py-12 text-center my-auto">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Menarik data staf...</p>
        </div>

        <div id="modalStaffContent" class="hidden overflow-y-auto my-4 flex-1 pr-1">
            <table class="w-full text-left border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                        <th class="px-6 pb-1">Nama Lengkap</th>
                        <th class="px-6 pb-1">Jabatan</th>
                    </tr>
                </thead>
                <tbody id="staffListContainer">
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeStaffModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP DETAIL LENGKAP KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoadingDetail" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        <div id="modalDataContentDetail" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100"></p>
            </div>

            <div class="border-t border-slate-100 pt-4 grid grid-cols-1 gap-y-4 text-sm">
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">NIP</span>
                    <span id="detail_nip" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">Email</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_email" class="text-slate-800 font-semibold truncate">-</span>
                        <a id="detail_email_link" href="#" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-sky-500 hover:bg-sky-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">No. Telepon</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_phone" class="text-slate-800 font-semibold">-</span>
                        <a id="detail_phone_link" href="#" target="_blank" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chat WA</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">Jobdesk</span>
                    <span id="detail_job" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 pb-2">
                    <span class="text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // AUTO PARSE GOOGLE MAPS URL DI JAVASCRIPT
    function autoParseMapsUrl(url) {
        if (!url) return;

        let match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (!match) match = url.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (!match) match = url.match(/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/);

        if (match && match[1] && match[2]) {
            document.getElementById('input_latitude').value = match[1];
            document.getElementById('input_longitude').value = match[2];
        }
    }

    function bukaModalTambahStasiun() {
        document.getElementById('judulModalForm').innerText = 'Tambah Stasiun Kerja';
        document.getElementById('formStasiunAction').action = "{{ route('admin.stations.store') }}";
        document.getElementById('methodFormStasiun').value = 'POST';

        document.getElementById('input_kode_stasiun').value = '';
        document.getElementById('input_name').value = '';
        document.getElementById('input_maps_url').value = '';
        document.getElementById('input_latitude').value = '';
        document.getElementById('input_longitude').value = '';
        document.getElementById('input_radius').value = '1000';

        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function bukaModalEditStasiun(button) {
        const stasiun = JSON.parse(button.getAttribute('data-stasiun'));

        document.getElementById('judulModalForm').innerText = 'Edit Stasiun Kerja';
        document.getElementById('formStasiunAction').action = `/admin/stations/${stasiun.id}`;
        document.getElementById('methodFormStasiun').value = 'PUT';

        document.getElementById('input_kode_stasiun').value = stasiun.kode_stasiun;
        document.getElementById('input_name').value = stasiun.name;
        document.getElementById('input_maps_url').value = `https://www.google.com/maps?q=${stasiun.latitude},${stasiun.longitude}`;
        document.getElementById('input_latitude').value = stasiun.latitude;
        document.getElementById('input_longitude').value = stasiun.longitude;
        document.getElementById('input_radius').value = stasiun.radius_meters;

        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalFormStasiun() {
        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    let mapInstance = null;
    let markerInstance = null;

    document.addEventListener("DOMContentLoaded", function () {
        const mapModal = document.getElementById("stationMapModal");
        const backdropMap = document.getElementById("mapModalBackdrop");
        const closeMapBtn = document.getElementById("closeMapModalBtn");
        const closeMapBtn2 = document.getElementById("closeMapModalBtn2");
        const mapContainer = document.getElementById("stationMap");

        // Definisi Icon Marker
        const customMarkerIcon = L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // RESIZE OBSERVER: Memaksa Leaflet menyesuaikan ukuran peta secara otomatis
        const mapResizeObserver = new ResizeObserver(() => {
            if (mapInstance) {
                mapInstance.invalidateSize();
            }
        });
        mapResizeObserver.observe(mapContainer);

        function openMapModal() {
            mapModal.classList.remove("hidden");
            mapModal.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function closeMapModal() {
            mapModal.classList.remove("flex");
            mapModal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        if (closeMapBtn) closeMapBtn.addEventListener("click", closeMapModal);
        if (closeMapBtn2) closeMapBtn2.addEventListener("click", closeMapModal);
        if (backdropMap) backdropMap.addEventListener("click", closeMapModal);

        document.querySelectorAll(".btn-view-map").forEach(item => {
            item.addEventListener("click", function () {
                const name = this.getAttribute("data-name");
                const lat = parseFloat(this.getAttribute("data-lat"));
                const lng = parseFloat(this.getAttribute("data-lng"));

                if (isNaN(lat) || isNaN(lng)) {
                    alert("Koordinat lokasi untuk stasiun ini belum diatur di database.");
                    return;
                }

                openMapModal();

                document.getElementById("mapModalTitle").textContent = `Lokasi Presensi: ${name}`;
                document.getElementById("mapModalCoords").textContent = `Koordinat GPS: ${lat}, ${lng}`;
                document.getElementById("btnOpenGoogleMaps").href = `https://www.google.com/maps?q=${lat},${lng}`;

                // Hapus instance peta lama jika ada
                if (mapInstance !== null) {
                    mapInstance.remove();
                    mapInstance = null;
                }

                // Inisialisasi Peta Leaflet + OpenStreetMap
                mapInstance = L.map('stationMap', {
                    center: [lat, lng],
                    zoom: 16
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
                }).addTo(mapInstance);

                markerInstance = L.marker([lat, lng], { icon: customMarkerIcon }).addTo(mapInstance)
                    .bindPopup(`<b>${name}</b><br>Titik Validasi Absensi GPS`)
                    .openPopup();

                // Pemicu ganda untuk memastikan peta penuh di segala kondisi
                setTimeout(() => {
                    if (mapInstance) {
                        mapInstance.invalidateSize();
                    }
                }, 400);
            });
        });

        // --- 2. LOGIKA MODAL LIST STAF PER STASIUN ---
        const modalStaff = document.getElementById("staffStationModal");
        const backdropStaff = document.getElementById("staffModalBackdrop");
        const closeStaffBtn = document.getElementById("closeStaffModalBtn");
        const closeStaffBtn2 = document.getElementById("closeStaffModalBtn2");

        const loadingSectionStaff = document.getElementById("modalStaffLoading");
        const contentSectionStaff = document.getElementById("modalStaffContent");
        const stationTitle = document.getElementById("modalStationTitle");
        const staffContainer = document.getElementById("staffListContainer");

        // --- 3. LOGIKA MODAL DETAIL LENGKAP KARYAWAN ---
        const modalDetail = document.getElementById("detailKaryawanModal");
        const backdropDetail = document.getElementById("detailModalBackdrop");
        const closeDetailBtn = document.getElementById("closeDetailModalBtn");
        const closeDetailBtn2 = document.getElementById("closeDetailModalBtn2");

        const loadingSectionDetail = document.getElementById("modalLoadingDetail");
        const contentSectionDetail = document.getElementById("modalDataContentDetail");

        function openStaffModal() {
            modalStaff.classList.remove("hidden");
            modalStaff.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function closeStaffModal() {
            modalStaff.classList.remove("flex");
            modalStaff.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        function openDetailModal() {
            modalDetail.classList.remove("hidden");
            modalDetail.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function closeDetailModal() {
            modalDetail.classList.remove("flex");
            modalDetail.classList.add("hidden");
            if (modalStaff.classList.contains("hidden")) {
                document.body.classList.remove("overflow-hidden");
            }
        }

        if (closeStaffBtn) closeStaffBtn.addEventListener("click", closeStaffModal);
        if (closeStaffBtn2) closeStaffBtn2.addEventListener("click", closeStaffModal);
        if (backdropStaff) backdropStaff.addEventListener("click", closeStaffModal);

        if (closeDetailBtn) closeDetailBtn.addEventListener("click", closeDetailModal);
        if (closeDetailBtn2) closeDetailBtn2.addEventListener("click", closeDetailModal);
        if (backdropDetail) backdropDetail.addEventListener("click", closeDetailModal);

        // FETCH DAFTAR STAF DALAM STASIUN
        document.querySelectorAll(".btn-view-staff").forEach(badge => {
            badge.addEventListener("click", function () {
                const stationId = this.getAttribute("data-id");
                const stationName = this.getAttribute("data-name");

                if (!stationId || this.classList.contains('cursor-not-allowed')) return;

                openStaffModal();
                stationTitle.textContent = `Stasiun Kerja: ${stationName}`;
                loadingSectionStaff.classList.remove("hidden");
                contentSectionStaff.classList.add("hidden");
                staffContainer.innerHTML = "";

                fetch(`/admin/stations/${stationId}/karyawan`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Gagal mengambil data staf (Status: ${response.status})`);
                        return response.json();
                    })
                    .then(karyawanList => {
                        loadingSectionStaff.classList.add("hidden");
                        contentSectionStaff.classList.remove("hidden");

                        if (!karyawanList || karyawanList.length === 0) {
                            staffContainer.innerHTML = `
                                <tr>
                                    <td colspan="2" class="text-center py-8 text-slate-400 text-sm bg-white rounded-2xl border border-slate-100 shadow-sm">
                                        Tidak ada karyawan yang aktif di stasiun ini.
                                    </td>
                                </tr>`;
                            return;
                        }

                        karyawanList.forEach(karyawan => {
                            const initials = karyawan.name ? karyawan.name.substring(0, 2).toUpperCase() : '??';
                            const photoHtml = karyawan.profile_photo
                                ? `<img src="/storage/${karyawan.profile_photo}" class="w-full h-full object-cover">`
                                : initials;

                            const roleName = karyawan.role_name || (karyawan.role ? karyawan.role.role_name : 'Staff');
                            let roleBadgeClass = 'bg-slate-100 text-slate-700 border border-slate-200/50';

                            if (roleName.toLowerCase() === 'manager') {
                                roleBadgeClass = 'bg-purple-50 text-purple-700 border border-purple-100';
                            } else if (roleName.toLowerCase() === 'supervisor') {
                                roleBadgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-100';
                            } else if (roleName.toLowerCase() === 'staff') {
                                roleBadgeClass = 'bg-sky-50 text-sky-700 border border-sky-100';
                            }

                            const tableRow = document.createElement("tr");
                            tableRow.className = "bg-white hover:bg-slate-50/50 transition-colors group shadow-sm border border-slate-100 rounded-2xl";
                            tableRow.innerHTML = `
                                <td class="px-6 py-4 font-medium text-slate-900 rounded-l-2xl border-y border-l border-slate-100">
                                    <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="${karyawan.id}">
                                        <div class="w-9 h-9 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden shrink-0">
                                            ${photoHtml}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors">${karyawan.name}</span>
                                            <span class="text-xs text-slate-400 mt-0.5">NIP: ${karyawan.nip || '-'}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle rounded-r-2xl border-y border-r border-slate-100">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block ${roleBadgeClass}">
                                        ${roleName}
                                    </span>
                                </td>
                            `;
                            staffContainer.appendChild(tableRow);
                        });
                    })
                    .catch(error => {
                        console.error(error);
                        loadingSectionStaff.classList.add("hidden");
                        staffContainer.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center py-8 text-rose-500 text-xs font-semibold bg-white rounded-2xl border border-slate-100 shadow-sm">
                                    ⚠️ Terjadi masalah sistem: ${error.message}
                                </td>
                            </tr>`;
                        contentSectionStaff.classList.remove("hidden");
                    });
            });
        });

        // FETCH POPUP DETAIL KARYAWAN SAAT NAMA DI-KLIK
        document.addEventListener("click", function(e) {
            const button = e.target.closest(".btn-detail-karyawan");
            if (button) {
                const karyawanId = button.getAttribute("data-id");

                openDetailModal();
                loadingSectionDetail.classList.remove("hidden");
                contentSectionDetail.classList.add("hidden");

                fetch(`/admin/karyawan/${karyawanId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
                        return response.json();
                    })
                    .then(data => {
                        if (!data || Object.keys(data).length === 0) throw new Error("Data karyawan kosong.");

                        loadingSectionDetail.classList.add("hidden");
                        contentSectionDetail.classList.remove("hidden");

                        document.getElementById("detail_name").textContent = data.name || '-';
                        document.getElementById("detail_nip").textContent = data.nip ? data.nip : '-';
                        document.getElementById("detail_role").textContent = data.role_name ? data.role_name : 'Tidak Ada Role';
                        document.getElementById("detail_station").textContent = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                        // LOGIKA EMAIL
                        const emailSpan = document.getElementById("detail_email");
                        const emailLink = document.getElementById("detail_email_link");

                        if (data.email) {
                            emailSpan.textContent = data.email;
                            emailLink.href = `mailto:${data.email}`;
                            emailLink.classList.remove("hidden");
                        } else {
                            emailSpan.textContent = '-';
                            emailLink.classList.add("hidden");
                        }

                        // LOGIKA PHONE (WHATSAPP)
                        const phoneSpan = document.getElementById("detail_phone");
                        const phoneLink = document.getElementById("detail_phone_link");

                        if (data.phone_number) {
                            phoneSpan.textContent = data.phone_number;

                            let cleanNumber = data.phone_number.replace(/[^0-9]/g, '');
                            if (cleanNumber.startsWith('0')) {
                                cleanNumber = '62' + cleanNumber.substring(1);
                            }

                            phoneLink.href = `https://wa.me/${cleanNumber}`;
                            phoneLink.classList.remove("hidden");
                        } else {
                            phoneSpan.textContent = '-';
                            phoneLink.classList.add("hidden");
                        }

                        let jobTitleText = 'Belum Memilih';
                        if(data.job_title == 'Operator' || data.job_title == '1') jobTitleText = 'Operator';
                        else if(data.job_title == 'Maintenance' || data.job_title == '2') jobTitleText = 'Maintenance';
                        else if(data.job_title == 'HSE' || data.job_title == '3') jobTitleText = 'Safety (HSE)';
                        else if(data.job_title == 'Dokumentasi' || data.job_title == '4') jobTitleText = 'Documenter';

                        document.getElementById("detail_job").textContent = jobTitleText;

                        const photoContainer = document.getElementById("detail_photo_container");
                        if (data.profile_photo) {
                            const img = document.createElement("img");
                            img.src = `/storage/${data.profile_photo}`;
                            img.className = "w-full h-full object-cover";
                            photoContainer.textContent = "";
                            photoContainer.appendChild(img);
                        } else {
                            const initials = data.name ? data.name.substring(0, 2).toUpperCase() : '??';
                            photoContainer.textContent = initials;
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        loadingSectionDetail.classList.add("hidden");
                        alert(`Terjadi kesalahan saat memuat data karyawan: ${error.message}`);
                        closeDetailModal();
                    });
            }
        });
    });
</script>
@endpush
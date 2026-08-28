@extends('layouts.app')
@section('title', 'Daftar Stasiun Kerja')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Mengunci ukuran container peta pratinjau */
    #stationMap {
        height: 320px !important;
        width: 100% !important;
        z-index: 10 !important;
        background-color: #f8fafc;
    }

    /* Leaflet Tile Styling di Mode Gelap (Dark Carto Filter) */
    html.dark .leaflet-tile-pane {
        filter: brightness(0.75) invert(1) contrast(1.2) hue-rotate(180deg) saturate(0.85);
    }

    html.dark .leaflet-container {
        background-color: #0f172a !important;
    }

    /* Mencegah Tailwind CSS merusak style tile gambar Leaflet */
    .leaflet-container img {
        max-width: none !important;
        max-height: none !important;
    }

    .leaflet-container {
        font-family: inherit;
    }

    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    /* Kustomisasi scrollbar halus pada form modal */
    .modal-scroll-area::-webkit-scrollbar {
        width: 6px;
    }
    .modal-scroll-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .modal-scroll-area::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.4);
        border-radius: 9999px;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4 space-y-8" 
     id="page-container"
     data-success="{{ session('success') }}"
     data-error="{{ session('error') }}"
     data-errors='@json($errors->all())'>

    @php
        // Pemisahan koleksi data stasiun berdasarkan tipe
        $stasiunUtama = isset($daftarStasiunUtama) ? $daftarStasiunUtama : $daftarStasiun->filter(function($s) {
            return ($s->type ?? 'stasiun') !== 'rumah_meter';
        });

        $stasiunRumahMeter = isset($daftarRumahMeter) ? $daftarRumahMeter : $daftarStasiun->filter(function($s) {
            return ($s->type ?? 'stasiun') === 'rumah_meter';
        });
    @endphp

    {{-- TABEL 1: KANTOR & STASIUN KERJA UTAMA --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-building-shield"></i>
                    </span>
                    Daftar Tempat Kerja Utama
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manajemen Kantor dan Stasiun Operasional untuk penempatan staf & validasi radius presensi GPS.</p>
            </div>
            @if(Auth::user()->isLevel1())
            <button type="button" onclick="bukaModalTambahStasiun('stasiun')" class="bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0">
                <i class="fa-solid fa-plus"></i> Tambah Lokasi Baru
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-6 py-4 w-24">Kode</th>
                        <th class="px-6 py-4">Nama Tempat Kerja</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">Radius GPS</th>
                        <th class="px-6 py-4 text-center">Total Penempatan Staf</th>
                        @if(Auth::user()->isLevel1())
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70 text-slate-700 dark:text-slate-200 text-sm">
                    @forelse($stasiunUtama as $stasiun)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">{{ $stasiun->kode_stasiun }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center space-x-2.5 cursor-pointer hover:text-sky-600 dark:hover:text-sky-400 transition-colors btn-view-map group"
                                     data-name="{{ $stasiun->name }}" 
                                     data-lat="{{ $stasiun->latitude }}" 
                                     data-lng="{{ $stasiun->longitude }}"
                                     data-radius="{{ $stasiun->radius_meters ?? 1000 }}">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500 group-hover:scale-125 transition-transform shrink-0"></div>
                                    <span>{{ $stasiun->name }}</span>
                                    <i class="fa-solid fa-map-location-dot text-xs text-sky-500 ml-1 opacity-70 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(($stasiun->type ?? 'stasiun') === 'kantor')
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800/60 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-building text-blue-500"></i> Kantor
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-bolt text-emerald-500"></i> Stasiun
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center font-mono text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200">
                                    {{ $stasiun->radius_meters ?? 1000 }} m
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $totalStaf = $stasiun->users_count ?? ($stasiun->total_karyawan ?? 0);
                                    $staffList = $stasiun->users ?? collect();
                                    $staffNames = $staffList->pluck('name')->implode(', ');
                                @endphp
                                <div class="relative inline-block group">
                                    <span data-id="{{ $stasiun->id }}" data-name="{{ $stasiun->name }}"
                                        title="{{ $staffNames ? 'Staf: ' . $staffNames : ($totalStaf > 0 ? 'Klik untuk melihat staf' : 'Belum ada staf') }}"
                                        class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200 inline-flex items-center gap-1.5
                                        {{ $totalStaf > 0 ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800 hover:bg-sky-100 dark:hover:bg-sky-900/60 cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 cursor-not-allowed' }}">
                                        @if($totalStaf > 0)
                                            <i class="fa-solid fa-users text-[10px] text-sky-500"></i>
                                        @endif
                                        <span>{{ $totalStaf }} Orang</span>
                                    </span>

                                    @if($totalStaf > 0 && $staffList->count() > 0)
                                    <!-- Popover Hover Mini Modern -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none transition-all duration-200 animate-in fade-in zoom-in-95">
                                        <div class="bg-slate-900/95 dark:bg-slate-800 text-white text-[11px] font-medium py-2 px-3.5 rounded-xl shadow-xl border border-slate-700 whitespace-nowrap min-w-[150px] text-left backdrop-blur-xs">
                                            <p class="font-bold text-sky-400 text-[10px] uppercase tracking-wider mb-1.5 flex items-center gap-1.5 border-b border-slate-700/80 pb-1">
                                                <i class="fa-solid fa-user-check text-[9px]"></i> Staf Penempatan ({{ $totalStaf }})
                                            </p>
                                            <ul class="text-slate-200 space-y-0.5">
                                                @foreach($staffList->take(5) as $staf)
                                                    <li class="flex items-center gap-1.5 truncate max-w-[200px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400 shrink-0"></span>
                                                        <span class="truncate">{{ $staf->name }}</span>
                                                    </li>
                                                @endforeach
                                                @if($staffList->count() > 5)
                                                    <li class="text-slate-400 text-[10px] italic pt-0.5">+{{ $staffList->count() - 5 }} staf lainnya (klik untuk detail)</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="w-2 h-2 bg-slate-900/95 dark:bg-slate-800 rotate-45 -mt-1 border-r border-b border-slate-700"></div>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            @if(Auth::user()->isLevel1())
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-stasiun='@json($stasiun)'
                                            onclick="bukaModalEditStasiun(this)"
                                            class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-xl text-xs transition-colors" 
                                            title="Edit Stasiun & Kalibrasi">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form id="form-delete-stasiun-{{ $stasiun->id }}" action="{{ route('admin.stations.destroy', $stasiun->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="konfirmasiHapus('form-delete-stasiun-{{ $stasiun->id }}', 'Stasiun / Lokasi: {{ $stasiun->name }}')"
                                                class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl text-xs transition-colors" 
                                                title="Hapus Lokasi">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-building-circle-exclamation text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada data Kantor / Stasiun Kerja yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL 2: KHUSUS RUMAH METER --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-amber-50/30 dark:bg-amber-950/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shadow-xs">
                            <i class="fa-solid fa-gauge-high"></i>
                        </span>
                        Daftar Rumah Meter
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800">
                        {{ $stasiunRumahMeter->count() }} Checkpoint
                    </span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Titik validasi absensi GPS khusus lokasi Rumah Meter dan checkpoint lapangan.</p>
            </div>
            @if(Auth::user()->isLevel1())
            <button type="button" onclick="bukaModalTambahStasiun('rumah_meter')" class="bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0">
                <i class="fa-solid fa-plus"></i> Tambah Rumah Meter
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-6 py-4 w-24">Kode</th>
                        <th class="px-6 py-4">Nama Rumah Meter</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">Radius GPS</th>
                        <th class="px-6 py-4 text-center">Total Staf Terikat</th>
                        @if(Auth::user()->isLevel1())
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70 text-slate-700 dark:text-slate-200 text-sm">
                    @forelse($stasiunRumahMeter as $stasiun)
                        <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/20 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">{{ $stasiun->kode_stasiun }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center space-x-2.5 cursor-pointer hover:text-amber-600 dark:hover:text-amber-400 transition-colors btn-view-map group"
                                     data-name="{{ $stasiun->name }}" 
                                     data-lat="{{ $stasiun->latitude }}" 
                                     data-lng="{{ $stasiun->longitude }}"
                                     data-radius="{{ $stasiun->radius_meters ?? 1000 }}">
                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 group-hover:scale-125 transition-transform shrink-0"></div>
                                    <span>{{ $stasiun->name }}</span>
                                    <i class="fa-solid fa-map-location-dot text-xs text-amber-500 ml-1 opacity-70 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-gauge-high text-amber-500"></i> Rumah Meter
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center font-mono text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200">
                                    {{ $stasiun->radius_meters ?? 1000 }} m
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $rm = $stasiun;
                                    $totalStafRm = $rm->assigned_users_count ?? ($rm->assignedUsers ? $rm->assignedUsers->count() : ($rm->users_count ?? 0));
                                    $staffRmList = $rm->assignedUsers ?? collect();
                                    $staffRmNames = $staffRmList->pluck('name')->implode(', ');
                                @endphp
                                <div class="relative inline-block group">
                                    <span data-id="{{ $rm->id }}" data-name="{{ $rm->name }}"
                                        title="{{ $staffRmNames ? 'Staf Terikat: ' . $staffRmNames : ($totalStafRm > 0 ? 'Klik untuk melihat staf' : 'Belum ada staf') }}"
                                        class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200 inline-flex items-center gap-1.5
                                        {{ $totalStafRm > 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 cursor-not-allowed' }}">
                                        @if($totalStafRm > 0)
                                            <i class="fa-solid fa-users text-[10px] text-amber-500"></i>
                                        @endif
                                        <span>{{ $totalStafRm }} Orang</span>
                                    </span>

                                    @if($totalStafRm > 0 && $staffRmList->count() > 0)
                                    <!-- Popover Hover Mini Modern -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none transition-all duration-200 animate-in fade-in zoom-in-95">
                                        <div class="bg-slate-900/95 dark:bg-slate-800 text-white text-[11px] font-medium py-2 px-3.5 rounded-xl shadow-xl border border-slate-700 whitespace-nowrap min-w-[150px] text-left backdrop-blur-xs">
                                            <p class="font-bold text-amber-400 text-[10px] uppercase tracking-wider mb-1.5 flex items-center gap-1.5 border-b border-slate-700/80 pb-1">
                                                <i class="fa-solid fa-shield-halved text-[9px]"></i> Staf Terikat ({{ $totalStafRm }})
                                            </p>
                                            <ul class="text-slate-200 space-y-0.5">
                                                @foreach($staffRmList->take(5) as $staf)
                                                    <li class="flex items-center gap-1.5 truncate max-w-[200px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                                        <span class="truncate">{{ $staf->name }}</span>
                                                    </li>
                                                @endforeach
                                                @if($staffRmList->count() > 5)
                                                    <li class="text-slate-400 text-[10px] italic pt-0.5">+{{ $staffRmList->count() - 5 }} staf lainnya (klik untuk detail)</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="w-2 h-2 bg-slate-900/95 dark:bg-slate-800 rotate-45 -mt-1 border-r border-b border-slate-700"></div>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            @if(Auth::user()->isLevel1())
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-stasiun='@json($stasiun)'
                                            onclick="bukaModalEditStasiun(this)"
                                            class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-xl text-xs transition-colors" 
                                            title="Edit Rumah Meter & Kalibrasi">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form id="form-delete-stasiun-{{ $stasiun->id }}" action="{{ route('admin.stations.destroy', $stasiun->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="konfirmasiHapus('form-delete-stasiun-{{ $stasiun->id }}', 'Rumah Meter: {{ $stasiun->name }}')"
                                                class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl text-xs transition-colors" 
                                                title="Hapus Rumah Meter">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-gauge-simple text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada checkpoint Rumah Meter yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL FORM TAMBAH / EDIT STASIUN DENGAN PETA KALIBRASI INTERAKTIF LEAFLET --}}
<div id="modalFormStasiun" class="fixed inset-0 z-50 items-center justify-center hidden p-3 sm:p-4 overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" onclick="tutupModalFormStasiun()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl p-5 sm:p-7 relative z-10 animate-in fade-in zoom-in-95 duration-200 my-auto max-h-[94vh] flex flex-col border border-slate-100 dark:border-slate-700">
        
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700/80 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-500/20">
                    <i class="fa-solid fa-map-location-dot text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-lg leading-tight" id="judulModalForm">Tambah Lokasi Kerja</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Konfigurasi data stasiun & kalibrasi presisi radius geofencing absensi karyawan</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalFormStasiun()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- FORM ACTIONS & REPEATER CONTAINER --}}
        <form id="formStasiunAction" action="{{ route('admin.stations.store') }}" method="POST" class="space-y-5 overflow-y-auto pr-1 flex-1 py-4 modal-scroll-area">
            @csrf
            <input type="hidden" name="_method" id="methodFormStasiun" value="POST">

            {{-- CONTAINER INPUT REPEATER STASIUN --}}
            <div id="stationRowsContainer" class="space-y-6">
                {{-- Dynamic Rows injected by JavaScript --}}
            </div>

            {{-- TOMBOL TAMBAH BARIS STASIUN (JIKA MODE TAMBAH BANYAK) --}}
            <div id="btnTambahStasiunContainer" class="pt-2">
                <button type="button" onclick="tambahBarisStasiun()" class="w-full py-3 bg-slate-50 hover:bg-sky-50/50 dark:bg-slate-800/60 dark:hover:bg-slate-700/60 text-sky-600 dark:text-sky-400 border-2 border-dashed border-sky-300 dark:border-sky-700/70 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all group">
                    <i class="fa-solid fa-plus text-xs group-hover:scale-125 transition-transform"></i> Tambah Baris Stasiun / Lokasi Lain
                </button>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700/80 shrink-0">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 hidden sm:inline flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-sky-500"></i> Koordinat & geofencing divalidasi otomatis saat absensi karyawan
                </span>
                <div class="flex items-center space-x-2.5 w-full sm:w-auto justify-end">
                    <button type="button" onclick="tutupModalFormStasiun()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-600/25 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Lokasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL POPUP PREVIEW PETA LOKASI STASIUN --}}
<div id="stationMapModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div id="mapModalBackdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl p-6 relative z-10 transform transition-all flex flex-col border border-slate-100 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base" id="mapModalTitle">Peta Lokasi Stasiun</h3>
                <p id="mapModalCoords" class="text-xs text-sky-600 dark:text-sky-400 font-mono mt-0.5">-</p>
            </div>
            <button type="button" id="closeMapModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="my-4 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner bg-slate-100 dark:bg-slate-900">
            <div id="stationMap" class="w-full h-80 z-0"></div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-700 pt-4">
            <a id="btnOpenGoogleMaps" href="#" target="_blank" class="px-4 py-2 bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-sky-100 dark:hover:bg-sky-900/60 border border-sky-200 dark:border-sky-800 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-location-arrow"></i> Buka di Google Maps
            </a>
            <button type="button" id="closeMapModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP VIEW DAFTAR KARYAWAN PER STASIUN --}}
<div id="staffStationModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div id="staffModalBackdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl p-6 relative z-10 transform transition-all max-h-[85vh] flex flex-col border border-slate-100 dark:border-slate-700">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Daftar Anggota Staf</h3>
                <p id="modalStationTitle" class="text-xs text-sky-600 dark:text-sky-400 font-medium mt-0.5"></p>
            </div>
            <button type="button" id="closeStaffModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalStaffLoading" class="py-12 text-center my-auto">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">Menarik data staf...</p>
        </div>

        <div id="modalStaffContent" class="hidden overflow-y-auto my-4 flex-1 pr-1 modal-scroll-area">
            <table class="w-full text-left border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-slate-400 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                        <th class="px-6 pb-1">Nama Lengkap</th>
                        <th class="px-6 pb-1">Jabatan</th>
                    </tr>
                </thead>
                <tbody id="staffListContainer">
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end border-t border-slate-100 dark:border-slate-700 pt-4">
            <button type="button" id="closeStaffModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP DETAIL LENGKAP KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div id="detailModalBackdrop" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg p-6 relative z-10 transform transition-all max-h-[90vh] overflow-y-auto border border-slate-100 dark:border-slate-700 modal-scroll-area">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoadingDetail" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">Memuat data...</p>
        </div>

        <div id="modalDataContentDetail" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white dark:border-slate-700 ring-4 ring-sky-50 dark:ring-sky-950"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800 dark:text-slate-100"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/50 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100 dark:border-sky-800"></p>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-4 grid grid-cols-1 gap-y-4 text-sm">
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">NIP</span>
                    <span id="detail_nip" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold font-mono">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2 items-center">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Email</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_email" class="text-slate-800 dark:text-slate-100 font-semibold truncate">-</span>
                        <a id="detail_email_link" href="#" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-sky-500 hover:bg-sky-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2 items-center">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">No. Telepon</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_phone" class="text-slate-800 dark:text-slate-100 font-semibold">-</span>
                        <a id="detail_phone_link" href="#" target="_blank" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chat WA</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Jobdesk</span>
                    <span id="detail_job" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 dark:border-slate-700 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let stationIndex = 0;
    let defaultTypeGlobal = 'stasiun';

    // Konstanta Default Koordinat Jawa Timur / Umbulan Pasuruan
    const COMPANY_DEFAULT_LAT = -7.7572565;
    const COMPANY_DEFAULT_LNG = 112.9314949;
    const COMPANY_DEFAULT_RADIUS = 1000;

    // Registry penyimpanan instance Leaflet Map form kalibrasi
    const calibrationMaps = {};

    // Standard high-res Leaflet marker icon
    const stationCustomMarkerIcon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // SweetAlert2 Toast & Popup Notification Handler
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('page-container');
        const sessionSuccess = container.getAttribute('data-success');
        const sessionError = container.getAttribute('data-error');
        const validationErrors = JSON.parse(container.getAttribute('data-errors') || '[]');

        if (sessionSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: sessionSuccess,
                confirmButtonColor: '#0284c7',
                timer: 3000,
                timerProgressBar: true
            });
        }

        if (sessionError) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: sessionError,
                confirmButtonColor: '#e11d48'
            });
        }

        if (validationErrors && validationErrors.length > 0) {
            let errorListHtml = validationErrors.map(err => `• ${err}`).join('<br>');

            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                html: `<div class="text-left text-xs font-medium text-slate-600 leading-relaxed">${errorListHtml}</div>`,
                confirmButtonColor: '#f59e0b'
            });
        }
    });

    // POPUP KONFIRMASI HAPUS STASIUN / RUMAH METER
    function konfirmasiHapus(formId, itemLabel) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: `Data "${itemLabel}" yang dihapus tidak dapat dikembalikan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl dark:bg-slate-800 dark:text-slate-100',
                confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
                cancelButton: 'rounded-xl text-xs font-bold px-4 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // =========================================================================
    // 🗺️ MODUL KALIBRASI PETA INTERAKTIF LEAFLET DENGAN GEOLOKASI & 3-WAY SYNC
    // =========================================================================

    /**
     * Memperbarui label indikator koordinat live di peta
     */
    function updateLiveBadge(index, lat, lng, radius) {
        const badge = document.getElementById(`badge_live_coords_${index}`);
        if (!badge) return;

        const latText = !isNaN(lat) && lat !== null && lat !== '' ? parseFloat(lat).toFixed(6) : '-';
        const lngText = !isNaN(lng) && lng !== null && lng !== '' ? parseFloat(lng).toFixed(6) : '-';
        const radText = !isNaN(radius) && radius > 0 ? `${parseInt(radius)}m` : '-';

        badge.textContent = `Lat: ${latText}, Lng: ${lngText} | Radius: ${radText}`;
    }

    /**
     * Menginisialisasi Peta Leaflet Interaktif pada Baris Form Kalibrasi
     */
    function initCalibrationMap(index, initialLat, initialLng, initialRadius) {
        const mapContainerId = `station_map_${index}`;
        const mapElem = document.getElementById(mapContainerId);
        if (!mapElem) return;

        // Bersihkan map lama jika ada
        if (calibrationMaps[index] && calibrationMaps[index].map) {
            try {
                if (calibrationMaps[index].resizeObserver) {
                    calibrationMaps[index].resizeObserver.disconnect();
                }
                calibrationMaps[index].map.remove();
            } catch (e) {
                console.error("Gagal membersihkan peta lama:", e);
            }
            delete calibrationMaps[index];
        }

        let lat = parseFloat(initialLat);
        let lng = parseFloat(initialLng);
        let rad = parseFloat(initialRadius) || COMPANY_DEFAULT_RADIUS;

        // Gunakan default lokasi jika belum ada koordinat
        if (isNaN(lat) || isNaN(lng)) {
            lat = COMPANY_DEFAULT_LAT;
            lng = COMPANY_DEFAULT_LNG;
        }

        // 1. Buat Instance Leaflet Map
        const map = L.map(mapContainerId, {
            center: [lat, lng],
            zoom: 16,
            attributionControl: false
        });

        L.control.attribution({ prefix: false }).addTo(map);

        // 2. Tambahkan Tile Layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
        }).addTo(map);

        // 3. Tambahkan Draggable Marker
        const marker = L.marker([lat, lng], {
            icon: stationCustomMarkerIcon,
            draggable: true,
            autoPan: true
        }).addTo(map);

        marker.bindTooltip("<b>Titik Stasiun</b><br><span class='text-[10px]'>Geser pin untuk memindahkan</span>", {
            direction: 'top',
            offset: [0, -36]
        });

        // 4. Tambahkan Geofencing Circle
        const circle = L.circle([lat, lng], {
            radius: rad,
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.22,
            weight: 2,
            dashArray: '5, 5'
        }).addTo(map);

        updateLiveBadge(index, lat, lng, rad);

        // 5. EVENT LISTENER: Saat Marker Digeser & Dilepas (dragend)
        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            const latVal = pos.lat.toFixed(7);
            const lngVal = pos.lng.toFixed(7);

            document.getElementById(`input_latitude_${index}`).value = latVal;
            document.getElementById(`input_longitude_${index}`).value = lngVal;
            document.getElementById(`input_maps_url_${index}`).value = `https://www.google.com/maps?q=${latVal},${lngVal}`;

            circle.setLatLng(pos);
            updateLiveBadge(index, pos.lat, pos.lng, circle.getRadius());
        });

        // Event saat sedang digeser (live drag feedback)
        marker.on('drag', function (e) {
            const pos = e.target.getLatLng();
            circle.setLatLng(pos);
            updateLiveBadge(index, pos.lat, pos.lng, circle.getRadius());
        });

        // 6. EVENT LISTENER: Saat Peta Diklik (map click)
        map.on('click', function (e) {
            const latVal = e.latlng.lat.toFixed(7);
            const lngVal = e.latlng.lng.toFixed(7);

            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);

            document.getElementById(`input_latitude_${index}`).value = latVal;
            document.getElementById(`input_longitude_${index}`).value = lngVal;
            document.getElementById(`input_maps_url_${index}`).value = `https://www.google.com/maps?q=${latVal},${lngVal}`;

            updateLiveBadge(index, e.latlng.lat, e.latlng.lng, circle.getRadius());
        });

        // 7. ResizeObserver untuk mencegah grey/blank tiles saat modal animasi
        const resizeObserver = new ResizeObserver(() => {
            if (map) {
                map.invalidateSize();
            }
        });
        resizeObserver.observe(mapElem);

        // Simpan ke registry kalibrasi
        calibrationMaps[index] = {
            map: map,
            marker: marker,
            circle: circle,
            resizeObserver: resizeObserver
        };

        // Invalidate size dengan timeout
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);
    }

    /**
     * Menghapus instans peta dari registry
     */
    function destroyCalibrationMap(index) {
        if (calibrationMaps[index]) {
            try {
                if (calibrationMaps[index].resizeObserver) {
                    calibrationMaps[index].resizeObserver.disconnect();
                }
                if (calibrationMaps[index].map) {
                    calibrationMaps[index].map.remove();
                }
            } catch (e) {
                console.error(e);
            }
            delete calibrationMaps[index];
        }
    }

    /**
     * Memusatkan ulang kamera peta ke posisi marker
     */
    function pusatkanKeMarker(index) {
        const item = calibrationMaps[index];
        if (!item || !item.marker || !item.map) return;

        const pos = item.marker.getLatLng();
        item.map.flyTo(pos, 17, { duration: 1 });
    }

    /**
     * Sinkronisasi saat admin mengetik manual Latitude / Longitude
     */
    function sinkronkanKoordinatManual(index) {
        const latInput = document.getElementById(`input_latitude_${index}`);
        const lngInput = document.getElementById(`input_longitude_${index}`);
        if (!latInput || !lngInput) return;

        const lat = parseFloat(latInput.value.replace(',', '.'));
        const lng = parseFloat(lngInput.value.replace(',', '.'));

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            const item = calibrationMaps[index];
            if (item && item.marker && item.map) {
                const newPos = [lat, lng];
                item.marker.setLatLng(newPos);
                item.circle.setLatLng(newPos);
                item.map.panTo(newPos);
                updateLiveBadge(index, lat, lng, item.circle.getRadius());
                document.getElementById(`input_maps_url_${index}`).value = `https://www.google.com/maps?q=${lat},${lng}`;
            }
        }
    }

    /**
     * Sinkronisasi saat angka Radius (Meter) diubah
     */
    function sinkronkanRadiusManual(index) {
        const radInput = document.getElementById(`input_radius_${index}`);
        if (!radInput) return;

        const rad = parseFloat(radInput.value);
        if (!isNaN(rad) && rad >= 5) {
            const item = calibrationMaps[index];
            if (item && item.circle) {
                item.circle.setRadius(rad);
                const pos = item.marker.getLatLng();
                updateLiveBadge(index, pos.lat, pos.lng, rad);
            }
        }
    }

    /**
     * Helper Parser URL Google Maps & Format Koordinat Mentah
     */
    function extractCoordinatesFromUrl(url) {
        if (!url || typeof url !== 'string') return null;
        url = url.trim();

        // 1. Pola Place Data !3d{lat}!4d{lng}
        let matchPlace = url.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
        if (matchPlace && matchPlace[1] && matchPlace[2]) {
            return { lat: parseFloat(matchPlace[1]), lng: parseFloat(matchPlace[2]) };
        }

        // 2. Pola Query ?q={lat},{lng} atau &ll={lat},{lng}
        let matchQuery = url.match(/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (matchQuery && matchQuery[1] && matchQuery[2]) {
            return { lat: parseFloat(matchQuery[1]), lng: parseFloat(matchQuery[2]) };
        }

        // 3. Pola Center param ?center={lat},{lng}
        let matchCenter = url.match(/[?&]center=(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (matchCenter && matchCenter[1] && matchCenter[2]) {
            return { lat: parseFloat(matchCenter[1]), lng: parseFloat(matchCenter[2]) };
        }

        // 4. Pola Direct Map @{lat},{lng}
        let matchAt = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (matchAt && matchAt[1] && matchAt[2]) {
            return { lat: parseFloat(matchAt[1]), lng: parseFloat(matchAt[2]) };
        }

        // 5. Pola Raw Teks: "-7.123456, 112.654321"
        let matchRaw = url.match(/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/);
        if (matchRaw && matchRaw[1] && matchRaw[2]) {
            return { lat: parseFloat(matchRaw[1]), lng: parseFloat(matchRaw[2]) };
        }

        return null;
    }

    /**
     * Membaca dan mensinkronkan koordinat dari URL Google Maps yang diinput
     */
    function sinkronkanDariMapsUrl(index, showSuccessToast = false) {
        const input = document.getElementById(`input_maps_url_${index}`);
        if (!input) return;

        const coords = extractCoordinatesFromUrl(input.value);
        if (coords) {
            const latVal = coords.lat.toFixed(7);
            const lngVal = coords.lng.toFixed(7);

            document.getElementById(`input_latitude_${index}`).value = latVal;
            document.getElementById(`input_longitude_${index}`).value = lngVal;

            const item = calibrationMaps[index];
            if (item && item.map && item.marker) {
                const newPos = [coords.lat, coords.lng];
                item.marker.setLatLng(newPos);
                item.circle.setLatLng(newPos);
                item.map.flyTo(newPos, 17, { duration: 1.2 });
                updateLiveBadge(index, coords.lat, coords.lng, item.circle.getRadius());
            }

            if (showSuccessToast) {
                Swal.fire({
                    icon: 'success',
                    title: 'Peta Berhasil Disinkronkan!',
                    text: `Koordinat ditemukan: (${latVal}, ${lngVal})`,
                    timer: 2200,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
            return true;
        }
        return false;
    }

    /**
     * Tombol "Tempel Link" dari Clipboard & Langsung Sinkronkan
     */
    async function tempelDanSinkronkan(index) {
        const input = document.getElementById(`input_maps_url_${index}`);
        let textFound = false;

        try {
            if (navigator.clipboard && navigator.clipboard.readText) {
                const clipText = await navigator.clipboard.readText();
                if (clipText && clipText.trim()) {
                    input.value = clipText.trim();
                    textFound = true;
                }
            }
        } catch (e) {
            console.log("Akses clipboard ditolak atau tidak didukung:", e);
        }

        const success = sinkronkanDariMapsUrl(index, true);

        if (!success && !textFound && !input.value.trim()) {
            Swal.fire({
                icon: 'info',
                title: 'Tempel Link Google Maps',
                text: 'Silakan tempel (Ctrl+V) link Google Maps ke dalam kolom teks terlebih dahulu.',
                confirmColor: '#0284c7'
            });
        } else if (!success && input.value.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Format Tidak Dikenali',
                text: 'Pastikan link Google Maps memuat koordinat (contoh: link berisi @-7.xxx,112.xxx atau ?q=-7.xxx,112.xxx).',
                confirmColor: '#f59e0b'
            });
        }
    }

    /**
     * Tombol "Gunakan GPS Saya" untuk mengambil koordinat perangkat saat ini
     */
    function ambilGpsSaya(index) {
        const btn = document.getElementById(`btn_gps_${index}`);
        const originalHtml = btn ? btn.innerHTML : '';

        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'Tidak Didukung',
                text: 'Browser perangkat Anda tidak mendukung Geolocation GPS.',
                confirmColor: '#e11d48'
            });
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Mendeteksi GPS...`;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const latVal = lat.toFixed(7);
                const lngVal = lng.toFixed(7);

                document.getElementById(`input_latitude_${index}`).value = latVal;
                document.getElementById(`input_longitude_${index}`).value = lngVal;
                document.getElementById(`input_maps_url_${index}`).value = `https://www.google.com/maps?q=${latVal},${lngVal}`;

                const item = calibrationMaps[index];
                if (item && item.map && item.marker) {
                    const newPos = [lat, lng];
                    item.marker.setLatLng(newPos);
                    item.circle.setLatLng(newPos);
                    item.map.flyTo(newPos, 17, { duration: 1.2 });
                    updateLiveBadge(index, lat, lng, item.circle.getRadius());
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Lokasi GPS Berhasil Diambil!',
                    text: `Koordinat GPS (${latVal}, ${lngVal}) berhasil diterapkan ke peta dan form.`,
                    timer: 2500,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            },
            function (error) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }

                let msg = 'Gagal mengambil lokasi GPS saat ini.';
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'Izin akses lokasi ditolak oleh browser. Harap izinkan akses lokasi di pengaturan browser Anda.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    msg = 'Informasi lokasi perangkat tidak tersedia atau sinyal GPS lemah.';
                } else if (error.code === error.TIMEOUT) {
                    msg = 'Waktu permintaan pengambilan lokasi GPS habis (timeout).';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Akses GPS',
                    text: msg,
                    confirmColor: '#e11d48'
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 12000,
                maximumAge: 0
            }
        );
    }

    // =========================================================================
    // 📋 MODUL FORM REPEATER STASIUN & MODAL HANDLER
    // =========================================================================

    function tambahBarisStasiun(stasiunData = null) {
        const container = document.getElementById('stationRowsContainer');
        const showDelete = container.children.length > 0;
        const curIdx = stationIndex;

        const typeVal = stasiunData ? (stasiunData.type || defaultTypeGlobal) : defaultTypeGlobal;
        const kodeVal = stasiunData ? (stasiunData.kode_stasiun || '') : '';
        const nameVal = stasiunData ? (stasiunData.name || '') : '';
        const latVal = stasiunData ? (stasiunData.latitude || '') : '';
        const lngVal = stasiunData ? (stasiunData.longitude || '') : '';
        const radVal = stasiunData ? (stasiunData.radius_meters || COMPANY_DEFAULT_RADIUS) : COMPANY_DEFAULT_RADIUS;
        const mapsUrlVal = (latVal && lngVal) ? `https://www.google.com/maps?q=${latVal},${lngVal}` : '';

        // Label dan placeholder dinamis berdasarkan tipe stasiun
        let typeFieldHtml = '';
        let placeholderKode = '';
        let placeholderNama = '';
        let labelNama = '';

        if (defaultTypeGlobal === 'rumah_meter') {
            typeFieldHtml = `<input type="hidden" name="stations[${curIdx}][type]" value="rumah_meter">`;
            placeholderKode = 'Contoh: RM-GIRI';
            placeholderNama = 'Contoh: Rumah Meter Giri';
            labelNama = 'Nama Checkpoint Rumah Meter';
        } else {
            typeFieldHtml = `
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-sky-500"></i> Tipe Tempat Kerja
                    </label>
                    <select name="stations[${curIdx}][type]" required class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none cursor-pointer transition-all">
                        <option value="stasiun" ${typeVal === 'stasiun' ? 'selected' : ''}>Stasiun Operasional</option>
                        <option value="kantor" ${typeVal === 'kantor' ? 'selected' : ''}>Kantor / Head Office</option>
                    </select>
                </div>
            `;
            placeholderKode = 'Contoh: HO-SBY atau ST-UMB';
            placeholderNama = 'Contoh: Kantor Surabaya atau Stasiun Umbulan';
            labelNama = 'Nama Tempat Kerja / Stasiun';
        }

        const rowHtml = `
            <div class="station-item-row bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/80 rounded-3xl p-5 sm:p-6 relative space-y-5 shadow-xs transition-all" id="station_row_${curIdx}">
                
                {{-- HEADER BARIS: INDIKATOR DAN TOMBOL HAPUS BARIS --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/80 font-mono">
                            Lokasi #${curIdx + 1}
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline">• Konfigurasi data & kalibrasi</span>
                    </div>

                    ${showDelete ? `
                        <button type="button" onclick="hapusBarisStasiun(this, ${curIdx})" class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Hapus Baris Ini">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    ` : ''}
                </div>

                {{-- INFO UTAMA STASIUN --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    ${typeFieldHtml}
                    <div class="${defaultTypeGlobal === 'rumah_meter' ? 'md:col-span-1' : ''}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-barcode text-sky-500"></i> Kode Unik
                        </label>
                        <input type="text" 
                               name="stations[${curIdx}][kode_stasiun]" 
                               value="${kodeVal}" 
                               required 
                               placeholder="${placeholderKode}" 
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs uppercase font-mono font-bold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                    </div>
                    <div class="${defaultTypeGlobal === 'rumah_meter' ? 'md:col-span-2' : ''}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-building-flag text-sky-500"></i> ${labelNama}
                        </label>
                        <input type="text" 
                               name="stations[${curIdx}][name]" 
                               value="${nameVal}" 
                               required 
                               placeholder="${placeholderNama}" 
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                    </div>
                </div>

                {{-- KARTU KALIBRASI PETA INTERAKTIF LEAFLET --}}
                <div class="bg-white dark:bg-slate-800/90 border border-slate-200/90 dark:border-slate-700 rounded-2xl p-4 sm:p-5 space-y-4 shadow-sm">
                    
                    {{-- 1. BAGIAN ATAS: BAR AKSI CEPAT --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-crosshairs"></i>
                                </span>
                                Kalibrasi Presisi Koordinat & Geofencing
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Gunakan URL Google Maps, GPS perangkat, atau geser pin langsung pada peta interaktif.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                            {{-- Input Google Maps + Tombol Tempel --}}
                            <div class="relative flex items-center">
                                <input type="url" 
                                       id="input_maps_url_${curIdx}"
                                       value="${mapsUrlVal}" 
                                       oninput="sinkronkanDariMapsUrl(${curIdx})" 
                                       placeholder="Tempel link Google Maps..." 
                                       class="w-full sm:w-72 pl-8 pr-20 py-2 bg-slate-50 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                                <i class="fa-solid fa-link text-slate-400 absolute left-3 text-xs pointer-events-none"></i>
                                <button type="button" 
                                        onclick="tempelDanSinkronkan(${curIdx})" 
                                        title="Tempel link dari clipboard atau proses link input"
                                        class="absolute right-1 px-2.5 py-1 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/50 dark:hover:bg-sky-900/60 text-sky-600 dark:text-sky-400 text-[10px] font-bold rounded-lg border border-sky-200 dark:border-sky-800 transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-paste text-[10px]"></i> Tempel
                                </button>
                            </div>

                            {{-- Tombol Gunakan GPS Saya --}}
                            <button type="button" 
                                    id="btn_gps_${curIdx}"
                                    onclick="ambilGpsSaya(${curIdx})" 
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 shrink-0">
                                <i class="fa-solid fa-location-crosshairs text-xs"></i> Gunakan GPS Saya
                            </button>
                        </div>
                    </div>

                    {{-- 2. BAGIAN TENGAH: PETA INTERAKTIF LEAFLET --}}
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner bg-slate-100 dark:bg-slate-900">
                        
                        {{-- Leaflet Map Container --}}
                        <div id="station_map_${curIdx}" class="w-full h-72 sm:h-80 z-0"></div>

                        {{-- Petunjuk Bantuan Melayang (Atas Kiri) --}}
                        <div class="absolute top-3 left-3 z-[400] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-[11px] text-slate-700 dark:text-slate-200 font-medium shadow-md flex items-center gap-2 pointer-events-none select-none max-w-[85%] sm:max-w-none">
                            <i class="fa-solid fa-hand-pointer text-sky-500 animate-pulse text-xs shrink-0"></i>
                            <span class="truncate sm:overflow-visible">Geser pin (marker) atau klik pada peta untuk menentukan titik koordinat presisi stasiun.</span>
                        </div>

                        {{-- Indikator Koordinat Real-time (Bawah Kiri) --}}
                        <div class="absolute bottom-3 left-3 z-[400] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-[10px] sm:text-[11px] font-mono text-sky-600 dark:text-sky-400 font-bold shadow-md flex items-center gap-2 select-none">
                            <i class="fa-solid fa-satellite text-sky-500 text-xs"></i>
                            <span id="badge_live_coords_${curIdx}">Memuat koordinat...</span>
                        </div>

                        {{-- Tombol Pusatkan Ulang ke Pin (Bawah Kanan) --}}
                        <button type="button" 
                                onclick="pusatkanKeMarker(${curIdx})" 
                                title="Pusatkan kamera peta ke pin koordinat"
                                class="absolute bottom-3 right-3 z-[400] bg-white/95 dark:bg-slate-900/95 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold shadow-md transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-to-dot text-sky-500"></i>
                            <span class="hidden sm:inline text-[11px]">Pusatkan Pin</span>
                        </button>
                    </div>

                    {{-- 3. BAGIAN BAWAH: GRID 3 KOLOM KOORDINAT & RADIUS (TWO-WAY BINDING) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-compass text-sky-500"></i> Latitude (Lintang)
                            </label>
                            <input type="text" 
                                   name="stations[${curIdx}][latitude]" 
                                   id="input_latitude_${curIdx}" 
                                   value="${latVal}" 
                                   required 
                                   oninput="sinkronkanKoordinatManual(${curIdx})" 
                                   placeholder="-7.7572565" 
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 font-mono font-bold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Terisi otomatis saat pin digeser / peta diklik.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-compass text-sky-500"></i> Longitude (Bujur)
                            </label>
                            <input type="text" 
                                   name="stations[${curIdx}][longitude]" 
                                   id="input_longitude_${curIdx}" 
                                   value="${lngVal}" 
                                   required 
                                   oninput="sinkronkanKoordinatManual(${curIdx})" 
                                   placeholder="112.9314949" 
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 font-mono font-bold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Dapat diketik manual untuk penyesuaian mikro.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-dot text-sky-500"></i> Radius Geofencing (Meter)
                            </label>
                            <input type="number" 
                                   name="stations[${curIdx}][radius_meters]" 
                                   id="input_radius_${curIdx}" 
                                   value="${radVal}" 
                                   required 
                                   min="10" 
                                   max="10000"
                                   oninput="sinkronkanRadiusManual(${curIdx})" 
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 font-mono font-bold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Lingkaran biru di peta berubah mengikuti nilai ini.</p>
                        </div>
                    </div>
                </div>

            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        // Inisialisasi peta Leaflet untuk baris baru
        setTimeout(() => {
            initCalibrationMap(curIdx, latVal, lngVal, radVal);
        }, 50);

        stationIndex++;
    }

    function hapusBarisStasiun(btn, index) {
        destroyCalibrationMap(index);
        const parentRow = btn.closest('.station-item-row');
        if (parentRow) parentRow.remove();
    }

    function bukaModalTambahStasiun(defaultType = 'stasiun') {
        defaultTypeGlobal = defaultType;
        document.getElementById('judulModalForm').innerText = defaultType === 'rumah_meter' ? 'Tambah Rumah Meter Baru' : 'Tambah Lokasi Kerja Utama';
        document.getElementById('formStasiunAction').action = "{{ route('admin.stations.store') }}";
        document.getElementById('methodFormStasiun').value = 'POST';

        // Bersihkan peta dan baris sebelumnya
        Object.keys(calibrationMaps).forEach(k => destroyCalibrationMap(k));
        document.getElementById('stationRowsContainer').innerHTML = '';
        document.getElementById('btnTambahStasiunContainer').classList.remove('hidden');
        stationIndex = 0;

        tambahBarisStasiun();

        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        // Pastikan ukuran peta Leaflet dihitung ulang setelah modal tampil sempurna
        setTimeout(() => {
            Object.values(calibrationMaps).forEach(item => {
                if (item && item.map) item.map.invalidateSize();
            });
        }, 250);

        setTimeout(() => {
            Object.values(calibrationMaps).forEach(item => {
                if (item && item.map) item.map.invalidateSize();
            });
        }, 500);
    }

    function bukaModalEditStasiun(button) {
        const stasiun = JSON.parse(button.getAttribute('data-stasiun'));

        defaultTypeGlobal = stasiun.type || 'stasiun';
        document.getElementById('judulModalForm').innerText = stasiun.type === 'rumah_meter' ? 'Edit Checkpoint Rumah Meter' : 'Edit Lokasi Kerja';
        document.getElementById('formStasiunAction').action = `/admin/stations/${stasiun.id}`;
        document.getElementById('methodFormStasiun').value = 'PUT';

        // Bersihkan peta dan baris sebelumnya
        Object.keys(calibrationMaps).forEach(k => destroyCalibrationMap(k));
        document.getElementById('stationRowsContainer').innerHTML = '';
        document.getElementById('btnTambahStasiunContainer').classList.add('hidden');
        stationIndex = 0;

        tambahBarisStasiun(stasiun);

        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        // Invalidate size untuk menghilangkan tile abu-abu
        setTimeout(() => {
            Object.values(calibrationMaps).forEach(item => {
                if (item && item.map) item.map.invalidateSize();
            });
        }, 250);

        setTimeout(() => {
            Object.values(calibrationMaps).forEach(item => {
                if (item && item.map) item.map.invalidateSize();
            });
        }, 500);
    }

    function tutupModalFormStasiun() {
        const modal = document.getElementById('modalFormStasiun');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        // Bersihkan registry maps
        Object.keys(calibrationMaps).forEach(k => destroyCalibrationMap(k));
    }

    // =========================================================================
    // 🔍 MODAL PREVIEW PETA, VIEW STAFF, & DETAIL KARYAWAN
    // =========================================================================

    let previewMapInstance = null;
    let previewMarkerInstance = null;
    let previewCircleInstance = null;

    document.addEventListener("DOMContentLoaded", function () {
        const mapModal = document.getElementById("stationMapModal");
        const backdropMap = document.getElementById("mapModalBackdrop");
        const closeMapBtn = document.getElementById("closeMapModalBtn");
        const closeMapBtn2 = document.getElementById("closeMapModalBtn2");
        const mapContainer = document.getElementById("stationMap");

        const mapResizeObserver = new ResizeObserver(() => {
            if (previewMapInstance) {
                previewMapInstance.invalidateSize();
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

        // KLIK TOMBOL VIEW PETA DI TABEL STASIUN
        document.querySelectorAll(".btn-view-map").forEach(item => {
            item.addEventListener("click", function () {
                const name = this.getAttribute("data-name");
                const lat = parseFloat(this.getAttribute("data-lat"));
                const lng = parseFloat(this.getAttribute("data-lng"));
                const radius = parseFloat(this.getAttribute("data-radius")) || COMPANY_DEFAULT_RADIUS;

                if (isNaN(lat) || isNaN(lng)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Koordinat Belum Dikalibrasi',
                        text: 'Koordinat lokasi untuk stasiun ini belum diatur di database. Silakan klik tombol Edit untuk melakukan kalibrasi.',
                        confirmColor: '#0284c7'
                    });
                    return;
                }

                openMapModal();

                document.getElementById("mapModalTitle").textContent = `Lokasi Presensi: ${name}`;
                document.getElementById("mapModalCoords").textContent = `Koordinat: ${lat}, ${lng} (Radius Geofencing: ${radius}m)`;
                document.getElementById("btnOpenGoogleMaps").href = `https://www.google.com/maps?q=${lat},${lng}`;

                if (previewMapInstance !== null) {
                    previewMapInstance.remove();
                    previewMapInstance = null;
                }

                previewMapInstance = L.map('stationMap', {
                    center: [lat, lng],
                    zoom: 16,
                    attributionControl: false
                });

                L.control.attribution({ prefix: false }).addTo(previewMapInstance);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
                }).addTo(previewMapInstance);

                previewMarkerInstance = L.marker([lat, lng], { icon: stationCustomMarkerIcon }).addTo(previewMapInstance)
                    .bindPopup(`<b>${name}</b><br><span class="text-xs text-slate-500">Titik Validasi Absensi GPS (Radius: ${radius}m)</span>`)
                    .openPopup();

                // Lingkaran Geofencing Radius pada Pratinjau
                previewCircleInstance = L.circle([lat, lng], {
                    radius: radius,
                    color: '#0284c7',
                    fillColor: '#38bdf8',
                    fillOpacity: 0.2,
                    weight: 2
                }).addTo(previewMapInstance);

                setTimeout(() => {
                    if (previewMapInstance) {
                        previewMapInstance.invalidateSize();
                    }
                }, 350);
            });
        });

        // MODAL STAF PER STASIUN
        const modalStaff = document.getElementById("staffStationModal");
        const backdropStaff = document.getElementById("staffModalBackdrop");
        const closeStaffBtn = document.getElementById("closeStaffModalBtn");
        const closeStaffBtn2 = document.getElementById("closeStaffModalBtn2");

        const loadingSectionStaff = document.getElementById("modalStaffLoading");
        const contentSectionStaff = document.getElementById("modalStaffContent");
        const stationTitle = document.getElementById("modalStationTitle");
        const staffContainer = document.getElementById("staffListContainer");

        // MODAL DETAIL KARYAWAN
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

        // KLIK TOMBOL VIEW STAF
        document.querySelectorAll(".btn-view-staff").forEach(badge => {
            badge.addEventListener("click", function () {
                const stationId = this.getAttribute("data-id");
                const stationName = this.getAttribute("data-name");

                if (!stationId || this.classList.contains('cursor-not-allowed')) return;

                openStaffModal();
                stationTitle.textContent = `Lokasi Kerja: ${stationName}`;
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
                                    <td colspan="2" class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                        Tidak ada karyawan yang aktif di lokasi ini.
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
                            let roleBadgeClass = 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-600';

                            if (roleName.toLowerCase() === 'manager') {
                                roleBadgeClass = 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800';
                            } else if (roleName.toLowerCase() === 'supervisor') {
                                roleBadgeClass = 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800';
                            } else if (roleName.toLowerCase() === 'staff') {
                                roleBadgeClass = 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800';
                            }

                            const tableRow = document.createElement("tr");
                            tableRow.className = "bg-white dark:bg-slate-800/80 hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors group shadow-sm border border-slate-100 dark:border-slate-700 rounded-2xl";
                            tableRow.innerHTML = `
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100 rounded-l-2xl border-y border-l border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="${karyawan.id}">
                                        <div class="w-9 h-9 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden shrink-0">
                                            ${photoHtml}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-slate-100 font-semibold text-sm group-hover:text-sky-600 dark:group-hover:text-sky-400 group-hover:underline transition-colors">${karyawan.name}</span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">NIP: ${karyawan.nip || '-'}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle rounded-r-2xl border-y border-r border-slate-100 dark:border-slate-700">
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
                                <td colspan="2" class="text-center py-8 text-rose-500 text-xs font-semibold bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                    ⚠️ Terjadi masalah sistem: ${error.message}
                                </td>
                            </tr>`;
                        contentSectionStaff.classList.remove("hidden");
                    });
            });
        });

        // KLIK DETAIL KARYAWAN
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: `Masalah saat memuat data karyawan: ${error.message}`,
                            confirmColor: '#e11d48'
                        });
                        closeDetailModal();
                    });
            }
        });
    });
</script>
@endpush
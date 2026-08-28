@extends('layouts.app')
@section('title', 'Daftar Karyawan & Skema Organisasi')

@push('styles')
<!-- SweetAlert2 CDN CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .mermaid-container {
        background: #f8fafc;
        border-radius: 1rem;
        padding: 1rem;
        width: 100%;
        overflow: hidden;
        position: relative;
        height: 550px;
        cursor: grab;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .mermaid-container:active {
        cursor: grabbing;
    }
    #karyawanOrgDiagram svg {
        max-width: 100% !important;
        height: auto !important;
        max-height: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4 space-y-6">

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2 text-rose-500"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- NAVIGASI TAB UTAMA (DEFAULT ACTIVATED: POHON ORGANISASI) --}}
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl shadow-xs transition-colors">
        <div class="flex space-x-2">
            <button type="button" onclick="switchTab('tab-karyawan-pohon')" id="btn-tab-karyawan-pohon" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-sky-600 text-white shadow-xs cursor-pointer">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Struktur Organisasi Karyawan
            </button>
            <button type="button" onclick="switchTab('tab-karyawan-tabel')" id="btn-tab-karyawan-tabel" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <i class="fa-solid fa-users mr-1.5"></i> Daftar Manajemen Karyawan
            </button>
        </div>
    </div>

    {{-- TAB 1: POHON ORGANISASI KARYAWAN BERDASARKAN ROLE (DEFAULT OPEN / TAMPIL DULUPAN) --}}
    <div id="tab-karyawan-pohon" class="tab-content space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 space-y-6 transition-colors">

            {{-- HEADER & TOMBOL KONTROL ZOOM --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 dark:border-slate-700 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-sitemap text-indigo-600 dark:text-indigo-400"></i> Struktur Organisasi Karyawan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Skema hirarki role jabatan beserta daftar karyawan terdaftar yang mengisi posisi tersebut.</p>
                </div>

                {{-- TOMBOL ZOOM CONTROL --}}
                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-900 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <button type="button" id="btnZoomIn" title="Zoom In (+)" class="w-8 h-8 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                    </button>
                    <button type="button" id="btnZoomOut" title="Zoom Out (-)" class="w-8 h-8 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass-minus text-sm"></i>
                    </button>
                    <button type="button" id="btnZoomReset" title="Reset Ukuran" class="w-8 h-8 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-rotate text-sm"></i>
                    </button>
                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>
                    <button type="button" onclick="renderKaryawanOrgChart()" class="px-3 h-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i> Reload
                    </button>
                </div>
            </div>

            {{-- WADAH RENDER DIAGRAM MERMAID DENGAN ZOOM --}}
            <div id="mermaidParent" class="mermaid-container flex justify-center items-center bg-slate-50 dark:bg-slate-900 rounded-2xl">
                <div id="karyawanOrgDiagram" class="w-full flex justify-center transition-transform origin-center"></div>
            </div>
        </div>
    </div>

    {{-- TAB 2: TABEL DAFTAR KARYAWAN (SEMBUNYI DAHULU / DIBERI CLASS HIDDEN) --}}
    <div id="tab-karyawan-tabel" class="tab-content hidden">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-sky-50/30 dark:bg-slate-800/60 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Daftar Manajemen Karyawan</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800">
                            {{ isset($daftarKaryawan) ? count($daftarKaryawan) : 0 }} Karyawan
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola data seluruh staf, hak akses role, penempatan stasiun kerja, dan informasi akun.</p>
                </div>
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="searchKaryawanInput" placeholder="Cari nama karyawan..."
                        class="w-full pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all text-slate-700 dark:text-slate-100 placeholder-slate-400">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="karyawanTable">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                            <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 dark:hover:bg-slate-800/70 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" data-sort="0">
                                Nama Lengkap <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 dark:hover:bg-slate-800/70 hover:text-slate-600 dark:hover:text-slate-200 transition-colors text-center" data-sort="1">
                                Jabatan <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 dark:hover:bg-slate-800/70 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" data-sort="2">
                                Stasiun <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 text-center">Sisa Cuti Utama</th>
                            <th class="px-6 py-4 text-center w-28">Edit Saldo Cuti</th>
                            <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 dark:hover:bg-slate-800/70 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" data-sort="5">
                                Status <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300" id="karyawanTableBody">
                        @forelse($daftarKaryawan as $karyawan)
                            @php
                                $cutiUtama = $karyawan->saldoCuti ? $karyawan->saldoCuti->filter(function($s) {
                                    $namaCuti = strtolower(optional($s->jenisCuti)->name_cuti ?? '');
                                    return $namaCuti === 'cuti' || $namaCuti === 'cuti tahunan' || optional($s->jenisCuti)->kuota_default == 12;
                                })->first() : null;
                                $sisaCutiVal = $cutiUtama ? $cutiUtama->sisa_saldo : ($karyawan->sisaCutiUtama ?? 12);
                                $saldoIdVal = $cutiUtama ? $cutiUtama->id : 0;
                                $namaCutiText = optional(optional($cutiUtama)->jenisCuti)->name_cuti ?? 'Cuti Utama';
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors table-row-item">
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100" data-search-value="{{ strtolower($karyawan->name) }}">
                                    <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id }}">
                                        <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden border border-slate-100 dark:border-slate-700 shrink-0">
                                            @if($karyawan->profile_photo)
                                                <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($karyawan->name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-slate-100 font-semibold text-sm group-hover:text-sky-600 dark:group-hover:text-sky-400 group-hover:underline transition-colors target-search-name">{{ $karyawan->name }}</span>
                                            <span class="text-xs text-slate-400">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $roleNames = $karyawan->roles->pluck('role_name')->implode(' / ');
                                        $roleIdsJson = json_encode($karyawan->roles->pluck('id')->toArray());
                                        $assignedRmIdsJson = json_encode($karyawan->assignedStations->pluck('id')->toArray());
                                    @endphp
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-600 role-label-{{ $karyawan->id }}">
                                            {{ $roleNames ?: 'Tidak Ada Role' }}
                                        </span>
                                        <button type="button"
                                            onclick='bukaModalKelolaRole({{ $karyawan->id }}, "{{ addslashes($karyawan->name) }}", {{ $roleIdsJson }}, {{ $assignedRmIdsJson }})'
                                            class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-user-tag text-[10px]"></i> Kelola Role
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        @if(($karyawan->station && !empty($karyawan->station->name)))
                                            <span class="inline-flex items-center text-xs text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/50 px-2.5 py-1 rounded-xl border border-slate-200/60 dark:border-slate-600">
                                                <i class="fa-solid fa-location-dot mr-1.5 text-rose-500 text-xs"></i>
                                                {{ $karyawan->station->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-rose-500 font-medium bg-rose-50 dark:bg-rose-950/40 px-2 py-1 rounded-xl italic border border-rose-100 dark:border-rose-800">
                                                ⚠️ Stasiun Belum Diatur
                                            </span>
                                        @endif

                                        @if(($karyawan->hasRole('AREA (PIPELINE)') || $karyawan->hasRole(14)) && $karyawan->assignedStations->count() > 0)
                                            <span class="inline-flex items-center text-[10px] text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800 font-semibold cursor-default" title="{{ $karyawan->assignedStations->pluck('name')->implode(', ') }}">
                                                <i class="fa-solid fa-gauge-high mr-1 text-[9px] text-amber-500"></i>
                                                {{ $karyawan->assignedStations->count() }} Rumah Meter
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">{{ $sisaCutiVal }} Hari</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button type="button"
                                        data-id="{{ $saldoIdVal }}"
                                        data-nama="{{ $namaCutiText }}"
                                        data-saldo="{{ $sisaCutiVal }}"
                                        onclick="bukaModalEditSaldoBtn(this)"
                                        class="px-2.5 py-1.5 bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800 rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1 shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($karyawan->cuti_aktif && $karyawan->cuti_aktif->count() > 0)
                                        @php $cuti = $karyawan->cuti_aktif->first(); @endphp
                                        <span class="inline-flex items-center text-xs text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800 px-2.5 py-1 rounded-full font-bold" title="{{ $cuti->alasan_cuti }}">
                                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5 animate-pulse"></span>
                                            On Leave (Cuti)
                                        </span>
                                    @else
                                        @php
                                            $statusDetail = $karyawan->status_detail ?? [
                                                'badge_class' => 'bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                                                'dot_class' => 'bg-slate-400',
                                                'is_on' => false,
                                                'label' => 'Standby'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center text-xs {{ $statusDetail['badge_class'] }} border px-2.5 py-1 rounded-full font-bold">
                                            <span class="w-1.5 h-1.5 {{ $statusDetail['dot_class'] }} rounded-full mr-1.5 {{ $statusDetail['is_on'] ? 'animate-pulse' : '' }}"></span>
                                            {{ $statusDetail['label'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-users text-3xl mb-2 block text-slate-200"></i>
                                    Belum ada data karyawan terdaftar di database.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noResultRow" class="hidden">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-3xl mb-2 block text-slate-200"></i>
                                Karyawan dengan nama tersebut tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- MODAL POPUP DETAIL KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        <div id="modalDataContent" class="hidden space-y-5">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50 dark:ring-sky-950/60"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800 dark:text-slate-100"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/60 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100 dark:border-sky-800"></p>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-4 grid grid-cols-1 gap-y-3.5 text-sm">
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2">
                    <span class="text-slate-400 font-medium">NIP</span>
                    <span id="detail_nip" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>

                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">Email</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_email" class="text-slate-800 dark:text-slate-100 font-semibold truncate">-</span>
                        <a id="detail_email_link" href="#" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-sky-500 hover:bg-sky-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">No. Telepon</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_phone" class="text-slate-800 dark:text-slate-100 font-semibold">-</span>
                        <a id="detail_phone_link" href="#" target="_blank" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chat WA</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2">
                    <span class="text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>

                <div id="detail_pipeline_area_container" class="hidden grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2">
                    <span class="text-slate-400 font-medium">Wilayah Pipeline</span>
                    <div id="detail_pipeline_stations" class="col-span-2 flex flex-wrap gap-1.5 pt-0.5">
                    </div>
                </div>

                {{-- Biometrik Wajah & Tombol Reset Khusus Admin (Level 1) --}}
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">Biometrik Wajah</span>
                    <div class="col-span-2 flex items-center justify-between gap-2">
                        <span id="detail_biometric_badge" class="px-2.5 py-1 rounded-lg text-xs font-bold"></span>
                        @php
                            $canResetBio = Auth::user()->isLevel1();
                        @endphp
                        @if($canResetBio)
                            <button type="button" id="btnResetBiometric" onclick="confirmResetBiometric()" class="hidden text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-xl transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                <span>Reset Biometrik</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50/80 dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-100 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sistem Jadwal Kerja</span>
                        <span id="detail_schedule_badge" class="px-2 py-0.5 text-xs font-bold rounded-md"></span>
                    </div>

                    <div id="detail_normal_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Kerja Masuk:</span>
                            <span id="detail_normal_days" class="font-semibold text-slate-700 dark:text-slate-200"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Libur Kerja:</span>
                            <span class="font-semibold text-rose-600 dark:text-rose-400">Sabtu & Minggu</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jam Operasional:</span>
                            <span id="detail_normal_hours" class="font-semibold text-emerald-600 dark:text-emerald-400"></span>
                        </div>
                    </div>

                    <div id="detail_roster_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Jadwal Shift Hari Ini:</span>
                            <span id="detail_roster_today_badge" class="px-2 py-0.5 rounded font-bold text-[11px]"></span>
                        </div>
                        <div id="detail_roster_hours_container" class="flex justify-between">
                            <span class="text-slate-400">Jam Shift Kerja:</span>
                            <span id="detail_roster_hours" class="font-semibold text-slate-700 dark:text-slate-200"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 dark:border-slate-700 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL EDIT SALDO CUTI UTAMA --}}
<div id="editSaldoModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="tutupModalEditSaldo()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-5 relative z-10 animate-in fade-in zoom-in-95 duration-200 border border-slate-100 dark:border-slate-700">
        <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm mb-3">Edit Sisa Saldo Cuti</h4>

        <form id="formEditSaldo" onsubmit="submitEditSaldo(event)" class="space-y-3">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_saldo_id" name="saldo_id">

            <div>
                <label id="label_jenis_cuti" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Jenis Cuti</label>
                <input type="number" id="input_sisa_saldo" name="sisa_saldo" min="0" required class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold focus:outline-none focus:border-sky-500">
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="tutupModalEditSaldo()" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL KELOLA MULTI-ROLE KARYAWAN --}}
<div id="modalKelolaRole" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalKelolaRole()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200 border border-slate-100 dark:border-slate-700 m-4">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-sky-600 dark:text-sky-400"></i> Kelola Peran / Jabatan Karyawan
                </h3>
                <p id="labelKelolaRoleNama" class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5"></p>
            </div>
            <button type="button" onclick="tutupModalKelolaRole()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formKelolaRole" onsubmit="submitKelolaRole(event)" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="kelola_role_user_id" name="user_id">

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        Pilih Peran yang Diemban (Multi-Role)
                    </label>
                    <span class="text-[10px] text-slate-400 font-medium">Bisa pilih lebih dari satu peran</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-60 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                    @foreach($daftarRole as $r)
                        <label class="flex items-center space-x-2.5 p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-sky-500 hover:bg-sky-50/50 dark:hover:bg-slate-700/50 transition-all text-xs">
                            <input type="checkbox" name="roles[]" value="{{ $r->id }}"
                                class="rounded border-slate-300 dark:border-slate-600 text-sky-600 focus:ring-sky-500 w-4 h-4 cursor-pointer kelola-role-checkbox">
                            <span class="font-semibold text-slate-700 dark:text-slate-200 leading-tight">{{ $r->role_name }}</span>
                        </label>
                    @endforeach
                </div>
                <span id="kelola-role-error" class="text-xs text-rose-500 mt-1.5 hidden font-medium">Silakan pilih minimal satu role/jabatan.</span>

                <!-- Input Penugasan Multi-Select Rumah Meter (Khusus Role AREA (PIPELINE)) -->
                <div id="kelolaRoleRumahMeterContainer" class="hidden transition-all mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-gauge-high text-amber-600 dark:text-amber-400"></i> Penugasan Rumah Meter (Pipeline)
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllKelolaRm(true)" class="text-[10px] font-bold text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 underline cursor-pointer">Pilih Semua</button>
                            <span class="text-amber-300 dark:text-amber-600 text-xs">|</span>
                            <button type="button" onclick="selectAllKelolaRm(false)" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 underline cursor-pointer">Reset</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-44 overflow-y-auto p-2.5 bg-amber-50/50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-xl">
                        @if(isset($daftarRumahMeter) && count($daftarRumahMeter) > 0)
                            @foreach($daftarRumahMeter as $rm)
                                <label class="flex items-center space-x-2 p-1.5 bg-white dark:bg-slate-800 border border-amber-200/60 dark:border-amber-800/60 rounded-lg cursor-pointer hover:border-amber-400 text-xs select-none shadow-2xs">
                                    <input type="checkbox" name="assigned_stations[]" value="{{ $rm->id }}"
                                        class="rounded border-slate-300 dark:border-slate-600 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer kelola-rm-checkbox">
                                    <span class="font-medium text-slate-700 dark:text-slate-200 truncate text-[11px]">
                                        <strong class="font-mono text-amber-700 dark:text-amber-400">{{ $rm->kode_stasiun }}</strong> {{ $rm->name }}
                                    </span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="tutupModalKelolaRole()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSimpanKelolaRole" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Sinkronisasi Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Mermaid.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<!-- Panzoom CDN (Untuk Fitur Zoom In/Out & Pan Drag) -->
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0284c7',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold'
            }
        });
    });
</script>
@endif

<script>
    let activeKaryawanId = null;
    let panzoomInstance = null;
    let kRenderCounter = 0;

    // Data Karyawan & Roles langsung diambil dari Controller
    const rawKaryawanData = JSON.parse(`{!! json_encode($daftarKaryawan ?? []) !!}`);
    const rawRolesData    = JSON.parse(`{!! json_encode($daftarRole ?? []) !!}`);

    // Inisialisasi Mermaid.js
    mermaid.initialize({
        startOnLoad: false,
        theme: 'default',
        securityLevel: 'loose',
        flowchart: {
            useMaxWidth: true,
            htmlLabels: true,
            curve: 'basis'
        }
    });

    async function renderKaryawanOrgChart() {
        const diagramContainer = document.getElementById('karyawanOrgDiagram');
        if (!diagramContainer) return;

        diagramContainer.innerHTML = '';

        let graphDef = 'graph TD\n';
        graphDef += '    COMPANY["B O D"]\n\n';

        // 1. Kelompokkan karyawan berdasarkan seluruh roles yang ia miliki (Many-to-Many)
        const karyawanPerRole = {};
        if (rawKaryawanData && rawKaryawanData.length > 0) {
            rawKaryawanData.forEach(u => {
                const rolesList = u.roles || (u.role ? [u.role] : []);
                if (rolesList.length > 0) {
                    rolesList.forEach(r => {
                        if (!karyawanPerRole[r.id]) {
                            karyawanPerRole[r.id] = [];
                        }
                        karyawanPerRole[r.id].push(u);
                    });
                }
            });
        }

        // 2. Buat struktur diagram berdasarkan daftarRole
        if (rawRolesData && rawRolesData.length > 0) {
            rawRolesData.forEach(r => {
                const nodeId = `R${r.id}`;
                const cleanRoleName = (r.role_name || 'Role').replace(/["'()]/g, '');

                let nodeLabel = `<b>${cleanRoleName}</b><br>──────────────`;
                const listKaryawan = karyawanPerRole[r.id] || [];

                if (listKaryawan.length > 0) {
                    listKaryawan.forEach(emp => {
                        const cleanName = (emp.name || '').replace(/["'()]/g, '');
                        nodeLabel += `<br>${cleanName}`;
                    });
                } else {
                    nodeLabel += `<br><i>Posisi Kosong</i>`;
                }

                graphDef += `    ${nodeId}["${nodeLabel}"]\n`;

                if (r.parent_role_id && rawRolesData.some(parent => parent.id == r.parent_role_id)) {
                    graphDef += `    R${r.parent_role_id} --> ${nodeId}\n`;
                } else {
                    graphDef += `    COMPANY --> ${nodeId}\n`;
                }
            });
        }

        try {
            kRenderCounter++;
            const elementId = `karyawanSvg_${kRenderCounter}`;
            const { svg } = await mermaid.render(elementId, graphDef);
            diagramContainer.innerHTML = svg;

            initPanzoom();

        } catch (err) {
            console.error('Org-Chart Render Error:', err);
            diagramContainer.innerHTML = `
                <div class="text-center py-6 text-rose-500 text-xs font-semibold">
                    <i class="fa-solid fa-triangle-exclamation text-2xl mb-2 block"></i>
                    Gagal merender skema organisasi. Klik tombol "Reload" di atas.
                </div>
            `;
        }
    }

    function initPanzoom() {
        const elem = document.getElementById('karyawanOrgDiagram');
        const parent = document.getElementById('mermaidParent');
        if (!elem || !parent) return;

        if (panzoomInstance) {
            panzoomInstance.destroy();
        }

        panzoomInstance = Panzoom(elem, {
            maxScale: 3,
            minScale: 0.1,
            startScale: 0.9,
            canvas: true
        });

        setTimeout(() => {
            panzoomInstance.pan(0, 0);
        }, 50);

        parent.addEventListener('wheel', panzoomInstance.zoomWithWheel);

        document.getElementById('btnZoomIn').onclick = panzoomInstance.zoomIn;
        document.getElementById('btnZoomOut').onclick = panzoomInstance.zoomOut;
        document.getElementById('btnZoomReset').onclick = () => {
            panzoomInstance.reset();
            panzoomInstance.zoom(0.9);
        };
    }

    function switchTab(tabId) {
        localStorage.setItem('active_tab_karyawan', tabId);

        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-sky-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        });

        document.getElementById(tabId).classList.remove('hidden');

        const activeBtn = document.getElementById('btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.add('bg-sky-600', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        }

        if (tabId === 'tab-karyawan-pohon') {
            setTimeout(() => {
                renderKaryawanOrgChart();
            }, 50);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        // BACA TAB TERAKHIR DARI LOCALSTORAGE (JIKA KOSONG, DEFAULT KE TAB POHON)
        const savedTab = localStorage.getItem('active_tab_karyawan') || 'tab-karyawan-pohon';
        switchTab(savedTab);

        const searchInput = document.getElementById("searchKaryawanInput");
        const noResultRow = document.getElementById("noResultRow");

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const filter = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll("#karyawanTableBody .table-row-item");
                let hasVisibleRow = false;

                rows.forEach(row => {
                    const nameCell = row.querySelector(".target-search-name");
                    if (nameCell) {
                        const nameText = nameCell.textContent.toLowerCase();
                        if (nameText.includes(filter)) {
                            row.style.setProperty('display', '', 'important');
                            hasVisibleRow = true;
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    }
                });

                if (rows.length > 0) {
                    if (!hasVisibleRow) {
                        noResultRow.classList.remove("hidden");
                    } else {
                        noResultRow.classList.add("hidden");
                    }
                }
            });
        }

        const headers = document.querySelectorAll("#karyawanTable th[data-sort]");
        const tableBody = document.getElementById("karyawanTableBody");
        let currentSortColumn = -1;
        let isAscending = true;

        headers.forEach(header => {
            header.addEventListener("click", function () {
                const columnIndex = parseInt(this.getAttribute("data-sort"));
                const rowsArray = Array.from(tableBody.querySelectorAll(".table-row-item"));

                if (currentSortColumn === columnIndex) {
                    isAscending = !isAscending;
                } else {
                    isAscending = true;
                    currentSortColumn = columnIndex;
                }

                headers.forEach(h => {
                    const icon = h.querySelector("i");
                    if (icon) {
                        icon.className = "fa-solid fa-sort ml-1.5 text-slate-300";
                    }
                });

                const currentIcon = this.querySelector("i");
                if (currentIcon) {
                    currentIcon.className = isAscending
                        ? "fa-solid fa-sort-up ml-1.5 text-sky-600"
                        : "fa-solid fa-sort-down ml-1.5 text-sky-600";
                }

                rowsArray.sort((rowA, rowB) => {
                    let cellA = rowA.children[columnIndex].textContent.trim();
                    let cellB = rowB.children[columnIndex].textContent.trim();

                    cellA = cellA.replace(/[^\x20-\x7E]/g, '').trim();
                    cellB = cellB.replace(/[^\x20-\x7E]/g, '').trim();

                    return isAscending
                        ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' })
                        : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
                });

                rowsArray.forEach(row => tableBody.appendChild(row));
                if (noResultRow) tableBody.appendChild(noResultRow);
            });
        });

        const modal = document.getElementById("detailKaryawanModal");
        const backdrop = document.getElementById("detailModalBackdrop");
        const closeBtn = document.getElementById("closeDetailModalBtn");
        const closeBtn2 = document.getElementById("closeDetailModalBtn2");

        function hideModal() {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        if (closeBtn) closeBtn.addEventListener("click", hideModal);
        if (closeBtn2) closeBtn2.addEventListener("click", hideModal);
        if (backdrop) backdrop.addEventListener("click", hideModal);

        document.addEventListener("click", function(e) {
            const button = e.target.closest(".btn-detail-karyawan");
            if (button) {
                activeKaryawanId = button.getAttribute("data-id");
                loadDetailKaryawan(activeKaryawanId);
            }
        });
    });

    function loadDetailKaryawan(karyawanId) {
        const modal = document.getElementById("detailKaryawanModal");
        const loadingSection = document.getElementById("modalLoading");
        const contentSection = document.getElementById("modalDataContent");

        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.classList.add("overflow-hidden");

        loadingSection.classList.remove("hidden");
        contentSection.classList.add("hidden");

        fetch(`/admin/karyawan/${karyawanId}/detail`)
            .then(response => {
                if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
                return response.json();
            })
            .then(data => {
                if (!data || Object.keys(data).length === 0) throw new Error("Data karyawan kosong.");

                loadingSection.classList.add("hidden");
                contentSection.classList.remove("hidden");

                document.getElementById("detail_name").textContent = data.name || '-';
                document.getElementById("detail_nip").textContent = data.nip ? data.nip : '-';
                document.getElementById("detail_role").textContent = data.role_name ? data.role_name : 'Tidak Ada Role';
                document.getElementById("detail_station").textContent = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                // Render Cakupan Rumah Meter jika Role Pipeline
                const pipelineBox = document.getElementById("detail_pipeline_area_container");
                const pipelineStations = document.getElementById("detail_pipeline_stations");
                if (pipelineBox && pipelineStations) {
                    if (data.is_pipeline && Array.isArray(data.assigned_stations) && data.assigned_stations.length > 0) {
                        pipelineBox.classList.remove("hidden");
                        pipelineStations.innerHTML = data.assigned_stations.map(st => `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                <i class="fa-solid fa-gauge-high text-[10px] text-amber-500"></i>
                                <span><strong class="font-mono font-bold text-amber-700">${st.kode_stasiun}</strong> ${st.name}</span>
                            </span>
                        `).join('');
                    } else if (data.is_pipeline) {
                        pipelineBox.classList.remove("hidden");
                        pipelineStations.innerHTML = `<span class="text-slate-400 text-xs italic">Belum ada penugasan Rumah Meter khusus.</span>`;
                    } else {
                        pipelineBox.classList.add("hidden");
                        pipelineStations.innerHTML = '';
                    }
                }

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
                    if (cleanNumber.startsWith('0')) cleanNumber = '62' + cleanNumber.substring(1);
                    phoneLink.href = `https://wa.me/${cleanNumber}`;
                    phoneLink.classList.remove("hidden");
                } else {
                    phoneSpan.textContent = '-';
                    phoneLink.classList.add("hidden");
                }

                const scheduleBadge = document.getElementById("detail_schedule_badge");
                const normalBox = document.getElementById("detail_normal_schedule_box");
                const rosterBox = document.getElementById("detail_roster_schedule_box");

                if (data.schedule_type === 'normal') {
                    scheduleBadge.textContent = 'Jadwal Normal';
                    scheduleBadge.className = 'px-2 py-0.5 text-xs font-bold rounded-md bg-sky-100 text-sky-700 border border-sky-200';
                    normalBox.classList.remove('hidden');
                    rosterBox.classList.add('hidden');
                    document.getElementById("detail_normal_days").textContent = data.normal_work_days;
                    document.getElementById("detail_normal_hours").textContent = `${data.normal_check_in} - ${data.normal_check_out} WIB`;
                } else {
                    scheduleBadge.textContent = 'Jadwal Roster';
                    scheduleBadge.className = 'px-2 py-0.5 text-xs font-bold rounded-md bg-purple-100 text-purple-700 border border-purple-200';
                    rosterBox.classList.remove('hidden');
                    normalBox.classList.add('hidden');

                    const rosterTodayBadge = document.getElementById("detail_roster_today_badge");
                    const rosterHoursContainer = document.getElementById("detail_roster_hours_container");
                    const rosterHours = document.getElementById("detail_roster_hours");

                    const shiftType = (data.today_shift_type || '').toLowerCase();
                    const shiftName = (data.today_shift || '').toLowerCase();

                    if (shiftType === 'pagi' || shiftName.includes('pagi')) {
                        rosterTodayBadge.textContent = 'Shift Pagi';
                        rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-emerald-100 text-emerald-700 border border-emerald-200';
                        rosterHoursContainer.classList.remove('hidden');
                        rosterHours.textContent = `${data.today_scheduled_in || '07:00'} - ${data.today_scheduled_out || '19:00'} WIB`;
                    } else if (shiftType === 'malam' || shiftName.includes('malam')) {
                        rosterTodayBadge.textContent = 'Shift Malam';
                        rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-indigo-100 text-indigo-700 border border-indigo-200';
                        rosterHoursContainer.classList.remove('hidden');
                        rosterHours.textContent = `${data.today_scheduled_in || '19:00'} - ${data.today_scheduled_out || '07:00'} WIB`;
                    } else {
                        rosterTodayBadge.textContent = 'OFF / Libur Roster';
                        rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-rose-100 text-rose-700 border border-rose-200';
                        rosterHoursContainer.classList.add('hidden');
                    }
                }

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

                // Render Status Biometrik Wajah & Tombol Reset
                const biometricBadge = document.getElementById("detail_biometric_badge");
                const btnResetBiometric = document.getElementById("btnResetBiometric");
                if (biometricBadge) {
                    if (data.is_face_registered) {
                        biometricBadge.innerHTML = `<i class="fa-solid fa-lock text-emerald-600 mr-1"></i> Terkunci & Aktif`;
                        biometricBadge.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200";
                        if (btnResetBiometric) btnResetBiometric.classList.remove("hidden");
                    } else {
                        biometricBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i> Belum Terdaftar / Kosong`;
                        biometricBadge.className = "px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200";
                        if (btnResetBiometric) btnResetBiometric.classList.add("hidden");
                    }
                }
            })
            .catch(error => {
                console.error(error);
                loadingSection.classList.add("hidden");
                Swal.fire({
                    title: 'Gagal!',
                    text: `Terjadi kesalahan saat memuat data karyawan: ${error.message}`,
                    icon: 'error',
                    confirmButtonColor: '#e11d48',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
                });
            });
    }

    function confirmResetBiometric() {
        if (!activeKaryawanId) return;

        Swal.fire({
            title: 'Reset Biometrik Wajah?',
            text: 'Data descriptor biometrik wajah karyawan ini akan dihapus. Karyawan akan diizinkan merekam wajahnya 1x lagi melalui dashboard.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Reset Biometrik',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold', cancelButton: 'px-5 py-2.5 rounded-xl font-bold' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang mereset data biometrik wajah...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(`/admin/karyawan/${activeKaryawanId}/reset-biometric`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: resData.message,
                            icon: 'success',
                            confirmButtonColor: '#0284c7',
                            customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
                        }).then(() => {
                            loadDetailKaryawan(activeKaryawanId);
                        });
                    } else {
                        Swal.fire('Gagal!', resData.message || 'Terjadi kesalahan.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
                });
            }
        });
    }

    function bukaModalEditSaldoBtn(button) {
        const saldoId = button.getAttribute('data-id') || 0;
        const namaCuti = button.getAttribute('data-nama') || 'Cuti Utama';
        const currentSaldo = button.getAttribute('data-saldo') || 0;

        document.getElementById('edit_saldo_id').value = saldoId;
        document.getElementById('label_jenis_cuti').textContent = `Edit Saldo: ${namaCuti}`;
        document.getElementById('input_sisa_saldo').value = currentSaldo;

        const modalEdit = document.getElementById('editSaldoModal');
        modalEdit.classList.remove('hidden');
        modalEdit.classList.add('flex');
    }

    function tutupModalEditSaldo() {
        const modalEdit = document.getElementById('editSaldoModal');
        modalEdit.classList.remove('flex');
        modalEdit.classList.add('hidden');
    }

    function submitEditSaldo(e) {
        e.preventDefault();
        const saldoId = document.getElementById('edit_saldo_id').value;
        const newSaldo = document.getElementById('input_sisa_saldo').value;
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '{{ csrf_token() }}';

        fetch(`/admin/karyawan/saldo-cuti/${saldoId}/update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ sisa_saldo: newSaldo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success || data.message) {
                tutupModalEditSaldo();
                Swal.fire({
                    title: 'BERHASIL!',
                    text: data.message || 'Sisa saldo cuti berhasil diperbarui!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0284c7',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold'
                    }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal memperbarui saldo cuti.',
                    icon: 'error',
                    confirmButtonColor: '#e11d48',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan jaringan.',
                icon: 'error',
                confirmButtonColor: '#e11d48',
                customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
            });
        });
    }

    // HANDLER MODAL KELOLA MULTI-ROLE KARYAWAN
    function bukaModalKelolaRole(userId, userName, roleIds, assignedStationIds = []) {
        document.getElementById('kelola_role_user_id').value = userId;
        document.getElementById('labelKelolaRoleNama').innerText = 'Karyawan: ' + userName;

        const roleIdsArr = Array.isArray(roleIds) ? roleIds.map(Number) : [];
        const checkboxes = document.querySelectorAll('.kelola-role-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = roleIdsArr.includes(parseInt(cb.value));
        });

        // Set status centang Rumah Meter
        const assignedRmArr = Array.isArray(assignedStationIds) ? assignedStationIds.map(Number) : [];
        const rmCheckboxes = document.querySelectorAll('.kelola-rm-checkbox');
        rmCheckboxes.forEach(cb => {
            cb.checked = assignedRmArr.includes(parseInt(cb.value));
        });

        // Evaluasi visibilitas Rumah Meter untuk role Pipeline
        evalKelolaRolePipeline();

        const errorMsg = document.getElementById('kelola-role-error');
        if (errorMsg) errorMsg.classList.add('hidden');

        const modal = document.getElementById('modalKelolaRole');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function evalKelolaRolePipeline() {
        const checkedRoleCbs = Array.from(document.querySelectorAll('.kelola-role-checkbox:checked'));
        const isPipelineChecked = checkedRoleCbs.some(cb => {
            const label = cb.closest('label')?.innerText.toLowerCase() || '';
            return label.includes('pipeline') || cb.value === '14';
        });

        const rmContainer = document.getElementById('kelolaRoleRumahMeterContainer');
        if (rmContainer) {
            if (isPipelineChecked) {
                rmContainer.classList.remove('hidden');
            } else {
                rmContainer.classList.add('hidden');
                document.querySelectorAll('.kelola-rm-checkbox').forEach(cb => cb.checked = false);
            }
        }
    }

    function selectAllKelolaRm(selectAll = true) {
        document.querySelectorAll('.kelola-rm-checkbox').forEach(cb => {
            cb.checked = selectAll;
        });
    }

    // Pasang listener untuk perubahan checkbox role di modal kelola role
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.kelola-role-checkbox').forEach(cb => {
            cb.addEventListener('change', evalKelolaRolePipeline);
        });
    });

    function tutupModalKelolaRole() {
        const modal = document.getElementById('modalKelolaRole');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function submitKelolaRole(e) {
        e.preventDefault();
        const userId = document.getElementById('kelola_role_user_id').value;
        const checked = Array.from(document.querySelectorAll('.kelola-role-checkbox:checked')).map(cb => cb.value);
        const checkedRm = Array.from(document.querySelectorAll('.kelola-rm-checkbox:checked')).map(cb => cb.value);

        const errorMsg = document.getElementById('kelola-role-error');
        if (checked.length === 0) {
            if (errorMsg) errorMsg.classList.remove('hidden');
            return;
        }
        if (errorMsg) errorMsg.classList.add('hidden');

        const btnSubmit = document.getElementById('btnSimpanKelolaRole');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...`;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const response = await fetch(`/admin/karyawan/${userId}/roles`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ roles: checked, assigned_stations: checkedRm })
            });

            const res = await response.json();
            if (response.ok && res.success) {
                tutupModalKelolaRole();

                Swal.fire({
                    title: 'BERHASIL!',
                    text: res.message || 'Peran karyawan berhasil disinkronkan!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0284c7',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(res.message || 'Gagal memperbarui peran karyawan.');
            }
        } catch (err) {
            Swal.fire({
                title: 'Gagal!',
                text: err.message || 'Terjadi kesalahan sistem.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Simpan Sinkronisasi Role`;
        }
    }
</script>
@endpush

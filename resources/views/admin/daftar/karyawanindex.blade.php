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
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2 text-rose-500"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- NAVIGASI TAB UTAMA --}}
    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3 rounded-2xl shadow-xs">
        <div class="flex space-x-2">
            <button type="button" onclick="switchTab('tab-karyawan-tabel')" id="btn-tab-karyawan-tabel" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-sky-600 text-white shadow-xs">
                <i class="fa-solid fa-users mr-1.5"></i> Daftar Manajemen Karyawan
            </button>
            <button type="button" onclick="switchTab('tab-karyawan-pohon')" id="btn-tab-karyawan-pohon" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Struktur Organisasi Karyawan
            </button>
        </div>
    </div>

    {{-- TAB 1: TABEL DAFTAR KARYAWAN --}}
    <div id="tab-karyawan-tabel" class="tab-content">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-sky-50/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800">Daftar Manajemen Karyawan</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200/80">
                            {{ isset($daftarKaryawan) ? count($daftarKaryawan) : 0 }} Karyawan
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola data seluruh staf, hak akses role, penempatan stasiun kerja, dan informasi akun.</p>
                </div>
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="searchKaryawanInput" placeholder="Cari nama karyawan..."
                        class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all text-slate-700 placeholder-slate-400">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="karyawanTable">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                            <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="0">
                                Nama Lengkap <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="1">
                                Jabatan <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="2">
                                Penempatan Stasiun <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                            <th class="px-6 py-4 text-center">Sisa Cuti Utama</th>
                            <th class="px-6 py-4 text-center w-28">Edit Saldo Cuti</th>
                            <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="5">
                                Status Operasional <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700" id="karyawanTableBody">
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
                            <tr class="hover:bg-slate-50/80 transition-colors table-row-item">
                                <td class="px-6 py-4 font-medium text-slate-900" data-search-value="{{ strtolower($karyawan->name) }}">
                                    <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id }}">
                                        <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden border border-slate-100 shrink-0">
                                            @if($karyawan->profile_photo)
                                                <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($karyawan->name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors target-search-name">{{ $karyawan->name }}</span>
                                            <span class="text-xs text-slate-400">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @php
                                        $roleName = $karyawan->role->role_name ?? 'Tidak Ada Role';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block
                                        {{ strtolower($roleName) == 'manager' ? 'bg-purple-50 text-purple-700 border border-purple-100' : (strtolower($roleName) == 'supervisor' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700 border border-slate-200/50') }}">
                                        {{ $roleName }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if(($karyawan->station && !empty($karyawan->station->name)))
                                        <span class="inline-flex items-center text-xs text-slate-700 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/60">
                                            <i class="fa-solid fa-location-dot mr-1.5 text-rose-500 text-xs"></i>
                                            {{ $karyawan->station->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-rose-500 font-medium bg-rose-50 px-2 py-1 rounded-xl italic border border-rose-100">
                                            ⚠️ Stasiun Belum Diatur
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $sisaCutiVal }} Hari</span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <button type="button"
                                        data-id="{{ $saldoIdVal }}"
                                        data-nama="{{ $namaCutiText }}"
                                        data-saldo="{{ $sisaCutiVal }}"
                                        onclick="bukaModalEditSaldoBtn(this)"
                                        class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/60 rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1 shadow-sm">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($karyawan->cuti_aktif && $karyawan->cuti_aktif->count() > 0)
                                        @php $cuti = $karyawan->cuti_aktif->first(); @endphp
                                        <span class="inline-flex items-center text-xs text-rose-600 bg-rose-50 border border-rose-100 px-2.5 py-1 rounded-full font-bold" title="{{ $cuti->alasan_cuti }}">
                                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5 animate-pulse"></span>
                                            On Leave (Cuti)
                                        </span>
                                    @else
                                        @php
                                            $statusDetail = $karyawan->status_detail ?? [
                                                'badge_class' => 'bg-slate-50 text-slate-600 border-slate-200',
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

    {{-- TAB 2: POHON ORGANISASI KARYAWAN BERDASARKAN ROLE --}}
    <div id="tab-karyawan-pohon" class="tab-content hidden space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">

            {{-- HEADER & TOMBOL KONTROL ZOOM --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sitemap text-indigo-600"></i> Struktur Organisasi Karyawan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Skema hirarki role jabatan beserta daftar karyawan terdaftar yang mengisi posisi tersebut.</p>
                </div>

                {{-- TOMBOL ZOOM CONTROL --}}
                <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                    <button type="button" id="btnZoomIn" title="Zoom In (+)" class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                    </button>
                    <button type="button" id="btnZoomOut" title="Zoom Out (-)" class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass-minus text-sm"></i>
                    </button>
                    <button type="button" id="btnZoomReset" title="Reset Ukuran" class="w-8 h-8 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition-all shadow-xs flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-rotate text-sm"></i>
                    </button>
                    <div class="h-4 w-px bg-slate-300 mx-1"></div>
                    <button type="button" onclick="renderKaryawanOrgChart()" class="px-3 h-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i> Reload
                    </button>
                </div>
            </div>

            {{-- WADAH RENDER DIAGRAM MERMAID DENGAN ZOOM --}}
            <div id="mermaidParent" class="mermaid-container flex justify-center items-center">
                <div id="karyawanOrgDiagram" class="w-full flex justify-center transition-transform origin-center"></div>
            </div>
        </div>
    </div>

</div>

{{-- MODAL POPUP DETAIL KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        <div id="modalDataContent" class="hidden space-y-5">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100"></p>
            </div>

            <div class="border-t border-slate-100 pt-4 grid grid-cols-1 gap-y-3.5 text-sm">
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
                    <span class="text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>

                <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sistem Jadwal Kerja</span>
                        <span id="detail_schedule_badge" class="px-2 py-0.5 text-xs font-bold rounded-md"></span>
                    </div>

                    <div id="detail_normal_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Kerja Masuk:</span>
                            <span id="detail_normal_days" class="font-semibold text-slate-700"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Libur Kerja:</span>
                            <span class="font-semibold text-rose-600">Sabtu & Minggu</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jam Operasional:</span>
                            <span id="detail_normal_hours" class="font-semibold text-emerald-600"></span>
                        </div>
                    </div>

                    <div id="detail_roster_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Jadwal Shift Hari Ini:</span>
                            <span id="detail_roster_today_badge" class="px-2 py-0.5 rounded font-bold text-[11px]"></span>
                        </div>
                        <div id="detail_roster_hours_container" class="flex justify-between">
                            <span class="text-slate-400">Jam Shift Kerja:</span>
                            <span id="detail_roster_hours" class="font-semibold text-slate-700"></span>
                        </div>
                    </div>
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

{{-- MODAL EDIT SALDO CUTI UTAMA --}}
<div id="editSaldoModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="tutupModalEditSaldo()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5 relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <h4 class="font-bold text-slate-800 text-sm mb-3">Edit Sisa Saldo Cuti</h4>

        <form id="formEditSaldo" onsubmit="submitEditSaldo(event)" class="space-y-3">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_saldo_id" name="saldo_id">

            <div>
                <label id="label_jenis_cuti" class="block text-xs font-semibold text-slate-500 mb-1">Jenis Cuti</label>
                <input type="number" id="input_sisa_saldo" name="sisa_saldo" min="0" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:border-sky-500">
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="tutupModalEditSaldo()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm">Simpan</button>
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

        // 1. Kelompokkan karyawan berdasarkan role_id
        const karyawanPerRole = {};
        if (rawKaryawanData && rawKaryawanData.length > 0) {
            rawKaryawanData.forEach(u => {
                const rId = u.role_id || (u.role ? u.role.id : null);
                if (rId) {
                    if (!karyawanPerRole[rId]) {
                        karyawanPerRole[rId] = [];
                    }
                    karyawanPerRole[rId].push(u);
                }
            });
        }

        // 2. Buat struktur diagram berdasarkan daftarRole persis seperti di Halaman Role
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

                // Hubungkan rantai hirarki sesuai parent_role_id
                if (r.parent_role_id && rawRolesData.some(parent => parent.id == r.parent_role_id)) {
                    graphDef += `    R${r.parent_role_id} --> ${nodeId}\n`;
                } else {
                    graphDef += `    COMPANY --> ${nodeId}\n`;
                }
            });
        } else {
            // Fallback dinamis jika $daftarRole belum terisi
            const roleMap = {};
            rawKaryawanData.forEach(u => {
                const roleObj = u.role || { id: 'default', role_name: 'Staff' };
                if (!roleMap[roleObj.id]) {
                    roleMap[roleObj.id] = {
                        id: roleObj.id,
                        name: roleObj.role_name,
                        members: []
                    };
                }
                roleMap[roleObj.id].members.push(u);
            });

            Object.values(roleMap).forEach(r => {
                const nodeId = `R${r.id}`;
                let nodeLabel = `<b>${r.name}</b>`;
                r.members.forEach(emp => {
                    nodeLabel += `<br>${emp.name}`;
                });
                graphDef += `    ${nodeId}["${nodeLabel}"]\n`;
                graphDef += `    COMPANY --> ${nodeId}\n`;
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
            startScale: 1.0,
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
            panzoomInstance.zoom(1.0);
        };
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-sky-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        });

        document.getElementById(tabId).classList.remove('hidden');

        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.add('bg-sky-600', 'text-white', 'shadow-xs');
        activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');

        if (tabId === 'tab-karyawan-pohon') {
            setTimeout(() => {
                renderKaryawanOrgChart();
            }, 50);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Otomatis jalankan render diagram saat halaman dimuat
        renderKaryawanOrgChart();

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
</script>
@endpush

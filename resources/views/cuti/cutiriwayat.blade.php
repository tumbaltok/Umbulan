@extends('layouts.app')
@section('title', 'Riwayat Pengajuan Cuti')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- BREADCRUMB & HEADER SECTION --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-sky-600 dark:hover:text-sky-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-house text-[11px] text-slate-400 dark:text-slate-500"></i>
                    <span>Dashboard</span>
                </a>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-sky-600 dark:text-sky-400 font-bold">Riwayat Cuti</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-calendar-check text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Riwayat Pengajuan Cuti Anda
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Daftar seluruh permohonan cuti dan izin kerja yang pernah Anda ajukan di PT META Adhya Tirta Umbulan
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('cuti.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white text-xs font-bold transition-all shadow-md shadow-sky-600/25 hover:shadow-lg hover:shadow-sky-600/35 active:scale-95 group cursor-pointer">
                <i class="fa-solid fa-plus text-xs text-white group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="text-white">Ajukan Cuti Baru</span>
            </a>
        </div>
    </div>

    {{-- STATISTIC SUMMARY CARDS --}}
    @php
        $totalCuti = $pengajuanCuti->count();
        $totalApproved = $pengajuanCuti->where('status_akhir', 'approved')->count();
        $totalPending = $pengajuanCuti->where('status_akhir', 'pending')->count();
        $totalRejected = $pengajuanCuti->where('status_akhir', 'rejected')->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        {{-- Total Pengajuan --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Diajukan</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalCuti }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-layer-group text-base text-sky-600 dark:text-sky-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-folder-open text-sky-500"></i>
                <span>Seluruh arsip permohonan</span>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disetujui</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalApproved }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-check-double text-emerald-500"></i>
                <span>Telah terverifikasi atasan</span>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu Review</p>
                    <h3 class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-1">{{ $totalPending }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock text-base text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                <span>Dalam antrean verifikasi</span>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ditolak</p>
                    <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $totalRejected }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-xmark text-base text-rose-600 dark:text-rose-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-circle-info text-rose-500"></i>
                <span>Perlu revisi / dibatalkan</span>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE CARD CONTAINER --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 overflow-hidden transition-all">

        {{-- TABLE CARD HEADER & CONTROLS --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50/80 via-white to-sky-50/30 dark:from-slate-800/90 dark:via-slate-800 dark:to-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-list-check text-sm text-sky-600 dark:text-sky-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span>Daftar Permohonan Cuti</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                            {{ $totalCuti }} Data
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Status persetujuan berjenjang dan cetak dokumen resmi cuti
                    </p>
                </div>
            </div>

            {{-- LIVE SEARCH & FILTER CHIPS --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="text" id="liveSearchCuti" placeholder="Cari jenis cuti / alasan..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                </div>
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border border-slate-200/70 dark:border-slate-700/60 text-xs">
                    <button type="button" onclick="filterStatusCuti('all')" class="chip-filter active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer" data-status="all">Semua</button>
                    <button type="button" onclick="filterStatusCuti('pending')" class="chip-filter px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="pending">Menunggu</button>
                    <button type="button" onclick="filterStatusCuti('approved')" class="chip-filter px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="approved">Disetujui</button>
                    <button type="button" onclick="filterStatusCuti('rejected')" class="chip-filter px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="rejected">Ditolak</button>
                </div>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelRiwayatCuti">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4">Jenis Cuti / Detail</th>
                        <th class="px-6 py-4">Rentang Waktu</th>
                        <th class="px-6 py-4 text-center">Durasi</th>
                        <th class="px-6 py-4">Catatan Alasan</th>
                        <th class="px-6 py-4 text-center">Status Akhir</th>
                        <th class="px-6 py-4 text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                    @forelse($pengajuanCuti as $cuti)
                        @php
                            $statusNormalized = 'pending';
                            if ($cuti->status_akhir === 'approved') {
                                $statusNormalized = 'approved';
                            } elseif ($cuti->status_akhir === 'rejected' || $cuti->status_tahap_1 === 'rejected' || $cuti->status_tahap_2 === 'rejected') {
                                $statusNormalized = 'rejected';
                            }
                        @endphp
                        <tr class="row-cuti hover:bg-sky-50/40 dark:hover:bg-slate-700/40 transition-colors group" data-status="{{ $statusNormalized }}">
                            {{-- JENIS CUTI & DETAIL SUB-CUTI --}}
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                                        <i class="fa-regular fa-calendar-check text-sm text-sky-600 dark:text-sky-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-100 text-sm target-search">
                                            {{ $cuti->name_cuti }}
                                        </div>
                                        @if(!empty($cuti->nama_sub_cuti))
                                            <div class="text-[11px] text-sky-600 dark:text-sky-400 font-semibold mt-1 bg-sky-50 dark:bg-sky-950/60 px-2.5 py-0.5 rounded-md w-fit border border-sky-200/70 dark:border-sky-800/70 target-search">
                                                {{ $cuti->nama_sub_cuti }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- TANGGAL PELAKSANAAN --}}
                            <td class="px-6 py-4 align-middle text-slate-600 dark:text-slate-300 text-xs font-medium whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-slate-400 dark:text-slate-500"></i>
                                    <span>
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }}
                                        <span class="text-slate-400 mx-1">s/d</span>
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                                    </span>
                                </div>
                            </td>

                            {{-- DURASI TOTAL HARI --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-extrabold bg-slate-100 dark:bg-slate-700/70 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-600/70">
                                    {{ $cuti->total_hari }} Hari
                                </span>
                            </td>

                            {{-- CATATAN ALASAN --}}
                            <td class="px-6 py-4 align-middle text-slate-600 dark:text-slate-400 text-xs max-w-xs truncate target-search" title="{{ blank($cuti->alasan_cuti) ? '-' : $cuti->alasan_cuti }}">
                                {{ blank($cuti->alasan_cuti) ? '-' : $cuti->alasan_cuti }}
                            </td>

                            {{-- STATUS PERSETUJUAN --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                @if($cuti->status_akhir == 'approved')
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200/80 dark:border-emerald-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($cuti->status_akhir == 'rejected' || $cuti->status_tahap_1 == 'rejected' || $cuti->status_tahap_2 == 'rejected')
                                    <span class="px-3 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200/80 dark:border-rose-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200/80 dark:border-amber-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" data-id="{{ $cuti->id }}"
                                            class="btn-detail-cuti px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-sky-600 dark:hover:text-sky-400 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                        <i class="fa-solid fa-eye text-xs text-sky-600 dark:text-sky-400"></i>
                                        <span>Lihat</span>
                                    </button>

                                    @if($cuti->status_akhir == 'approved')
                                        <button type="button"
                                                data-url="{{ route('cuti.cetak', $cuti->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-sm hover:shadow cursor-pointer">
                                            <i class="fa-solid fa-print text-xs text-white"></i>
                                            <span class="text-white">Cetak</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRowPlaceholder">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-regular fa-calendar-xmark"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Riwayat Cuti</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                    Anda belum pernah mengajukan permohonan cuti. Klik tombol di bawah untuk membuat permohonan baru.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('cuti.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-plus text-white"></i>
                                        <span class="text-white">Ajukan Sekarang</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noSearchResultRow" class="hidden">
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass text-slate-300 dark:text-slate-600 text-2xl mb-2 block"></i>
                            Tidak ada pengajuan cuti yang cocok dengan kata kunci pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: DETAILED INFORMATION POPUP --}}
<div id="detailCutiModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div id="cutiModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg p-6 relative z-10 transform transition-all max-h-[90vh] overflow-y-auto border border-slate-200/80 dark:border-slate-700/80">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700/80">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice text-base text-sky-600 dark:text-sky-400"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">
                        Detail Pengajuan Cuti
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Rincian parameter permohonan & status persetujuan</p>
                </div>
            </div>
            <button type="button" id="closeCutiModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Modal Loading State --}}
        <div id="cutiModalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Sedang menyinkronkan data...</p>
        </div>

        {{-- Modal Content Area --}}
        <div id="cutiModalContent" class="hidden space-y-4 pt-4">
            <div class="bg-gradient-to-r from-sky-50/70 to-slate-50/70 dark:from-slate-900/60 dark:to-slate-900/40 p-4 rounded-2xl border border-sky-100/80 dark:border-slate-700 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-400 block font-bold uppercase tracking-wider">Kategori Utama</span>
                    <span id="txt_jenis_cuti" class="font-black text-slate-800 dark:text-slate-100 mt-1 block">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-400 block font-bold uppercase tracking-wider">Detail Keperluan</span>
                    <span id="txt_sub_cuti" class="font-bold text-sky-600 dark:text-sky-400 mt-1 block">-</span>
                </div>
            </div>

            <div class="border border-slate-200/80 dark:border-slate-700/80 rounded-2xl divide-y divide-slate-100 dark:divide-slate-700/80 text-xs overflow-hidden">
                <div class="grid grid-cols-3 p-3.5 bg-white dark:bg-slate-800/60">
                    <span class="text-slate-400 dark:text-slate-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar text-sky-500"></i> Rentang Waktu
                    </span>
                    <span id="txt_rentang_tanggal" class="col-span-2 text-slate-800 dark:text-slate-100 font-bold">-</span>
                </div>
                <div class="grid grid-cols-3 p-3.5 bg-white dark:bg-slate-800/60">
                    <span class="text-slate-400 dark:text-slate-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-hourglass-half text-sky-500"></i> Total Akumulasi
                    </span>
                    <span id="txt_total_hari" class="col-span-2 text-slate-800 dark:text-slate-100 font-extrabold">-</span>
                </div>
                <div class="grid grid-cols-3 p-3.5 bg-white dark:bg-slate-800/60">
                    <span class="text-slate-400 dark:text-slate-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-regular fa-comment-dots text-sky-500"></i> Alasan Izin
                    </span>
                    <span id="txt_alasan_cuti" class="col-span-2 text-slate-700 dark:text-slate-300 leading-relaxed font-medium">-</span>
                </div>
                <div class="grid grid-cols-3 p-3.5 bg-white dark:bg-slate-800/60 items-center">
                    <span class="text-slate-400 dark:text-slate-400 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-sky-500"></i> Status Akhir
                    </span>
                    <div id="wrapper_status" class="col-span-2"></div>
                </div>
            </div>

            <div id="wrapper_dokumen" class="border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 space-y-2 bg-sky-50/20 dark:bg-sky-950/20">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-paperclip text-sky-500"></i> Berkas Lampiran Pendukung
                </span>
                <div id="dokumen_render_area"></div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center mt-6 justify-end border-t border-slate-100 dark:border-slate-700/80 pt-4">
            <button type="button" id="closeCutiModalBtn2" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Search & Status Filtering
    function filterStatusCuti(status) {
        document.querySelectorAll('.chip-filter').forEach(btn => {
            if (btn.getAttribute('data-status') === status) {
                btn.className = "chip-filter active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer";
            } else {
                btn.className = "chip-filter px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer";
            }
        });
        applyCutiFilters();
    }

    const searchInputCuti = document.getElementById('liveSearchCuti');
    if (searchInputCuti) {
        searchInputCuti.addEventListener('input', applyCutiFilters);
    }

    function applyCutiFilters() {
        const query = (document.getElementById('liveSearchCuti')?.value || '').toLowerCase().trim();
        const activeChip = document.querySelector('.chip-filter.active');
        const selectedStatus = activeChip ? activeChip.getAttribute('data-status') : 'all';

        const rows = document.querySelectorAll('.row-cuti');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const statusMatch = (selectedStatus === 'all') || (rowStatus === selectedStatus);

            let textMatch = true;
            if (query.length > 0) {
                const searchTargets = row.querySelectorAll('.target-search');
                let rowText = '';
                searchTargets.forEach(t => rowText += ' ' + t.innerText);
                textMatch = rowText.toLowerCase().includes(query);
            }

            if (statusMatch && textMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultRow = document.getElementById('noSearchResultRow');
        if (noResultRow) {
            noResultRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }


    // Modal Detail Cuti
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("detailCutiModal");
        const backdrop = document.getElementById("cutiModalBackdrop");
        const closeBtn = document.getElementById("closeCutiModalBtn");
        const closeBtn2 = document.getElementById("closeCutiModalBtn2");

        const loadingSection = document.getElementById("cutiModalLoading");
        const contentSection = document.getElementById("cutiModalContent");

        function openModal() {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function closeModal() {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (closeBtn2) closeBtn2.addEventListener("click", closeModal);
        if (backdrop) backdrop.addEventListener("click", closeModal);

        document.querySelectorAll(".btn-detail-cuti").forEach(btn => {
            btn.addEventListener("click", function () {
                const cutiId = this.getAttribute("data-id");

                openModal();
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");

                fetch(`/cuti/riwayat/${cutiId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error("Jaringan bermasalah");
                        return response.json();
                    })
                    .then(data => {
                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        document.getElementById("txt_jenis_cuti").innerText = data.name_cuti;
                        document.getElementById("txt_sub_cuti").innerText = data.nama_sub_cuti ? data.nama_sub_cuti : '-';
                        document.getElementById("txt_rentang_tanggal").innerText = `${data.tanggal_mulai_formatted} s/d ${data.tanggal_selesai_formatted}`;
                        document.getElementById("txt_total_hari").innerText = `${data.total_hari} Hari`;
                        document.getElementById("txt_alasan_cuti").innerText = data.alasan_cuti ? data.alasan_cuti : '-';

                        const wrapperStatus = document.getElementById("wrapper_status");
                        if (data.status_akhir === 'approved') {
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Disetujui</span>`;
                        } else if (data.status_akhir === 'rejected' || data.status_tahap_1 === 'rejected' || data.status_tahap_2 === 'rejected') {
                            let note = data.catatan_penolakan ? `<p class="text-xs text-rose-600 dark:text-rose-400 mt-1 italic font-medium">"${data.catatan_penolakan}"</p>` : '';
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200 dark:border-rose-800"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Ditolak</span> ${note}`;
                        } else {
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200 dark:border-amber-800"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Menunggu Review</span>`;
                        }

                        const docArea = document.getElementById("dokumen_render_area");
                        if (data.dokumen_pendukung) {
                            const fileUrl = `/storage/${data.dokumen_pendukung}`;
                            docArea.innerHTML = `
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xs">
                                    <div class="flex items-center gap-2.5 overflow-hidden">
                                        <div class="p-2 bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 rounded-lg text-base"><i class="fa-solid fa-file text-sky-600 dark:text-sky-400"></i></div>
                                        <div class="flex flex-col truncate">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">${data.dokumen_pendukung.split('/').pop()}</span>
                                        </div>
                                    </div>
                                    <button type="button" onclick="bukaPratinjauLampiran('${fileUrl}')" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors shrink-0 cursor-pointer">
                                        <span class="text-white">Lihat Lampiran</span>
                                    </button>
                                </div>`;
                        } else {
                            docArea.innerHTML = `<span class="text-xs italic text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800/80 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-3 block text-center">Tidak melampirkan berkas dokumen apapun.</span>`;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Gagal memuat data detail pengajuan cuti.");
                        closeModal();
                    });
            });
        });
    });
</script>
@endpush

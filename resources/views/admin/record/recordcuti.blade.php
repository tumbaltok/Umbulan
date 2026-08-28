@extends('layouts.app')

@section('title', 'Record Riwayat Cuti Karyawan')

@section('content')
<div class="space-y-6">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-business-time"></i>
                <span>Human Resource & Leave Management</span>
            </div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-sky-600 dark:text-sky-400"></i>
                Record Riwayat Cuti & Izin Karyawan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Log komprehensif seluruh permohonan cuti tahunan, sakit, dan izin khusus dengan verifikasi persetujuan berjenjang.
            </p>
        </div>

        {{-- PERIODE STATUS BADGE & QUICK ACTION --}}
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Rentang Periode Aktif</span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                        @if($filters['start_date'] === $filters['end_date'])
                            {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('l, d F Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}
                        @endif
                    @else
                        Semua Periode Data
                    @endif
                </span>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base border border-sky-100 dark:border-sky-800 shrink-0">
                <i class="fa-regular fa-calendar-days"></i>
            </span>

            {{-- TOMBOL EXPORT CSV --}}
            <button type="button" onclick="exportCsvCuti()" 
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-emerald-600/20 flex items-center gap-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-file-excel text-sm"></i>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    {{-- FILTER BAR PANEL COMPREHENSIVE --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <form method="GET" action="{{ route('admin.record.cuti') }}" id="filterFormCuti" class="space-y-4">
            
            {{-- Quick Chips Periode --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 mr-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-clock-rotate-left text-sky-500"></i> Preset Cepat:
                    </span>
                    <button type="button" onclick="setPeriodePreset('today')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $filters['periode'] === 'today' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setPeriodePreset('week')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $filters['periode'] === 'week' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Minggu Ini
                    </button>
                    <button type="button" onclick="setPeriodePreset('month')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $filters['periode'] === 'month' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Bulan Ini
                    </button>
                    <button type="button" onclick="setPeriodePreset('all')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $filters['periode'] === 'all' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Semua Waktu
                    </button>
                    <button type="button" onclick="openCustomDateModal()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $filters['periode'] === 'custom' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        <i class="fa-regular fa-calendar-days text-[11px]"></i>
                        <span>Custom</span>
                        @if($filters['periode'] === 'custom' && !empty($filters['start_date']))
                            <span class="text-[10px] font-mono opacity-90 pl-1 border-l border-white/30">
                                {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d/m') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d/m/Y') }}
                            </span>
                            <i class="fa-solid fa-pen text-[9px] ml-0.5 opacity-80"></i>
                        @endif
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.record.cuti') }}" 
                       class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700/70 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Reset Filter
                    </a>
                </div>
            </div>

            {{-- State Hidden Inputs (Periode, Start & End Date) --}}
            <input type="hidden" name="periode" id="periodeInput" value="{{ $filters['periode'] }}">
            <input type="hidden" name="start_date" id="startDateInput" value="{{ $filters['start_date'] }}">
            <input type="hidden" name="end_date" id="endDateInput" value="{{ $filters['end_date'] }}">

            {{-- Input Grid Filter (5 Kolom) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- 1. Filter Karyawan --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-user mr-1"></i> Karyawan
                    </label>
                    <select name="user_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawanList as $k)
                            <option value="{{ $k->id }}" {{ $filters['user_id'] == $k->id ? 'selected' : '' }}>
                                {{ $k->name }} {{ $k->nip ? '(' . $k->nip . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Filter Stasiun --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-tower-broadcast mr-1"></i> Stasiun / RM
                    </label>
                    <select name="station_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Stasiun (22 Titik)</option>
                        @foreach($stations as $st)
                            <option value="{{ $st->id }}" {{ $filters['station_id'] == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Filter Jenis Cuti --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-calendar-day mr-1"></i> Jenis Cuti
                    </label>
                    <select name="jenis_cuti_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Jenis Cuti</option>
                        @foreach($jenisCutiList as $jc)
                            <option value="{{ $jc->id }}" {{ $filters['jenis_cuti_id'] == $jc->id ? 'selected' : '' }}>
                                {{ $jc->name_cuti }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Filter Status Approval --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-tag mr-1"></i> Status Approval
                    </label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Disetujui Penuh</option>
                        <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                {{-- 5. Tombol Filter & Search Input --}}
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer h-[38px] flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-1.5"></i> Terapkan Filter
                    </button>
                </div>
            </div>

            {{-- Live Search Toolbar Baris Tambahan --}}
            <div class="pt-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="table-search" placeholder="Pencarian cepat nama karyawan, NIP, stasiun, nomor pengajuan atau alasan..." 
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-700 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all">
                </div>
            </div>

        </form>
    </div>

    {{-- CARDS STATISTIK SUMMARY HRMS CUTI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Pengajuan --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan Cuti</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">
                    {{ number_format($metrics['total']) }}
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Seluruh berkas masuk</p>
            </div>
            <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center text-xl border border-sky-100 dark:border-sky-800 shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        {{-- Card 2: Disetujui (Approved) --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Cuti Disetujui</p>
                <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1">
                    {{ number_format($metrics['approved']) }}
                </h3>
                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">Disetujui penuh L1/L2</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl border border-emerald-100 dark:border-emerald-800 shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        {{-- Card 3: Menunggu Persetujuan (Pending) --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-amber-100 dark:border-amber-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Menunggu Persetujuan</p>
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    {{ number_format($metrics['pending']) }}
                </h3>
                <p class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-0.5">Verifikasi SPV / Manager</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center text-xl border border-amber-100 dark:border-amber-800 shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        {{-- Card 4: Ditolak (Rejected) --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-rose-100 dark:border-rose-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">Cuti Ditolak</p>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ number_format($metrics['rejected']) }}
                </h3>
                <p class="text-[11px] text-rose-500/80 dark:text-rose-400/80 mt-0.5">Tidak memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/60 text-rose-500 dark:text-rose-400 rounded-2xl flex items-center justify-center text-xl border border-rose-100 dark:border-rose-800 shrink-0">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>
    </div>

    {{-- TABEL DATA TRANSAKSI CUTI STANDAR INDUSTRI --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                    Daftar Log Riwayat Cuti ({{ $daftarCuti->total() }} Data Ditemukan)
                </h2>
            </div>
            <span class="text-xs text-slate-400">Halaman {{ $daftarCuti->currentPage() }} dari {{ $daftarCuti->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tableCuti">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-5 py-3.5">No & Tanggal</th>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tipe Cuti</th>
                        <th class="px-5 py-3.5">Durasi & Periode</th>
                        <th class="px-5 py-3.5 text-center">Potong Kuota</th>
                        <th class="px-5 py-3.5">Approval Berjenjang</th>
                        <th class="px-5 py-3.5">Alasan & Bukti</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300 text-xs" id="tableBodyCuti">
                    @forelse($daftarCuti as $cuti)
                        @php
                            $karyawan = $cuti->user;
                            $jenisCuti = $cuti->jenisCuti;
                            $subCuti = $cuti->subCuti;

                            // Evaluasi aturan potong saldo cuti tahunan
                            $isPotong = false;
                            if ($jenisCuti) {
                                $namaJenis = strtolower($jenisCuti->name_cuti ?? '');
                                $kodeJenis = strtoupper($jenisCuti->kode_cuti ?? '');
                                if ($kodeJenis === 'CT' || str_contains($namaJenis, 'tahunan') || $namaJenis === 'cuti') {
                                    $isPotong = true;
                                }
                            }

                            $statusTahap1 = $cuti->status_tahap_1;
                            $statusTahap2 = $cuti->status_tahap_2;
                            $statusAkhir = $cuti->status_akhir;
                            $hasDokumen = !empty($cuti->dokumen_pendukung);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors row-item"
                            data-search="{{ strtolower($karyawan->name ?? '') }} {{ strtolower($karyawan->nip ?? '') }} {{ strtolower($karyawan->station->name ?? '') }} {{ strtolower($jenisCuti->name_cuti ?? '') }} {{ strtolower($cuti->alasan_cuti ?? '') }} #cuti-{{ sprintf('%04d', $cuti->id) }}">
                            
                            {{-- 1. No & Tanggal Diajukan --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-sky-600 dark:text-sky-400">#CUTI-{{ sprintf('%04d', $cuti->id) }}</span>
                                    <span class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[10px]"></i>
                                        {{ $cuti->created_at ? $cuti->created_at->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- 2. Karyawan (Avatar, Nama, NIP, Jabatan & Stasiun) --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden shrink-0">
                                        @if($karyawan && $karyawan->profile_photo)
                                            <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($karyawan->name ?? '??', 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-slate-800 dark:text-slate-100 font-semibold text-xs truncate max-w-[150px]">
                                            {{ $karyawan->name ?? 'User Terhapus' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-location-dot text-[9px] text-slate-400"></i>
                                            {{ $karyawan->station->name ?? 'Pusat' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Tipe Cuti & Sub-Cuti --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold inline-block w-fit
                                        {{ $isPotong ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800' : 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' }}">
                                        {{ $jenisCuti->name_cuti ?? 'Cuti Umum' }}
                                    </span>
                                    @if($subCuti)
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <i class="fa-solid fa-turn-up rotate-90 text-[9px] text-slate-400"></i>
                                            {{ $subCuti->nama_sub_cuti }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- 4. Durasi & Rentang Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 rounded-md text-[11px]">
                                            {{ $cuti->total_hari ?? 1 }} Hari Kerja
                                        </span>
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-1">
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M') }} s/d {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </td>

                            {{-- 5. Potong Kuota Saldo --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($isPotong)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-scissors text-[9px]"></i> Ya (Tahunan)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-ban text-[9px]"></i> Tidak (Non-Tahunan)
                                    </span>
                                @endif
                            </td>

                            {{-- 6. Approval Berjenjang (Level 3 / SPV & Level 1/2 / Manager) --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1.5">
                                    {{-- Tahap 1: Supervisor --}}
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="w-4 text-slate-400 font-mono text-[10px]">L3:</span>
                                        @if($statusTahap1 === 'approved')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check text-[10px]"></i> Disetujui
                                            </span>
                                        @elseif($statusTahap1 === 'rejected')
                                            <span class="text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="text-amber-500 font-medium flex items-center gap-1">
                                                <i class="fa-solid fa-clock text-[10px]"></i> Menunggu SPV
                                            </span>
                                        @endif
                                        @if($cuti->approverTahap1)
                                            <span class="text-[10px] text-slate-400">({{ Str::limit($cuti->approverTahap1->name, 10) }})</span>
                                        @endif
                                    </div>

                                    {{-- Tahap 2: Manager --}}
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="w-4 text-slate-400 font-mono text-[10px]">L1:</span>
                                        @if($statusTahap2 === 'approved')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check text-[10px]"></i> Disetujui
                                            </span>
                                        @elseif($statusTahap2 === 'rejected')
                                            <span class="text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Ditolak
                                            </span>
                                        @elseif($statusTahap2 === 'not_required')
                                            <span class="text-slate-400 font-medium">Bypass/Selesai</span>
                                        @else
                                            <span class="text-amber-500 font-medium flex items-center gap-1">
                                                <i class="fa-solid fa-clock text-[10px]"></i> Menunggu Mgr
                                            </span>
                                        @endif
                                        @if($cuti->approverTahap2)
                                            <span class="text-[10px] text-slate-400">({{ Str::limit($cuti->approverTahap2->name, 10) }})</span>
                                        @endif
                                    </div>

                                    {{-- Badge Status Akhir --}}
                                    <div>
                                        @if($statusAkhir === 'approved')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                DISETUJUI PENUH
                                            </span>
                                        @elseif($statusAkhir === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                                DITOLAK
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                DALAM PROSES
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- 7. Alasan Cuti & Lampiran Bukti --}}
                            <td class="px-5 py-4 max-w-[200px]">
                                <p class="text-xs text-slate-700 dark:text-slate-300 truncate" title="{{ $cuti->alasan_cuti }}">
                                    {{ $cuti->alasan_cuti ?: '-' }}
                                </p>
                                @if($hasDokumen)
                                    <button type="button" onclick="previewBukti('{{ asset('storage/' . $cuti->dokumen_pendukung) }}', 'Bukti Lampiran #CUTI-{{ sprintf('%04d', $cuti->id) }}')"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline mt-1 cursor-pointer">
                                        <i class="fa-solid fa-paperclip text-[10px]"></i>
                                        Lihat Berkas Lampiran
                                    </button>
                                @endif
                            </td>

                            {{-- 8. Aksi (Modal Detail & Cetak PDF) --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Modal Detail --}}
                                    <button type="button" onclick="openDetailCutiModal({{ json_encode([
                                        'id' => $cuti->id,
                                        'nomor' => '#CUTI-' . sprintf('%04d', $cuti->id),
                                        'nama' => $karyawan->name ?? '-',
                                        'nip' => $karyawan->nip ?? '-',
                                        'station' => $karyawan->station->name ?? 'Pusat',
                                        'role' => $karyawan->role->role_name ?? '-',
                                        'jenis' => $jenisCuti->name_cuti ?? 'Cuti Umum',
                                        'sub_cuti' => $subCuti->nama_sub_cuti ?? '-',
                                        'tanggal_mulai' => \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('l, d F Y'),
                                        'tanggal_selesai' => \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('l, d F Y'),
                                        'total_hari' => ($cuti->total_hari ?? 1) . ' Hari Kerja',
                                        'is_potong' => $isPotong ? 'Ya (Memotong Kuota Saldo Tahunan)' : 'Tidak (Cuti Khusus Non-Tahunan)',
                                        'alasan' => $cuti->alasan_cuti ?: '-',
                                        'dokumen_url' => $hasDokumen ? asset('storage/' . $cuti->dokumen_pendukung) : null,
                                        'spv_status' => $statusTahap1,
                                        'spv_name' => $cuti->approverTahap1->name ?? '-',
                                        'mgr_status' => $statusTahap2,
                                        'mgr_name' => $cuti->approverTahap2->name ?? '-',
                                        'status_akhir' => $statusAkhir,
                                        'catatan_penolakan' => $cuti->catatan_penolakan ?: '-',
                                        'cetak_url' => route('cuti.cetak', $cuti->id)
                                    ]) }})"
                                            class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 flex items-center justify-center transition-colors shadow-sm cursor-pointer"
                                            title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    {{-- Tombol Cetak PDF --}}
                                    @if($statusAkhir === 'approved')
                                        <a href="{{ route('cuti.cetak', $cuti->id) }}" target="_blank"
                                           class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 flex items-center justify-center transition-colors shadow-sm cursor-pointer"
                                           title="Cetak Surat Izin Cuti (PDF)">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </a>
                                    @else
                                        <button type="button" disabled
                                                class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-500 flex items-center justify-center cursor-not-allowed opacity-50"
                                                title="Surat hanya dapat dicetak setelah disetujui penuh">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fa-solid fa-inbox"></i>
                                    </div>
                                    <p class="font-bold text-slate-600 dark:text-slate-300 text-sm">Belum Ada Riwayat Cuti</p>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ditemukan pengajuan cuti yang sesuai dengan kriteria filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($daftarCuti->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $daftarCuti->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{--                       MODAL DETAIL LENGKAP CUTI                          --}}
{{-- ========================================================================= --}}
<div id="modalDetailCuti" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetailCutiModal()"></div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100 dark:border-slate-700">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base border border-sky-100 dark:border-sky-800 shrink-0">
                    <i class="fa-solid fa-file-lines"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base" id="modalCutiNomor">Detail Pengajuan Cuti</h3>
                    <p class="text-xs text-slate-400">Informasi lengkap verifikasi & riwayat permohonan</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailCutiModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="py-4 space-y-4 text-xs">
            {{-- Bagian 1: Data Karyawan --}}
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Profil Karyawan Pemohon</h4>
                <div class="grid grid-cols-2 gap-3 text-slate-700 dark:text-slate-300">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nama Lengkap</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-sm" id="modalCutiNama">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">NIP</span>
                        <span class="font-mono font-semibold" id="modalCutiNip">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Jabatan / Divisi</span>
                        <span class="font-semibold" id="modalCutiRole">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Stasiun Kerja</span>
                        <span class="font-semibold" id="modalCutiStation">-</span>
                    </div>
                </div>
            </div>

            {{-- Bagian 2: Rincian Permohonan Cuti --}}
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Rincian Waktu & Jenis Izin</h4>
                <div class="grid grid-cols-2 gap-3 text-slate-700 dark:text-slate-300">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Jenis Cuti</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400" id="modalCutiJenis">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kategori / Sub-Cuti</span>
                        <span class="font-semibold" id="modalCutiSub">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Tanggal Mulai</span>
                        <span class="font-medium" id="modalCutiMulai">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Tanggal Selesai</span>
                        <span class="font-medium" id="modalCutiSelesai">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Total Durasi Efektif</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" id="modalCutiDurasi">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Skema Saldo Kuota</span>
                        <span class="font-bold" id="modalCutiPotong">-</span>
                    </div>
                </div>

                {{-- Alasan Cuti --}}
                <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-slate-400 block text-[10px] mb-0.5">Alasan / Keperluan Mengajukan Cuti:</span>
                    <p class="text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs" id="modalCutiAlasan">-</p>
                </div>
            </div>

            {{-- Bagian 3: Dokumen Pendukung / Surat Dokter --}}
            <div id="modalCutiDokumenSection" class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60 hidden">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Berkas Lampiran / Surat Dokter</h4>
                <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-paperclip text-sky-600 dark:text-sky-400 text-base"></i>
                        <span class="font-medium text-slate-700 dark:text-slate-200">Dokumen Pendukung Resmi Terlampir</span>
                    </div>
                    <a id="modalCutiDokumenLink" href="#" target="_blank" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Berkas
                    </a>
                </div>
            </div>

            {{-- Bagian 4: Status Persetujuan Berjenjang --}}
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Jejak Verifikasi & Approval</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block">Tahap 1: Supervisor (L3)</span>
                        <div class="font-bold text-xs mt-1" id="modalCutiSpvStatus">-</div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5" id="modalCutiSpvName">-</span>
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block">Tahap 2: Manager (L1/2)</span>
                        <div class="font-bold text-xs mt-1" id="modalCutiMgrStatus">-</div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5" id="modalCutiMgrName">-</span>
                    </div>
                </div>

                {{-- Catatan Penolakan jika ada --}}
                <div id="modalCutiCatatanSection" class="mt-3 pt-3 border-t border-rose-200 dark:border-rose-900/60 hidden">
                    <span class="text-rose-500 font-bold block text-[10px] mb-0.5">Catatan Evaluasi / Penolakan:</span>
                    <p class="text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 p-2.5 rounded-lg border border-rose-200 dark:border-rose-900 text-xs" id="modalCutiCatatan">-</p>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
            <div id="modalCutiCetakContainer">
                <a id="modalCutiCetakBtn" href="#" target="_blank"
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-emerald-600/20 cursor-pointer">
                    <i class="fa-solid fa-print"></i> Cetak Surat Izin (PDF)
                </a>
            </div>
            <button type="button" onclick="closeDetailCutiModal()"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors cursor-pointer ml-auto">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL CUSTOM DATE RANGE --}}
<div id="customDateModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCustomDateModal()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-sm p-6 relative z-10 m-4 border border-slate-100 dark:border-slate-700">
        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base mb-1">Pilih Rentang Tanggal</h3>
        <p class="text-xs text-slate-400 mb-4">Tentukan periode tanggal pengajuan cuti yang ingin ditinjau.</p>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input type="date" id="modalStartDate" value="{{ $filters['start_date'] }}"
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                <input type="date" id="modalEndDate" value="{{ $filters['end_date'] }}"
                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 mt-5">
            <button type="button" onclick="closeCustomDateModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl cursor-pointer">Batal</button>
            <button type="button" onclick="applyCustomDate()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-md cursor-pointer">Terapkan</button>
        </div>
    </div>
</div>

{{-- LIGHTBOX MODAL BUKTI LAMPIRAN --}}
<div id="lightboxModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="closeLightboxModal()"></div>
    <div class="relative z-10 max-w-3xl max-h-[85vh] p-4 m-4 flex flex-col items-center">
        <button type="button" onclick="closeLightboxModal()" class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center shadow-lg hover:scale-105 transition-transform cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="lightboxImg" src="" alt="Lampiran" class="max-w-full max-h-[75vh] rounded-2xl shadow-2xl object-contain border border-slate-700/50 bg-black/40">
        <p id="lightboxCaption" class="text-white text-xs font-semibold mt-2.5 text-center"></p>
    </div>
</div>

@push('scripts')
<script>
    // PRESET PERIODE CEPAT
    function setPeriodePreset(val) {
        document.getElementById('periodeInput').value = val;
        if (val !== 'custom') {
            document.getElementById('startDateInput').value = '';
            document.getElementById('endDateInput').value = '';
        }
        document.getElementById('filterFormCuti').submit();
    }

    // MODAL CUSTOM DATE
    function openCustomDateModal() {
        const modal = document.getElementById('customDateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCustomDateModal() {
        const modal = document.getElementById('customDateModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function applyCustomDate() {
        const start = document.getElementById('modalStartDate').value;
        const end = document.getElementById('modalEndDate').value;
        if (!start || !end) {
            alert('Silakan tentukan kedua tanggal mulai dan selesai!');
            return;
        }
        document.getElementById('periodeInput').value = 'custom';
        document.getElementById('startDateInput').value = start;
        document.getElementById('endDateInput').value = end;
        document.getElementById('filterFormCuti').submit();
    }

    // EXPORT CSV CUTI BERSTANDAR INDUSTRI
    function exportCsvCuti() {
        const form = document.getElementById('filterFormCuti');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        window.location.href = `{{ route('admin.record.cuti.export') }}?${params.toString()}`;
    }

    // LIVE CLIENT-SIDE TABLE SEARCH
    document.getElementById('table-search')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#tableBodyCuti tr.row-item');
        rows.forEach(row => {
            const text = row.getAttribute('data-search') || '';
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });

    // MODAL DETAIL CUTI
    function openDetailCutiModal(data) {
        document.getElementById('modalCutiNomor').textContent = 'Detail Pengajuan ' + data.nomor;
        document.getElementById('modalCutiNama').textContent = data.nama;
        document.getElementById('modalCutiNip').textContent = data.nip;
        document.getElementById('modalCutiRole').textContent = data.role;
        document.getElementById('modalCutiStation').textContent = data.station;
        document.getElementById('modalCutiJenis').textContent = data.jenis;
        document.getElementById('modalCutiSub').textContent = data.sub_cuti;
        document.getElementById('modalCutiMulai').textContent = data.tanggal_mulai;
        document.getElementById('modalCutiSelesai').textContent = data.tanggal_selesai;
        document.getElementById('modalCutiDurasi').textContent = data.total_hari;
        document.getElementById('modalCutiPotong').textContent = data.is_potong;
        document.getElementById('modalCutiAlasan').textContent = data.alasan;

        // Dokumen
        const docSection = document.getElementById('modalCutiDokumenSection');
        if (data.dokumen_url) {
            docSection.classList.remove('hidden');
            document.getElementById('modalCutiDokumenLink').href = data.dokumen_url;
        } else {
            docSection.classList.add('hidden');
        }

        // SPV Status
        const spvEl = document.getElementById('modalCutiSpvStatus');
        if (data.spv_status === 'approved') {
            spvEl.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Disetujui</span>';
        } else if (data.spv_status === 'rejected') {
            spvEl.innerHTML = '<span class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak</span>';
        } else {
            spvEl.innerHTML = '<span class="text-amber-500"><i class="fa-solid fa-clock mr-1"></i> Menunggu Konfirmasi</span>';
        }
        document.getElementById('modalCutiSpvName').textContent = 'Oleh: ' + data.spv_name;

        // Manager Status
        const mgrEl = document.getElementById('modalCutiMgrStatus');
        if (data.mgr_status === 'approved') {
            mgrEl.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Disetujui</span>';
        } else if (data.mgr_status === 'rejected') {
            mgrEl.innerHTML = '<span class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak</span>';
        } else if (data.mgr_status === 'not_required') {
            mgrEl.innerHTML = '<span class="text-slate-400">Tidak Diperlukan</span>';
        } else {
            mgrEl.innerHTML = '<span class="text-amber-500"><i class="fa-solid fa-clock mr-1"></i> Menunggu Konfirmasi</span>';
        }
        document.getElementById('modalCutiMgrName').textContent = 'Oleh: ' + data.mgr_name;

        // Catatan Penolakan
        const catatanSection = document.getElementById('modalCutiCatatanSection');
        if (data.status_akhir === 'rejected' && data.catatan_penolakan !== '-') {
            catatanSection.classList.remove('hidden');
            document.getElementById('modalCutiCatatan').textContent = data.catatan_penolakan;
        } else {
            catatanSection.classList.add('hidden');
        }

        // Tombol Cetak PDF
        const cetakContainer = document.getElementById('modalCutiCetakContainer');
        if (data.status_akhir === 'approved') {
            cetakContainer.classList.remove('hidden');
            document.getElementById('modalCutiCetakBtn').href = data.cetak_url;
        } else {
            cetakContainer.classList.add('hidden');
        }

        const modal = document.getElementById('modalDetailCuti');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailCutiModal() {
        const modal = document.getElementById('modalDetailCuti');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // LIGHTBOX PREVIEW BUKTI
    function previewBukti(url, caption) {
        document.getElementById('lightboxImg').src = url;
        document.getElementById('lightboxCaption').textContent = caption;
        const modal = document.getElementById('lightboxModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeLightboxModal() {
        const modal = document.getElementById('lightboxModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('lightboxImg').src = '';
    }

    // ESC Key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailCutiModal();
            closeCustomDateModal();
            closeLightboxModal();
        }
    });
</script>
@endpush
@endsection

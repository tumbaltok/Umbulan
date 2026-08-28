@extends('layouts.app')

@section('title', 'Record Material Purchase Request (MPR)')

@section('content')
<div class="space-y-6">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-boxes-packing"></i>
                <span>Procurement & Technical Material Management</span>
            </div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-sky-600 dark:text-sky-400"></i>
                Record Material Purchase Request (MPR)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Log audit pengadaan sparepart pipa transmisi, pompa suplai air, material emergency, dan verifikasi persetujuan teknis.
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
            <button type="button" onclick="exportCsvMpr()" 
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-emerald-600/20 flex items-center gap-2 cursor-pointer shrink-0">
                <i class="fa-solid fa-file-excel text-sm"></i>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    {{-- FILTER BAR PANEL COMPREHENSIVE --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <form method="GET" action="{{ route('admin.record.mpr') }}" id="filterFormMpr" class="space-y-4">
            
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
                    <a href="{{ route('admin.record.mpr') }}" 
                       class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700/70 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Reset Filter
                    </a>
                </div>
            </div>

            {{-- State Hidden Inputs --}}
            <input type="hidden" name="periode" id="periodeInput" value="{{ $filters['periode'] }}">
            <input type="hidden" name="start_date" id="startDateInput" value="{{ $filters['start_date'] }}">
            <input type="hidden" name="end_date" id="endDateInput" value="{{ $filters['end_date'] }}">

            {{-- Input Grid Filter (5 Kolom) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- 1. Filter Pemohon --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-user mr-1"></i> Pemohon
                    </label>
                    <select name="user_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Pemohon</option>
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

                {{-- 3. Filter Urgensi / Kategori --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Urgensi / Prioritas
                    </label>
                    <select name="priority" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Urgensi</option>
                        <option value="Emergency" {{ strtolower($filters['priority']) === 'emergency' ? 'selected' : '' }}>Emergency (Transmisi Kritis)</option>
                        <option value="High" {{ strtolower($filters['priority']) === 'high' ? 'selected' : '' }}>High (Prioritas Tinggi)</option>
                        <option value="Normal" {{ strtolower($filters['priority']) === 'normal' ? 'selected' : '' }}>Normal (Rutin Operasional)</option>
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

                {{-- 5. Tombol Terapkan --}}
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer h-[38px] flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-1.5"></i> Terapkan Filter
                    </button>
                </div>
            </div>

            {{-- Live Search Toolbar --}}
            <div class="pt-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="table-search" placeholder="Pencarian cepat nomor MPR, pemohon, sparepart, stasiun, delivery point, atau keperluan..." 
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-700 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all">
                </div>
            </div>

        </form>
    </div>

    {{-- CARDS STATISTIK SUMMARY HRMS / TEKNIK MPR --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Pengadaan MPR --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengadaan MPR</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">
                    {{ number_format($metrics['total']) }}
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Seluruh usulan material</p>
            </div>
            <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center text-xl border border-sky-100 dark:border-sky-800 shrink-0">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
        </div>

        {{-- Card 2: MPR Emergency Aktif --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-rose-100 dark:border-rose-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">MPR Emergency Aktif</p>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ number_format($metrics['emergency']) }}
                </h3>
                <p class="text-[11px] text-rose-500/80 dark:text-rose-400/80 mt-0.5">Penanganan Kritis / Mendesak</p>
            </div>
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/60 text-rose-500 dark:text-rose-400 rounded-2xl flex items-center justify-center text-xl border border-rose-100 dark:border-rose-800 shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        {{-- Card 3: Total Nilai Pengadaan Disetujui (Rp) --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Nilai Pengadaan</p>
                <h3 class="text-xl font-black text-emerald-700 dark:text-emerald-400 mt-1">
                    Rp {{ number_format($metrics['total_nilai_pengadaan'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">{{ $metrics['approved'] }} Pengadaan Disetujui</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl border border-emerald-100 dark:border-emerald-800 shrink-0">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        {{-- Card 4: MPR Selesai / Terbit --}}
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-purple-100 dark:border-purple-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">MPR Selesai / Disetujui</p>
                <h3 class="text-2xl font-black text-purple-700 dark:text-purple-400 mt-1">
                    {{ number_format($metrics['approved']) }}
                </h3>
                <p class="text-[11px] text-purple-600/80 dark:text-purple-400/80 mt-0.5">{{ $metrics['pending'] }} Dalam Proses Review</p>
            </div>
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-xl border border-purple-100 dark:border-purple-800 shrink-0">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
        </div>
    </div>

    {{-- TABEL DATA TRANSAKSI MPR STANDAR INDUSTRI --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                    Daftar Log Riwayat Material Purchase Request ({{ $daftarMpr->total() }} Data Ditemukan)
                </h2>
            </div>
            <span class="text-xs text-slate-400">Halaman {{ $daftarMpr->currentPage() }} dari {{ $daftarMpr->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tableMpr">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-5 py-3.5">Nomor & Tanggal</th>
                        <th class="px-5 py-3.5">Pemohon & Stasiun</th>
                        <th class="px-5 py-3.5">Kategori / Urgensi</th>
                        <th class="px-5 py-3.5">Rincian Material</th>
                        <th class="px-5 py-3.5">Estimasi Pengadaan</th>
                        <th class="px-5 py-3.5">Status Approval</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300 text-xs" id="tableBodyMpr">
                    @forelse($daftarMpr as $mpr)
                        @php
                            $karyawan = $mpr->user;
                            $items = $mpr->items ?? collect();
                            $totalPengadaan = $items->sum(function($i) {
                                return (float)$i->jumlah * (float)$i->estimasi_harga;
                            });
                            $itemsCount = $items->count();
                            $statusTahap1 = $mpr->status_tahap_1;
                            $statusTahap2 = $mpr->status_tahap_2;
                            $statusAkhir = $mpr->status_akhir;
                            $isEmergency = strtolower($mpr->priority ?? '') === 'emergency';
                            $nomorDoc = $mpr->nomor_mpr ?: ('MPR-' . sprintf('%04d', $mpr->id));

                            $itemDetails = $items->map(function($it) {
                                return [
                                    'nama' => $it->nama_barang,
                                    'keterangan' => $it->keterangan_item ?: '-',
                                    'jumlah' => (float)$it->jumlah,
                                    'satuan' => $it->satuan ?: 'pcs',
                                    'estimasi_harga' => (float)$it->estimasi_harga,
                                    'subtotal' => (float)$it->jumlah * (float)$it->estimasi_harga,
                                ];
                            });
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors row-item"
                            data-search="{{ strtolower($nomorDoc) }} {{ strtolower($karyawan->name ?? '') }} {{ strtolower($karyawan->nip ?? '') }} {{ strtolower($karyawan->station->name ?? '') }} {{ strtolower($mpr->delivery_point ?? '') }} {{ strtolower($items->pluck('nama_barang')->implode(' ')) }} {{ strtolower($mpr->keperluan_urgensi ?? '') }}">
                            
                            {{-- 1. Nomor Dokumen & Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-sky-600 dark:text-sky-400">{{ $nomorDoc }}</span>
                                    <span class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[10px]"></i>
                                        {{ $mpr->tanggal_pengajuan ? \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- 2. Pemohon, Lokasi Stasiun & Delivery Point --}}
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
                                            {{ $karyawan->name ?? '-' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-truck-ramp-box text-[9px] text-slate-400"></i>
                                            Kirim: {{ $mpr->delivery_point ?: 'Site Umbulan' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Kategori & Urgensi --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @if($isEmergency)
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 inline-flex items-center gap-1.5 animate-pulse w-fit">
                                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> EMERGENCY
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 inline-flex items-center gap-1.5 w-fit">
                                            <i class="fa-solid fa-circle-info text-[10px]"></i> {{ strtoupper($mpr->priority ?: 'NORMAL') }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-slate-400">Dept: {{ $mpr->department ?: 'Operation' }}</span>
                                </div>
                            </td>

                            {{-- 4. Rincian Material --}}
                            <td class="px-5 py-4 max-w-[220px]">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                        <i class="fa-solid fa-screwdriver-wrench text-sky-500 text-[10px]"></i>
                                        {{ $itemsCount }} Macam Material
                                    </span>
                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                        @foreach($items->take(2) as $it)
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded text-[10px] truncate max-w-[150px]">
                                                {{ $it->nama_barang }} ({{ (float)$it->jumlah }} {{ $it->satuan }})
                                            </span>
                                        @endforeach
                                        @if($itemsCount > 2)
                                            <span class="px-1.5 py-0.5 bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 rounded text-[10px] font-bold">
                                                +{{ $itemsCount - 2 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- 5. Estimasi Pengadaan (IDR) --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400 text-sm">
                                        Rp {{ number_format($totalPengadaan, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Estimasi Total Biaya</span>
                                </div>
                            </td>

                            {{-- 6. Status Approval Berjenjang --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    {{-- Approver 1 / SPV --}}
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="w-4 text-slate-400 font-mono text-[10px]">SPV:</span>
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
                                    </div>

                                    {{-- Approver 2 / Manager --}}
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="w-4 text-slate-400 font-mono text-[10px]">MGR:</span>
                                        @if($statusTahap2 === 'approved')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check text-[10px]"></i> Disetujui
                                            </span>
                                        @elseif($statusTahap2 === 'rejected')
                                            <span class="text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-circle-xmark text-[10px]"></i> Ditolak
                                            </span>
                                        @elseif($statusTahap2 === 'not_required')
                                            <span class="text-slate-400 font-medium">Bypass</span>
                                        @else
                                            <span class="text-amber-500 font-medium flex items-center gap-1">
                                                <i class="fa-solid fa-clock text-[10px]"></i> Menunggu Mgr
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Badge Status Akhir --}}
                                    <div class="mt-0.5">
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

                            {{-- 7. Aksi (Modal Detail & Cetak PDF) --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Detail Rincian Material --}}
                                    <button type="button" onclick="openDetailMprModal({{ json_encode([
                                        'id' => $mpr->id,
                                        'nomor' => $nomorDoc,
                                        'nama' => $karyawan->name ?? '-',
                                        'nip' => $karyawan->nip ?? '-',
                                        'role' => $karyawan->role->role_name ?? '-',
                                        'station' => $karyawan->station->name ?? 'Pusat',
                                        'department' => $mpr->department ?: 'Operation',
                                        'delivery_point' => $mpr->delivery_point ?: 'Site Umbulan',
                                        'priority' => $mpr->priority ?: 'Normal',
                                        'tanggal' => $mpr->tanggal_pengajuan ? \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->translatedFormat('l, d F Y') : '-',
                                        'keperluan' => $mpr->keperluan_urgensi ?: '-',
                                        'total_pengadaan' => 'Rp ' . number_format($totalPengadaan, 0, ',', '.'),
                                        'spv_status' => $statusTahap1,
                                        'spv_name' => $mpr->approverTahap1->name ?? ($mpr->supervisor->name ?? '-'),
                                        'mgr_status' => $statusTahap2,
                                        'mgr_name' => $mpr->approverTahap2->name ?? ($mpr->manager->name ?? '-'),
                                        'status_akhir' => $statusAkhir,
                                        'catatan_penolakan' => $mpr->catatan_penolakan ?: '-',
                                        'items' => $itemDetails,
                                        'cetak_url' => route('mpr.cetak', $mpr->id)
                                    ]) }})"
                                            class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/60 flex items-center justify-center transition-colors shadow-sm cursor-pointer"
                                            title="Rincian Material">
                                        <i class="fa-solid fa-list-check text-xs"></i>
                                    </button>

                                    {{-- Tombol Cetak Dokumen PDF MPR --}}
                                    @if($statusAkhir !== 'rejected')
                                        <a href="{{ route('mpr.cetak', $mpr->id) }}" target="_blank"
                                           class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 flex items-center justify-center transition-colors shadow-sm cursor-pointer"
                                           title="Cetak Formulir MPR Resmi (PDF)">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </a>
                                    @else
                                        <button type="button" disabled
                                                class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-500 flex items-center justify-center cursor-not-allowed opacity-50"
                                                title="Dokumen MPR ditolak tidak dapat dicetak">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fa-solid fa-inbox"></i>
                                    </div>
                                    <p class="font-bold text-slate-600 dark:text-slate-300 text-sm">Belum Ada Riwayat MPR</p>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ditemukan pengadaan Material Purchase Request yang sesuai kriteria filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($daftarMpr->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $daftarMpr->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{--                  MODAL DETAIL RINCIAN MATERIAL MPR LENGKAP               --}}
{{-- ========================================================================= --}}
<div id="modalDetailMpr" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetailMprModal()"></div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100 dark:border-slate-700">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base border border-sky-100 dark:border-sky-800 shrink-0">
                    <i class="fa-solid fa-boxes-packing"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base" id="modalMprNomor">Detail Material Purchase Request</h3>
                    <p class="text-xs text-slate-400">Rincian spesifikasi teknis sparepart & jejak approval pengadaan</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailMprModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="py-4 space-y-4 text-xs">
            {{-- Info Pemohon, Departemen & Titik Pengiriman --}}
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-slate-700 dark:text-slate-300">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Pemohon</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100" id="modalMprNama">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">NIP</span>
                        <span class="font-mono font-semibold" id="modalMprNip">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Departemen</span>
                        <span class="font-semibold" id="modalMprDept">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Delivery Point</span>
                        <span class="font-semibold text-sky-600 dark:text-sky-400" id="modalMprDelivery">-</span>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex flex-col sm:flex-row justify-between gap-3">
                    <div class="flex-1">
                        <span class="text-slate-400 block text-[10px] mb-0.5">Keperluan & Alasan Urgensi Pengadaan:</span>
                        <p class="text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs" id="modalMprKeperluan">-</p>
                    </div>
                    <div class="sm:w-44 shrink-0">
                        <span class="text-slate-400 block text-[10px] mb-0.5">Prioritas / Urgensi:</span>
                        <div id="modalMprPriorityBadge" class="mt-1"></div>
                    </div>
                </div>
            </div>

            {{-- Tabel Multi-Item Material --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Spesifikasi Material / Sparepart</h4>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" id="modalMprTotalPengadaan">-</span>
                </div>
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
                                <th class="px-3 py-2.5">No</th>
                                <th class="px-3 py-2.5">Nama Sparepart / Barang</th>
                                <th class="px-3 py-2.5">Keterangan / Spesifikasi</th>
                                <th class="px-3 py-2.5 text-center">Qty / Satuan</th>
                                <th class="px-3 py-2.5 text-right">Estimasi / Unit</th>
                                <th class="px-3 py-2.5 text-right">Subtotal Estimasi</th>
                            </tr>
                        </thead>
                        <tbody id="modalMprItemsBody" class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Jejak Approval --}}
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Jejak Verifikasi & Approval</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block">Supervisor (Verifikasi Teknis & Lapangan)</span>
                        <div class="font-bold text-xs mt-1" id="modalMprSpvStatus">-</div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5" id="modalMprSpvName">-</span>
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block">Manager (Persetujuan Anggaran Pengadaan)</span>
                        <div class="font-bold text-xs mt-1" id="modalMprMgrStatus">-</div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5" id="modalMprMgrName">-</span>
                    </div>
                </div>

                {{-- Catatan Penolakan --}}
                <div id="modalMprCatatanSection" class="mt-3 pt-3 border-t border-rose-200 dark:border-rose-900/60 hidden">
                    <span class="text-rose-500 font-bold block text-[10px] mb-0.5">Catatan Evaluasi / Penolakan:</span>
                    <p class="text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 p-2.5 rounded-lg border border-rose-200 dark:border-rose-900 text-xs" id="modalMprCatatan">-</p>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
            <div id="modalMprCetakContainer">
                <a id="modalMprCetakBtn" href="#" target="_blank"
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-emerald-600/20 cursor-pointer">
                    <i class="fa-solid fa-print"></i> Cetak Formulir MPR (PDF)
                </a>
            </div>
            <button type="button" onclick="closeDetailMprModal()"
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
        <p class="text-xs text-slate-400 mb-4">Tentukan periode tanggal pengajuan MPR yang ingin ditinjau.</p>
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

@push('scripts')
<script>
    // PRESET PERIODE CEPAT
    function setPeriodePreset(val) {
        document.getElementById('periodeInput').value = val;
        if (val !== 'custom') {
            document.getElementById('startDateInput').value = '';
            document.getElementById('endDateInput').value = '';
        }
        document.getElementById('filterFormMpr').submit();
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
        document.getElementById('filterFormMpr').submit();
    }

    // EXPORT CSV MPR BERSTANDAR INDUSTRI
    function exportCsvMpr() {
        const form = document.getElementById('filterFormMpr');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        window.location.href = `{{ route('admin.record.mpr.export') }}?${params.toString()}`;
    }

    // LIVE TABLE SEARCH
    document.getElementById('table-search')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#tableBodyMpr tr.row-item');
        rows.forEach(row => {
            const text = row.getAttribute('data-search') || '';
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });

    // MODAL DETAIL MPR
    function openDetailMprModal(data) {
        document.getElementById('modalMprNomor').textContent = 'Detail ' + data.nomor;
        document.getElementById('modalMprNama').textContent = data.nama;
        document.getElementById('modalMprNip').textContent = data.nip;
        document.getElementById('modalMprDept').textContent = data.department;
        document.getElementById('modalMprDelivery').textContent = data.delivery_point;
        document.getElementById('modalMprKeperluan').textContent = data.keperluan;
        document.getElementById('modalMprTotalPengadaan').textContent = 'Total: ' + data.total_pengadaan;

        // Priority Badge
        const pBadge = document.getElementById('modalMprPriorityBadge');
        if (data.priority.toLowerCase() === 'emergency') {
            pBadge.innerHTML = '<span class="px-2.5 py-1 rounded-lg text-xs font-black bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 inline-flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> EMERGENCY TRANSMISI</span>';
        } else {
            pBadge.innerHTML = `<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 inline-flex items-center gap-1.5">${data.priority.toUpperCase()}</span>`;
        }

        // Render Material Items
        const body = document.getElementById('modalMprItemsBody');
        let html = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach((it, idx) => {
                html += `
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/40">
                        <td class="px-3 py-2 text-center text-slate-400">${idx + 1}</td>
                        <td class="px-3 py-2 font-semibold text-slate-800 dark:text-slate-100">${it.nama}</td>
                        <td class="px-3 py-2 text-slate-500 dark:text-slate-400">${it.keterangan}</td>
                        <td class="px-3 py-2 text-center font-bold">${it.jumlah} ${it.satuan}</td>
                        <td class="px-3 py-2 text-right font-mono">Rp ${Number(it.estimasi_harga).toLocaleString('id-ID')}</td>
                        <td class="px-3 py-2 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp ${Number(it.subtotal).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });
        } else {
            html = `<tr><td colspan="6" class="px-3 py-4 text-center text-slate-400 italic">Tidak ada item material terlampir.</td></tr>`;
        }
        body.innerHTML = html;

        // SPV
        const spvEl = document.getElementById('modalMprSpvStatus');
        if (data.spv_status === 'approved') {
            spvEl.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Terverifikasi</span>';
        } else if (data.spv_status === 'rejected') {
            spvEl.innerHTML = '<span class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak</span>';
        } else {
            spvEl.innerHTML = '<span class="text-amber-500"><i class="fa-solid fa-clock mr-1"></i> Menunggu Verifikasi</span>';
        }
        document.getElementById('modalMprSpvName').textContent = 'Oleh: ' + data.spv_name;

        // Mgr
        const mgrEl = document.getElementById('modalMprMgrStatus');
        if (data.mgr_status === 'approved') {
            mgrEl.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Disetujui</span>';
        } else if (data.mgr_status === 'rejected') {
            mgrEl.innerHTML = '<span class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak</span>';
        } else if (data.mgr_status === 'not_required') {
            mgrEl.innerHTML = '<span class="text-slate-400">Tidak Diperlukan</span>';
        } else {
            mgrEl.innerHTML = '<span class="text-amber-500"><i class="fa-solid fa-clock mr-1"></i> Menunggu Persetujuan</span>';
        }
        document.getElementById('modalMprMgrName').textContent = 'Oleh: ' + data.mgr_name;

        // Catatan Penolakan
        const catSection = document.getElementById('modalMprCatatanSection');
        if (data.status_akhir === 'rejected' && data.catatan_penolakan !== '-') {
            catSection.classList.remove('hidden');
            document.getElementById('modalMprCatatan').textContent = data.catatan_penolakan;
        } else {
            catSection.classList.add('hidden');
        }

        // Cetak
        const cetakContainer = document.getElementById('modalMprCetakContainer');
        if (data.status_akhir !== 'rejected') {
            cetakContainer.classList.remove('hidden');
            document.getElementById('modalMprCetakBtn').href = data.cetak_url;
        } else {
            cetakContainer.classList.add('hidden');
        }

        const modal = document.getElementById('modalDetailMpr');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailMprModal() {
        const modal = document.getElementById('modalDetailMpr');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailMprModal();
            closeCustomDateModal();
        }
    });
</script>
@endpush
@endsection
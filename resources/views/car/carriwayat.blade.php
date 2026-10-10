@extends('layouts.app')
@section('title', 'Riwayat Pengajuan CAR')

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
                <span class="text-sky-600 dark:text-sky-400 font-bold">Riwayat CAR</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Riwayat Pengajuan CAR (Cash Advance Request)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Pemantauan status verifikasi berkas uang muka material, ongkos kirim, dan pencairan dana kasbon Anda
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('car.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white text-xs font-bold transition-all shadow-md shadow-sky-600/25 hover:shadow-lg hover:shadow-sky-600/35 active:scale-95 group cursor-pointer">
                <i class="fa-solid fa-plus text-xs text-white group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="text-white">Ajukan CAR Baru</span>
            </a>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/80 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-sm text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-200 uppercase tracking-wider">Berhasil</h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/80 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-sm text-rose-600 dark:text-rose-400"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200 uppercase tracking-wider">Pemberitahuan</h4>
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- STATISTIC SUMMARY CARDS --}}
    @php
        $totalCar = $riwayatCar->count();
        $totalCarApproved = $riwayatCar->filter(fn($c) => trim(strtolower($c->status_akhir)) === 'approved')->count();
        $totalCarPending = $riwayatCar->filter(fn($c) => trim(strtolower($c->status_akhir)) === 'pending')->count();
        $totalCarRejected = $riwayatCar->filter(fn($c) => trim(strtolower($c->status_akhir)) === 'rejected')->count();
        $totalDanaSemua = $riwayatCar->sum(fn($c) => $c->details->sum('total_harga'));
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        {{-- Total Pengajuan CAR --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pengajuan</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalCar }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt text-base text-sky-600 dark:text-sky-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-folder-closed text-sky-500"></i>
                <span>Seluruh riwayat CAR</span>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disetujui</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalCarApproved }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                <span>Siap dicairkan / siap cetak</span>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu</p>
                    <h3 class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-1">{{ $totalCarPending }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock text-base text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                <span>Menunggu persetujuan atasan</span>
            </div>
        </div>

        {{-- Akumulasi Total Dana --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Akumulasi Biaya</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 mt-1 truncate max-w-[150px]">
                        Rp {{ number_format($totalDanaSemua, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-100 dark:border-cyan-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-wallet text-base text-cyan-600 dark:text-cyan-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-coins text-cyan-500"></i>
                <span>Termasuk total ongkos kirim</span>
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
                        <span>Daftar Pengajuan Anggaran CAR</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                            {{ $totalCar }} Dokumen
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Status verifikasi kasbon dan pratinjau nota pembelian barang
                    </p>
                </div>
            </div>

            {{-- LIVE SEARCH & FILTER CHIPS --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="text" id="liveSearchCar" placeholder="Cari No. CAR / barang..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                </div>
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border border-slate-200/70 dark:border-slate-700/60 text-xs">
                    <button type="button" onclick="filterStatusCar('all')" class="chip-filter-car active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer" data-status="all">Semua</button>
                    <button type="button" onclick="filterStatusCar('pending')" class="chip-filter-car px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="pending">Menunggu</button>
                    <button type="button" onclick="filterStatusCar('approved')" class="chip-filter-car px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="approved">Disetujui</button>
                    <button type="button" onclick="filterStatusCar('rejected')" class="chip-filter-car px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="rejected">Ditolak</button>
                </div>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelRiwayatCar">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4" style="width: 20%;">No. CAR / Tanggal</th>
                        <th class="px-6 py-4" style="width: 42%;">Rincian Barang & Nota</th>
                        <th class="px-6 py-4" style="width: 18%;">Total Biaya</th>
                        <th class="px-6 py-4 text-center" style="width: 10%;">Status Akhir</th>
                        <th class="px-6 py-4 text-center" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatCar as $car)
                        @php
                            $statusNormalized = 'pending';
                            $statusAkhirRaw = trim(strtolower($car->status_akhir));
                            if ($statusAkhirRaw === 'approved') {
                                $statusNormalized = 'approved';
                            } elseif ($statusAkhirRaw === 'rejected') {
                                $statusNormalized = 'rejected';
                            }
                        @endphp
                        <tr class="row-car hover:bg-sky-50/40 dark:hover:bg-slate-700/40 transition-colors group" data-status="{{ $statusNormalized }}">
                            {{-- NO CAR & TANGGAL --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fa-solid fa-receipt text-xs text-sky-600 dark:text-sky-400"></i>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-100 block text-xs target-search">
                                            {{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 mt-0.5">
                                            <i class="fa-regular fa-calendar text-[10px]"></i>
                                            {{ $car->tanggal_pengajuan ? \Carbon\Carbon::parse($car->tanggal_pengajuan)->format('d M Y') : $car->created_at->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- RINCIAN BARANG & NOTA --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    @foreach($car->details as $detail)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70 dark:bg-slate-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 target-search">
                                            <div class="space-y-0.5">
                                                <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">
                                                    {{ $detail->nama_barang }}
                                                </span>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1.5">
                                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Rp {{ number_format($detail->estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                                    <span class="text-slate-400">x {{ $detail->jumlah }} {{ $detail->satuan }}</span>
                                                    @if(($detail->ongkir ?? 0) > 0)
                                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                                        <span class="text-amber-600 dark:text-amber-400 font-semibold">+ Ongkir: Rp {{ number_format($detail->ongkir, 0, ',', '.') }}</span>
                                                    @endif
                                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                                    <span class="text-slate-400">Total:</span>
                                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</span>
                                                </div>
                                            </div>

                                            @if($detail->dokumen_nota_or_proposal)
                                                <button type="button"
                                                        data-url="{{ asset('storage/' . $detail->dokumen_nota_or_proposal) }}"
                                                        onclick="bukaPratinjauLampiran(this.dataset.url)"
                                                        class="self-start sm:self-auto inline-flex items-center gap-1.5 text-[11px] bg-white dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-slate-700 text-sky-600 dark:text-sky-400 font-bold px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 shadow-2xs transition-all cursor-pointer">
                                                    <i class="fa-solid fa-file-invoice text-xs text-sky-600 dark:text-sky-400"></i>
                                                    <span>Nota</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            {{-- TOTAL BIAYA --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="font-black text-emerald-600 dark:text-emerald-400 text-xs">
                                    Rp {{ number_format($car->details->sum('total_harga'), 0, ',', '.') }}
                                </div>
                                @php $totalOngkirCar = $car->details->sum('ongkir'); @endphp
                                @if($totalOngkirCar > 0)
                                    <span class="inline-block text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-1 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded border border-amber-200/60 dark:border-amber-800/60">
                                        Termasuk Ongkir: Rp {{ number_format($totalOngkirCar, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            {{-- STATUS AKHIR --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($statusAkhirRaw === 'approved')
                                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($statusAkhirRaw === 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-3 py-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                        @if(!empty($car->catatan_penolakan))
                                            <span class="block text-[11px] text-rose-600 dark:text-rose-400 font-medium italic max-w-[180px] whitespace-normal mx-auto bg-rose-50/80 dark:bg-rose-950/50 px-2.5 py-1 rounded-lg border border-rose-100 dark:border-rose-800">
                                                "{{ $car->catatan_penolakan }}"
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($statusAkhirRaw === 'approved')
                                    <button type="button"
                                            onclick="bukaPratinjauCetak('{{ route('car.print', $car->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer">
                                        <i class="fa-solid fa-print text-xs text-white"></i>
                                        <span class="text-white">Cetak PDF</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 dark:text-slate-500 text-xs italic">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Belum Ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRowPlaceholderCar">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-solid fa-receipt text-slate-400"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan CAR</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                    Anda belum pernah mengajukan dana muka operasional (CAR). Klik tombol di bawah untuk membuat permohonan baru.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('car.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-plus text-white"></i>
                                        <span class="text-white">Ajukan CAR Sekarang</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noSearchResultRowCar" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass text-slate-300 dark:text-slate-600 text-2xl mb-2 block"></i>
                            Tidak ada pengajuan CAR yang cocok dengan kata kunci pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Search & Status Filtering
    function filterStatusCar(status) {
        document.querySelectorAll('.chip-filter-car').forEach(btn => {
            if (btn.getAttribute('data-status') === status) {
                btn.className = "chip-filter-car active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer";
            } else {
                btn.className = "chip-filter-car px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer";
            }
        });
        applyCarFilters();
    }

    const searchInputCar = document.getElementById('liveSearchCar');
    if (searchInputCar) {
        searchInputCar.addEventListener('input', applyCarFilters);
    }

    function applyCarFilters() {
        const query = (document.getElementById('liveSearchCar')?.value || '').toLowerCase().trim();
        const activeChip = document.querySelector('.chip-filter-car.active');
        const selectedStatus = activeChip ? activeChip.getAttribute('data-status') : 'all';

        const rows = document.querySelectorAll('.row-car');
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

        const noResultRow = document.getElementById('noSearchResultRowCar');
        if (noResultRow) {
            noResultRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }

</script>
@endpush

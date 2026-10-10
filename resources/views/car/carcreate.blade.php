@extends('layouts.app')
@section('title', 'Ajukan CAR Baru')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- BREADCRUMB & TOP HEADER BAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-sky-600 dark:hover:text-sky-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-house text-[11px] text-slate-400 dark:text-slate-500"></i>
                    <span>Dashboard</span>
                </a>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-slate-600 dark:text-slate-400">Keuangan & Kas Bon</span>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-sky-600 dark:text-sky-400 font-bold">Form Pengajuan CAR</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 dark:shadow-sky-500/30 shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Pengajuan CAR (Cash Advance Request)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Formulir pengajuan dana muka operasional, talangan pembelian material, dan kebutuhan mendesak PT META Adhya Tirta Umbulan
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('car.riwayat') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-sky-300 dark:hover:border-sky-500/50 text-slate-700 dark:text-slate-200 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50/50 dark:hover:bg-slate-700/60 text-xs font-bold transition-all shadow-xs hover:shadow-sm group">
                <i class="fa-solid fa-clock-rotate-left text-sky-500 dark:text-sky-400 group-hover:rotate-[-30deg] transition-transform"></i>
                <span>Riwayat Pengajuan</span>
            </a>
        </div>
    </div>

    {{-- MAIN 2-COLUMN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: FORM CONTAINER (8 COLS) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- ERROR BANNER SISTEM / SERVER --}}
            @if(isset($errors) && $errors->any())
                <div class="p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-start gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/80 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200 uppercase tracking-wider">Perhatian: Formulir Belum Valid</h4>
                        <ul class="text-xs text-rose-700 dark:text-rose-300 mt-1 space-y-1 font-medium list-disc pl-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- FORM CARD UTAMA --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 overflow-hidden transition-all">

                {{-- CARD HEADER --}}
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50/80 via-white to-sky-50/30 dark:from-slate-800/90 dark:via-slate-800 dark:to-slate-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center">
                            <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                Formulir Dana Muka Operasional (CAR)
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Rincikan barang, ongkos kirim per vendor, dan rekening pencairan secara presisi
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-200/70 dark:border-sky-500/30">
                            <i class="fa-solid fa-building-columns text-[10px] text-sky-600 dark:text-sky-400"></i>
                            <span>Pencairan Kas & Transfer</span>
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <form id="formCar" action="{{ route('car.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- SEKSI 1: METADATA & REKENING PENCAIRAN --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">1</span>
                                    <span>Informasi Dokumen & Rekening Pencairan</span>
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- NOMOR CAR (OTOMATIS) --}}
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Nomor CAR (Resmi Otomatis)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600 dark:text-sky-400">
                                            <i class="fa-solid fa-hashtag text-sm"></i>
                                        </div>
                                        <input type="text"
                                               name="nomor_car"
                                               id="nomor_car"
                                               value="{{ $nomorCar }}"
                                               readonly
                                               class="w-full pl-10 pr-10 py-3 bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-bold focus:outline-none cursor-not-allowed">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Format atomik sistem: [No] / META / PAS / CAR / [Bulan] / [Tahun]</p>
                                </div>

                                {{-- TANGGAL PENGAJUAN --}}
                                <div>
                                    <label for="tanggal_pengajuan" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Tanggal Pengajuan <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-regular fa-calendar-check text-sm"></i>
                                        </div>
                                        <input type="date"
                                               name="tanggal_pengajuan"
                                               id="tanggal_pengajuan"
                                               value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}"
                                               required
                                               class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer">
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Tanggal saat kebutuhan dana diajukan</p>
                                </div>
                            </div>

                            {{-- REKENING PENERIMA DANA (RECEIVING ACCOUNT) --}}
                            <div class="pt-1">
                                <label for="receiving_account" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Rekening Penerima Dana (Receiving Account) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600 dark:text-sky-400">
                                        <i class="fa-solid fa-credit-card text-sm"></i>
                                    </div>
                                    <input type="text"
                                           name="receiving_account"
                                           id="receiving_account"
                                           value="{{ old('receiving_account') }}"
                                           required
                                           class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all {{ $errors->has('receiving_account') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                           placeholder="Contoh: BCA 1234567890 a.n. {{ auth()->user()->name }}">
                                </div>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Wajib sertakan: [Nama Bank] - [Nomor Rekening] - a.n. [Nama Pemilik Rekening]</p>

                                {{-- BANK PRESET CHIPS --}}
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] pt-2">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-building-columns text-sky-500 text-[10px]"></i>
                                        <span>Pilihan Bank Cepat:</span>
                                    </span>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="BCA">BCA</button>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="Mandiri">Mandiri</button>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="BRI">BRI</button>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="BNI">BNI</button>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="BSI">BSI</button>
                                    <button type="button" class="btn-bank-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-bank="Bank Jatim">Bank Jatim</button>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 2: ALASAN PEMBELIAN & JUSTIFIKASI OPERASIONAL --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">2</span>
                                    <span>Alasan Pembelian & Justifikasi Operasional</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium" id="char-counter-alasan">0 / 1000 karakter</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- ALASAN PEMBELIAN --}}
                                <div>
                                    <label for="alasan_pembelian" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Alasan Pembelian / Urgensi Operasional <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="alasan_pembelian"
                                              id="alasan_pembelian"
                                              rows="4"
                                              maxlength="1000"
                                              required
                                              class="w-full px-4 py-3 bg-white dark:bg-slate-900 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed {{ $errors->has('alasan_pembelian') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                              placeholder="Jelaskan kebutuhan pengajuan dana talangan / kas bon secara detail (contoh: Pembelian material mendesak di toko lokal karena stok habis di site)...">{{ old('alasan_pembelian') }}</textarea>

                                    {{-- QUICK TEMPLATE ALASAN --}}
                                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                            <i class="fa-solid fa-wand-magic-sparkles text-sky-500 text-[10px]"></i>
                                            <span>Template:</span>
                                        </span>
                                        <button type="button" class="btn-quick-car-note inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="Kebutuhan pembelian material mendesak di toko lokal guna perbaikan operasional site.">
                                            <i class="fa-solid fa-boxes-packing text-sky-500 text-[10px]"></i>
                                            <span>Material Lapangan</span>
                                        </button>
                                        <button type="button" class="btn-quick-car-note inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="Talangan biaya perbaikan darurat dan penggantian sparepart pompa di instalasi.">
                                            <i class="fa-solid fa-wrench text-amber-500 text-[10px]"></i>
                                            <span>Perbaikan Darurat</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- NOTE & EXPLANATION (OPSIONAL) --}}
                                <div>
                                    <label for="note_explanation" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Catatan Tambahan Teknis (Note & Explanation) <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional)</span>
                                    </label>
                                    <textarea name="note_explanation"
                                              id="note_explanation"
                                              rows="4"
                                              maxlength="1000"
                                              class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed"
                                              placeholder="Catatan tambahan seperti sisa kas sebelumnya, estimasi tanggal penyelesaian pekerjaan, atau pelaporan SPJ...">{{ old('note_explanation') }}</textarea>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Dapat dikosongkan jika seluruh rincian sudah tertulis di alasan pembelian.</p>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 3: RINCIAN BARANG & ONGKIR PER VENDOR (REQUESTED ITEMS) --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">3</span>
                                        <span>Rincian Barang & Ongkos Kirim per Vendor</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Sertakan ongkir spesifik per item jika berbelanja di vendor / ekspedisi berbeda</p>
                                </div>

                                <button type="button"
                                        id="btn-tambah-item"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 hover:shadow-lg hover:shadow-sky-600/30 transition-all cursor-pointer active:scale-95">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Tambah Baris Item</span>
                                </button>
                            </div>

                            {{-- PRESET CHIPS TEMPLATE MATERIAL UMUM CAR --}}
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-cart-plus text-sky-500 text-[10px]"></i>
                                    <span>Template Cepat Item:</span>
                                </span>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Pipa PVC 2 Inch AW & Sambungan" data-satuan="Batang">Pipa PVC & Fitting</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Baut, Mur & Rubber Seal Flange" data-satuan="Set">Baut, Mur & Gasket</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Semen & Pasir Pasang Perbaikan Lantai" data-satuan="Sak">Bahan Bangunan Site</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Kabel Listrik NYM & MCB Schneider" data-satuan="Meter">Kelistrikan & MCB</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Perlengkapan Kebersihan & Chemical Cuci" data-satuan="Pcs">Peralatan Kebersihan</button>
                            </div>

                            {{-- CONTAINER DAFTAR ITEM DENGAN KARTU MODERN --}}
                            <div id="container-item" class="space-y-4">
                                {{-- BARIS DEFAULT PERTAMA (INDEX 0) --}}
                                <div class="baris-item bg-slate-50/70 dark:bg-slate-900/60 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 relative shadow-2xs space-y-3 transition-all">
                                    <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="label-nomor-item px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase tracking-wider bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30">
                                                Item #1
                                            </span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold item-title-display">Barang Baru</span>
                                        </div>

                                        <button type="button" class="btn-hapus-item text-slate-300 dark:text-slate-600 cursor-not-allowed p-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" disabled title="Hapus Item">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                        {{-- NAMA BARANG --}}
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                Nama Barang / Material <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-sky-600/70 dark:text-sky-400">
                                                    <i class="fa-solid fa-box text-xs"></i>
                                                </div>
                                                <input type="text"
                                                       name="items[0][nama_barang]"
                                                       required
                                                       class="input-nama-barang w-full pl-9 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="Contoh: Pipa PVC 2 Inch AW">
                                            </div>
                                        </div>

                                        {{-- QTY, SATUAN, HARGA, ONGKIR --}}
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:col-span-5">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                    Qty <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number"
                                                       name="items[0][jumlah]"
                                                       required
                                                       min="1"
                                                       value="1"
                                                       class="input-jumlah w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="1">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                    Satuan <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="text"
                                                       name="items[0][satuan]"
                                                       required
                                                       list="satuan-list"
                                                       class="input-satuan w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold text-center focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="Pcs">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                    Harga (Rp) <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number"
                                                       name="items[0][estimasi_harga]"
                                                       required
                                                       min="0"
                                                       class="input-harga w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="0">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-amber-700 dark:text-amber-400 mb-1.5 flex items-center gap-1">
                                                    <i class="fa-solid fa-truck-fast text-[10px]"></i>
                                                    <span>Ongkir</span>
                                                </label>
                                                <input type="number"
                                                       name="items[0][ongkir]"
                                                       min="0"
                                                       value="0"
                                                       class="input-ongkir w-full px-2.5 py-2.5 bg-amber-50/30 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-700/80 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
                                                       placeholder="0">
                                            </div>
                                        </div>

                                        {{-- SUBTOTAL BARIS --}}
                                        <div class="md:col-span-3 flex flex-col justify-center items-start md:items-end pt-1 md:pt-0">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Subtotal Baris</span>
                                            <span class="label-subtotal text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rp 0</span>
                                        </div>
                                    </div>

                                    {{-- ATTACHMENT DROPZONE KHUSUS ITEM INI --}}
                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <div class="w-full sm:w-2/3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                                Lampiran Nota / Proposal / Foto Barang <span class="font-normal text-slate-400">(Opsional untuk item ini)</span>
                                            </label>
                                            <input type="file"
                                                   name="items[0][dokumen_pendukung]"
                                                   accept=".pdf,.jpg,.jpeg,.png"
                                                   class="input-file-dokumen w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 dark:file:bg-sky-950/50 file:text-sky-700 dark:file:text-sky-300 hover:file:bg-sky-100 dark:hover:file:bg-sky-900/60 cursor-pointer">
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Format: PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                                        </div>

                                        {{-- PREVIEW CONTAINER --}}
                                        <div class="preview-container hidden p-2 bg-white dark:bg-slate-900 border border-sky-200 dark:border-sky-800 rounded-xl w-full sm:w-1/3 shadow-2xs">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="p-1 bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file">File</span>
                                                <span class="text-[11px] text-slate-600 dark:text-slate-300 truncate font-medium nama-file-preview">nama_file.jpg</span>
                                            </div>
                                            <div class="area-preview-visual flex justify-start items-center"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- DATALIST SATUAN STANDAR --}}
                            <datalist id="satuan-list">
                                <option value="PCS">
                                <option value="Unit">
                                <option value="Box">
                                <option value="Lot">
                                <option value="Meter">
                                <option value="Batang">
                                <option value="Set">
                                <option value="Roll">
                                <option value="Kg">
                                <option value="Liter">
                                <option value="Tabung">
                                <option value="Paket">
                                <option value="Sak">
                            </datalist>

                            {{-- RINGKASAN AKUMULASI GRAND TOTAL BOX (HARMONIOUS LIGHT & DARK) --}}
                            <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-50/90 via-sky-50/40 to-emerald-50/40 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-slate-200/90 dark:border-slate-700/80 shadow-xs dark:shadow-xl dark:shadow-slate-900/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-receipt text-base"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">Estimasi Grand Total CAR</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium" id="label-jumlah-item">1 Macam Item Terdaftar</span>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span id="grand_total" class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight block">Rp 0</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5" id="label-rincian-total">Barang: Rp 0 + Total Ongkir: Rp 0</span>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 order-2 sm:order-1">
                                <i class="fa-solid fa-lock text-[11px] text-slate-500 dark:text-slate-400"></i>
                                <span>Data terlindungi dan tersinkronisasi</span>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                                <a href="{{ route('car.riwayat') }}"
                                   class="flex-1 sm:flex-none px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white hover:border-slate-400 dark:hover:border-slate-600 text-xs sm:text-sm font-bold transition-all text-center shadow-xs">
                                    Batal
                                </a>
                                <button type="submit"
                                        id="btn-submit-car"
                                        class="flex-1 sm:flex-none px-7 py-3 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 via-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 dark:from-sky-500 dark:via-sky-500 dark:to-cyan-500 dark:hover:from-sky-400 dark:hover:to-cyan-400 text-white text-xs sm:text-sm font-extrabold shadow-md shadow-sky-600/20 hover:shadow-lg hover:shadow-sky-600/30 dark:shadow-sky-500/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Kirim Pengajuan CAR</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: SIDEBAR WIDGETS (4 COLS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- WIDGET 1: STATUS KAS BON (CAR) TAHUN INI --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-6 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 relative overflow-hidden transition-all">
                {{-- Ambient Top Glow --}}
                <div class="absolute -top-12 -right-12 w-36 h-36 rounded-full bg-gradient-to-br from-sky-400/20 to-cyan-400/20 blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-500/15 px-2.5 py-1 rounded-full border border-sky-200/70 dark:border-sky-500/30">
                        STATUS KAS BON {{ date('Y') }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Tahun Aktif</span>
                </div>

                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-4xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                            {{ $totalCarUser ?? 0 }}
                        </span>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400 ml-1">Pengajuan CAR</span>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-600/25 dark:shadow-sky-500/30">
                        <i class="fa-solid fa-wallet text-base text-white"></i>
                    </div>
                </div>

                {{-- MINI BREAKDOWN STATS --}}
                <div class="mt-5 grid grid-cols-3 gap-2.5 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Total</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5 block">{{ $totalCarUser ?? 0 }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Disetujui</span>
                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $carApprovedCount ?? 0 }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Pending</span>
                        <span class="text-xs font-black text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $carPendingCount ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- WIDGET 2: RINGKASAN PENGAJUAN REAL-TIME (LIVE SUMMARY) --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-6 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-receipt text-xs text-sky-600 dark:text-sky-400"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Ringkasan Pengajuan CAR (Live)
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">No. CAR</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400 truncate max-w-[170px]" id="preview-no-car">{{ $nomorCar }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Pemohon</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[160px]">{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Rekening Tujuan</span>
                        <span id="preview-rekening" class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">-</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Macam Item</span>
                        <span id="preview-total-items" class="font-bold text-slate-800 dark:text-slate-100">1 Item</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Total Belanja Barang</span>
                        <span id="preview-total-barang" class="font-bold text-slate-800 dark:text-slate-200">Rp 0</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Total Ongkir Vendor</span>
                        <span id="preview-total-ongkir" class="font-bold text-amber-600 dark:text-amber-400">Rp 0</span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500 dark:text-slate-400">Grand Total CAR</span>
                        <span id="preview-grand-total" class="font-black text-emerald-600 dark:text-emerald-400 text-sm">Rp 0</span>
                    </div>
                </div>
            </div>

            {{-- WIDGET 3: KETENTUAN & SOP KAS BON (POLICY & GUIDELINES) --}}
            <div class="bg-gradient-to-br from-slate-50 to-sky-50/50 dark:from-slate-800/80 dark:to-slate-900/60 rounded-3xl border border-sky-200/80 dark:border-slate-700/70 p-6 space-y-3 shadow-sm">
                <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-circle-question text-xs text-sky-600 dark:text-sky-400"></i>
                    </div>
                    <span>Ketentuan Dana Muka (CAR SOP)</span>
                </h4>

                <ul class="space-y-2.5 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Nota & Bukti Pengeluaran:</strong> Seluruh pengeluaran wajib disertai nota/struk asli bermaterai atau stempel toko resmi saat pertanggungjawaban.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Ongkir per Toko:</strong> Cantumkan ongkos kirim pada masing-masing item jika membeli dari toko/ekspedisi yang terpisah.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Persetujuan Bertingkat:</strong> Verifikasi persetujuan instan dikirim ke Atasan & Finance via WhatsApp Gateway.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Batas Pelaporan (SPJ):</strong> Laporan pertanggungjawaban sisa dana muka wajib diselesaikan maksimal 3 hari kerja setelah transaksi.</span>
                    </li>
                </ul>
            </div>

            {{-- WIDGET 4: PENGAJUAN CAR TERAKHIR ANDA --}}
            @if(isset($recentCars) && $recentCars->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-xs text-slate-600 dark:text-slate-400"></i>
                            </div>
                            <span>Pengajuan CAR Terakhir</span>
                        </h4>
                        <a href="{{ route('car.riwayat') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300">
                            Semua
                        </a>
                    </div>

                    <div class="space-y-2">
                        @foreach($recentCars as $rc)
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                <div class="min-w-0 flex-1 pr-2">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $rc->nomor_car ?? ('CAR #' . $rc->id) }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($rc->tanggal_pengajuan ?? $rc->created_at)->format('d M Y') }} • Rp {{ number_format($rc->details->sum('total_harga'), 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    @if($rc->status_akhir === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30">
                                            Disetujui
                                        </span>
                                    @elseif($rc->status_akhir === 'rejected')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/60 dark:border-rose-500/30">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/60 dark:border-amber-500/30">
                                            Menunggu
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'PENGAJUAN CAR BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'Buka Riwayat CAR',
            confirmButtonColor: '#0284c7',
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            customClass: {
                popup: `rounded-3xl border ${isDark ? 'border-slate-700 shadow-2xl' : 'border-slate-200 shadow-xl'}`,
                confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 cursor-pointer'
            }
        }).then(() => {
            window.location.href = "{{ route('car.riwayat') }}";
        });
    });
</script>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const isDark = () => document.documentElement.classList.contains('dark');

        function getSwalTheme(custom = {}) {
            const dark = isDark();
            return {
                background: dark ? '#1e293b' : '#ffffff',
                color: dark ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#0284c7',
                cancelButtonColor: dark ? '#475569' : '#94a3b8',
                customClass: {
                    popup: `rounded-3xl border ${dark ? 'border-slate-700 shadow-2xl' : 'border-slate-200 shadow-xl'}`,
                    confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm cursor-pointer',
                    ...(custom.customClass || {})
                },
                ...custom
            };
        }

        // DOM ELEMENTS
        const form = document.getElementById('formCar');
        const containerItem = document.getElementById('container-item');
        const btnTambahItem = document.getElementById('btn-tambah-item');
        const grandTotalOutput = document.getElementById('grand_total');
        const labelJumlahItem = document.getElementById('label-jumlah-item');
        const labelRincianTotal = document.getElementById('label-rincian-total');

        const inputReceiving = document.getElementById('receiving_account');
        const previewRekening = document.getElementById('preview-rekening');
        const previewTotalItems = document.getElementById('preview-total-items');
        const previewTotalBarang = document.getElementById('preview-total-barang');
        const previewTotalOngkir = document.getElementById('preview-total-ongkir');
        const previewGrandTotal = document.getElementById('preview-grand-total');

        const alasanPembelian = document.getElementById('alasan_pembelian');
        const charCounterAlasan = document.getElementById('char-counter-alasan');

        let itemIndex = 1;
        let isConfirmed = false;

        // 1. BANK CHIPS CLICK HANDLER
        document.querySelectorAll('.btn-bank-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                const bankName = this.getAttribute('data-bank');
                let currentVal = inputReceiving.value.trim();

                if (!currentVal) {
                    inputReceiving.value = `${bankName} `;
                } else if (!currentVal.toLowerCase().startsWith(bankName.toLowerCase())) {
                    inputReceiving.value = `${bankName} ${currentVal}`;
                }
                inputReceiving.focus();
                updateLivePreview();
            });
        });

        if (inputReceiving) {
            inputReceiving.addEventListener('input', function () {
                if (previewRekening) {
                    previewRekening.textContent = this.value.trim() ? this.value.trim() : '-';
                }
            });
        }

        // 2. QUICK REASON CHIPS & COUNTER
        document.querySelectorAll('.btn-quick-car-note').forEach(btn => {
            btn.addEventListener('click', function () {
                const text = this.getAttribute('data-text');
                if (!alasanPembelian.value.trim()) {
                    alasanPembelian.value = text;
                } else {
                    alasanPembelian.value = alasanPembelian.value.trim() + " " + text;
                }
                updateCharCounter();
            });
        });

        function updateCharCounter() {
            if (charCounterAlasan && alasanPembelian) {
                const len = alasanPembelian.value.length;
                charCounterAlasan.textContent = `${len} / 1000 karakter`;
            }
        }

        if (alasanPembelian) {
            alasanPembelian.addEventListener('input', updateCharCounter);
            updateCharCounter();
        }

        // 3. ATTACHMENT PREVIEW HANDLER PER ITEM
        function setupPreviewListener(inputElement) {
            inputElement.addEventListener('change', function () {
                const baris = this.closest('.baris-item');
                const previewContainer = baris.querySelector('.preview-container');
                const labelTipe = baris.querySelector('.label-tipe-file');
                const namaFile = baris.querySelector('.nama-file-preview');
                const areaVisual = baris.querySelector('.area-preview-visual');

                const file = this.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        Swal.fire(getSwalTheme({
                            icon: 'error',
                            title: 'Ukuran Berkas Terlalu Besar',
                            text: 'Maksimal ukuran lampiran per item adalah 2MB. Silakan pilih berkas lain.'
                        }));
                        this.value = '';
                        previewContainer.classList.add('hidden');
                        areaVisual.innerHTML = '';
                        return;
                    }

                    namaFile.textContent = file.name;
                    previewContainer.classList.remove('hidden');

                    if (file.type.startsWith('image/')) {
                        labelTipe.textContent = 'Gambar';
                        labelTipe.className = 'p-1 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                        areaVisual.innerHTML = `<img src="${URL.createObjectURL(file)}" class="max-h-24 rounded-lg border border-slate-200 object-contain" alt="Pratinjau Nota">`;
                    } else if (file.type === 'application/pdf') {
                        labelTipe.textContent = 'PDF';
                        labelTipe.className = 'p-1 bg-rose-50 text-rose-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                        areaVisual.innerHTML = `<div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300"><i class="fa-solid fa-file-pdf text-rose-500 text-sm"></i> <span>PDF Siap Diunggah</span></div>`;
                    } else {
                        labelTipe.textContent = 'File';
                        labelTipe.className = 'p-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                        areaVisual.innerHTML = `<span class="text-[10px] text-slate-400">Berkas Terlampir</span>`;
                    }
                } else {
                    previewContainer.classList.add('hidden');
                    areaVisual.innerHTML = '';
                }
            });
        }

        // Pasang preview untuk baris pertama
        const firstFile = containerItem.querySelector('.input-file-dokumen');
        if (firstFile) setupPreviewListener(firstFile);

        // 4. HITUNG AKUMULASI BIAYA & GRAND TOTAL
        function hitungAkumulasi() {
            let totalHargaBarang = 0;
            let totalOngkir = 0;
            let grandTotal = 0;
            const semuaBaris = containerItem.querySelectorAll('.baris-item');

            semuaBaris.forEach((baris, index) => {
                // Update Badge Label Nomor Item
                const labelNomor = baris.querySelector('.label-nomor-item');
                if (labelNomor) labelNomor.textContent = `Item #${index + 1}`;

                // Update Display Title
                const inputNama = baris.querySelector('.input-nama-barang');
                const titleDisplay = baris.querySelector('.item-title-display');
                if (titleDisplay && inputNama) {
                    titleDisplay.textContent = inputNama.value.trim() ? inputNama.value.trim() : 'Barang Baru';
                }

                // Kalkulasi Subtotal
                const inputJumlah = baris.querySelector('.input-jumlah');
                const inputHarga = baris.querySelector('.input-harga');
                const inputOngkir = baris.querySelector('.input-ongkir');
                const labelSubtotal = baris.querySelector('.label-subtotal');

                const qty = parseFloat(inputJumlah.value) || 0;
                const harga = parseFloat(inputHarga.value) || 0;
                const ongkir = parseFloat(inputOngkir ? inputOngkir.value : 0) || 0;

                const hargaBarang = qty * harga;
                const subtotal = hargaBarang + ongkir;

                totalHargaBarang += hargaBarang;
                totalOngkir += ongkir;
                grandTotal += subtotal;

                if (labelSubtotal) {
                    labelSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                }
            });

            const formattedGrandTotal = 'Rp ' + grandTotal.toLocaleString('id-ID');
            const formattedTotalBarang = 'Rp ' + totalHargaBarang.toLocaleString('id-ID');
            const formattedTotalOngkir = 'Rp ' + totalOngkir.toLocaleString('id-ID');

            if (grandTotalOutput) grandTotalOutput.textContent = formattedGrandTotal;
            if (previewGrandTotal) previewGrandTotal.textContent = formattedGrandTotal;

            const itemsCountText = `${semuaBaris.length} Macam Item`;
            if (labelJumlahItem) labelJumlahItem.textContent = `${itemsCountText} Terdaftar`;
            if (previewTotalItems) previewTotalItems.textContent = itemsCountText;

            if (labelRincianTotal) {
                labelRincianTotal.textContent = `Barang: ${formattedTotalBarang} + Total Ongkir: ${formattedTotalOngkir}`;
            }

            if (previewTotalBarang) previewTotalBarang.textContent = formattedTotalBarang;
            if (previewTotalOngkir) previewTotalOngkir.textContent = formattedTotalOngkir;

            // Atur status tombol hapus jika hanya 1 baris
            semuaBaris.forEach(baris => {
                const btn = baris.querySelector('.btn-hapus-item');
                if (btn) {
                    if (semuaBaris.length === 1) {
                        btn.disabled = true;
                        btn.className = "btn-hapus-item text-slate-300 dark:text-slate-600 cursor-not-allowed p-1.5 rounded-xl";
                    } else {
                        btn.disabled = false;
                        btn.className = "btn-hapus-item text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors p-1.5 rounded-xl cursor-pointer";
                    }
                }
            });
        }

        containerItem.addEventListener('input', function(e) {
            if (e.target.classList.contains('input-jumlah') ||
                e.target.classList.contains('input-harga') ||
                e.target.classList.contains('input-ongkir') ||
                e.target.classList.contains('input-nama-barang')) {
                hitungAkumulasi();
            }
        });

        // 5. TAMBAH BARIS ITEM DENGAN DESAIN MODERN
        function tambahBarisItem(namaDefault = '', satuanDefault = 'Pcs') {
            const barisBaru = document.createElement('div');
            barisBaru.className = "baris-item bg-slate-50/70 dark:bg-slate-900/60 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 relative shadow-2xs space-y-3 transition-all";

            barisBaru.innerHTML = `
                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-700/60 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="label-nomor-item px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase tracking-wider bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30">
                            Item #${itemIndex + 1}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold item-title-display">${namaDefault || 'Barang Baru'}</span>
                    </div>

                    <button type="button" class="btn-hapus-item text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors p-1.5 rounded-xl cursor-pointer" title="Hapus Item">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Barang / Material <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-sky-600/70 dark:text-sky-400">
                                <i class="fa-solid fa-box text-xs"></i>
                            </div>
                            <input type="text"
                                   name="items[${itemIndex}][nama_barang]"
                                   required
                                   value="${namaDefault}"
                                   class="input-nama-barang w-full pl-9 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="Nama barang atau material">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:col-span-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Qty <span class="text-rose-500">*</span>
                            </label>
                            <input type="number"
                                   name="items[${itemIndex}][jumlah]"
                                   required
                                   min="1"
                                   value="1"
                                   class="input-jumlah w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="items[${itemIndex}][satuan]"
                                   required
                                   value="${satuanDefault}"
                                   list="satuan-list"
                                   class="input-satuan w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold text-center focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="Pcs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Harga (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number"
                                   name="items[${itemIndex}][estimasi_harga]"
                                   required
                                   min="0"
                                   class="input-harga w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-amber-700 dark:text-amber-400 mb-1.5 flex items-center gap-1">
                                <i class="fa-solid fa-truck-fast text-[10px]"></i>
                                <span>Ongkir</span>
                            </label>
                            <input type="number"
                                   name="items[${itemIndex}][ongkir]"
                                   min="0"
                                   value="0"
                                   class="input-ongkir w-full px-2.5 py-2.5 bg-amber-50/30 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-700/80 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
                                   placeholder="0">
                        </div>
                    </div>

                    <div class="md:col-span-3 flex flex-col justify-center items-start md:items-end pt-1 md:pt-0">
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Subtotal Baris</span>
                        <span class="label-subtotal text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rp 0</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="w-full sm:w-2/3">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                            Lampiran Nota / Proposal / Foto Barang <span class="font-normal text-slate-400">(Opsional untuk item ini)</span>
                        </label>
                        <input type="file"
                               name="items[${itemIndex}][dokumen_pendukung]"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="input-file-dokumen w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 dark:file:bg-sky-950/50 file:text-sky-700 dark:file:text-sky-300 hover:file:bg-sky-100 dark:hover:file:bg-sky-900/60 cursor-pointer">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Format: PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                    </div>

                    <div class="preview-container hidden p-2 bg-white dark:bg-slate-900 border border-sky-200 dark:border-sky-800 rounded-xl w-full sm:w-1/3 shadow-2xs">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="p-1 bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file">File</span>
                            <span class="text-[11px] text-slate-600 dark:text-slate-300 truncate font-medium nama-file-preview">nama_file.jpg</span>
                        </div>
                        <div class="area-preview-visual flex justify-start items-center"></div>
                    </div>
                </div>
            `;

            containerItem.appendChild(barisBaru);
            setupPreviewListener(barisBaru.querySelector('.input-file-dokumen'));
            itemIndex++;
            hitungAkumulasi();
        }

        if (btnTambahItem) {
            btnTambahItem.addEventListener('click', function () {
                tambahBarisItem();
            });
        }

        // 6. PRESET CHIP BARANG
        document.querySelectorAll('.btn-item-template').forEach(btn => {
            btn.addEventListener('click', function () {
                const nama = this.getAttribute('data-nama');
                const satuan = this.getAttribute('data-satuan');

                const barisPertama = containerItem.querySelector('.baris-item');
                const inputNama0 = barisPertama ? barisPertama.querySelector('.input-nama-barang') : null;

                if (barisPertama && inputNama0 && !inputNama0.value.trim()) {
                    inputNama0.value = nama;
                    const inputSatuan0 = barisPertama.querySelector('.input-satuan');
                    if (inputSatuan0) inputSatuan0.value = satuan;
                    hitungAkumulasi();
                } else {
                    tambahBarisItem(nama, satuan);
                }
            });
        });

        // 7. HAPUS BARIS ITEM
        containerItem.addEventListener('click', function(e) {
            const tombolHapus = e.target.closest('.btn-hapus-item');
            if (tombolHapus && !tombolHapus.disabled) {
                const baris = tombolHapus.closest('.baris-item');
                baris.remove();
                hitungAkumulasi();
            }
        });

        // 8. LIVE PREVIEW UPDATE HELPER
        function updateLivePreview() {
            if (previewRekening && inputReceiving) {
                previewRekening.textContent = inputReceiving.value.trim() ? inputReceiving.value.trim() : '-';
            }
        }

        // 9. KONFIRMASI SUBMIT DENGAN SWEETALERT2
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!isConfirmed) {
                    e.preventDefault();

                    const semuaBaris = containerItem.querySelectorAll('.baris-item');
                    if (semuaBaris.length === 0) {
                        Swal.fire(getSwalTheme({
                            icon: 'error',
                            title: 'Daftar Item Kosong',
                            text: 'Silakan tambahkan minimal 1 item barang yang diajukan.'
                        }));
                        return;
                    }

                    const rekeningVal = inputReceiving.value.trim() || '-';
                    const grandTotalVal = grandTotalOutput.textContent;
                    const rincianText = labelRincianTotal.textContent;
                    const dark = isDark();

                    Swal.fire(getSwalTheme({
                        title: 'Konfirmasi Pengajuan CAR',
                        html: `
                            <div class="text-left text-xs ${dark ? 'text-slate-200 bg-slate-900/80 border-slate-700' : 'text-slate-700 bg-slate-50 border-slate-200'} space-y-2 p-3.5 rounded-2xl border">
                                <div><span class="text-slate-400 font-medium">Rekening Tujuan:</span> <strong class="${dark ? 'text-slate-100' : 'text-slate-800'}">${rekeningVal}</strong></div>
                                <div><span class="text-slate-400 font-medium">Jumlah Item:</span> <strong class="${dark ? 'text-slate-100' : 'text-slate-800'}">${semuaBaris.length} Macam Barang</strong></div>
                                <div><span class="text-slate-400 font-medium">Rincian Biaya:</span> <span class="text-slate-500 dark:text-slate-400 font-medium">${rincianText}</span></div>
                                <div><span class="text-slate-400 font-medium">Grand Total Kas Bon:</span> <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">${grandTotalVal}</span></div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 font-medium">Apakah nomor rekening pencairan dan rincian belanja di atas sudah benar?</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim Pengajuan',
                        cancelButtonText: 'Periksa Kembali'
                    })).then((result) => {
                        if (result.isConfirmed) {
                            isConfirmed = true;
                            const btnSubmit = document.getElementById('btn-submit-car');
                            if (btnSubmit) {
                                btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Mengirim...</span>';
                                btnSubmit.disabled = true;
                            }
                            form.submit();
                        }
                    });
                }
            });
        }

        // INITIAL LOAD
        updateLivePreview();
        hitungAkumulasi();
    });
</script>
@endpush

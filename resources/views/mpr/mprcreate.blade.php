@extends('layouts.app')
@section('title', 'Ajukan MPR Baru')

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
                <span class="text-slate-600 dark:text-slate-400">Pengadaan & Fasilitas</span>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-sky-600 dark:text-sky-400 font-bold">Form Pengajuan MPR</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 dark:shadow-sky-500/30 shrink-0">
                    <i class="fa-solid fa-boxes-packing text-base text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Pengajuan MPR (Material Purchase Request)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Formulir pengadaan barang, sparepart teknik, chemical, dan jasa operasional resmi PT META Adhya Tirta Umbulan
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('mpr.riwayat') }}"
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
                            <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                Formulir Pengadaan Material (MPR)
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Cantumkan spesifikasi teknis lengkap untuk mempercepat persetujuan & pembelian
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-200/70 dark:border-sky-500/30">
                            <i class="fa-solid fa-shield-halved text-[10px] text-sky-600 dark:text-sky-400"></i>
                            <span>Verifikasi Berjenjang</span>
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <form id="formMpr" action="{{ route('mpr.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- SEKSI 1: METADATA & LOGISTIK DOKUMEN --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">1</span>
                                    <span>Informasi Dokumen & Logistik</span>
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- NOMOR MPR (OTOMATIS) --}}
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Nomor MPR (Resmi Otomatis)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600 dark:text-sky-400">
                                            <i class="fa-solid fa-hashtag text-sm"></i>
                                        </div>
                                        <input type="text"
                                               name="nomor_mpr"
                                               id="nomor_mpr"
                                               value="{{ $nomorMpr }}"
                                               readonly
                                               class="w-full pl-10 pr-10 py-3 bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-bold focus:outline-none cursor-not-allowed">
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-lock text-xs"></i>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Format atomik sistem: [No] / META / PAS / MPR / [Bulan] / [Tahun]</p>
                                </div>

                                {{-- TANGGAL PENGAJUAN (READONLY DISPLAY) --}}
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Tanggal Pembuatan Dokumen
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-regular fa-calendar-check text-sm"></i>
                                        </div>
                                        <input type="text"
                                               value="{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}"
                                               readonly
                                               class="w-full pl-10 pr-4 py-3 bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold focus:outline-none cursor-not-allowed">
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Dicatat otomatis pada zona waktu WIB</p>
                                </div>
                            </div>

                            {{-- TINGKAT URGENSI / PRIORITY (CARD SELECTOR) --}}
                            <div class="pt-2">
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Tingkat Prioritas Pengadaan <span class="text-rose-500">*</span>
                                </label>
                                <input type="hidden" name="priority" id="input-priority" value="{{ old('priority', 'Normal') }}">

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="priority-cards-container">
                                    {{-- OPSI NORMAL --}}
                                    <button type="button"
                                            class="btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group {{ old('priority', 'Normal') === 'Normal' ? 'border-sky-500 bg-sky-50/70 dark:bg-sky-950/40 ring-2 ring-sky-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                            data-priority="Normal">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                <i class="fa-solid fa-circle text-[6px] text-sky-500"></i>
                                                Normal
                                            </span>
                                            <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 check-icon {{ old('priority', 'Normal') === 'Normal' ? 'opacity-100' : 'opacity-0' }} transition-opacity"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Kebutuhan Rutin</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Pengadaan terjadwal & stok berkala (Lead time reguler)</p>
                                    </button>

                                    {{-- OPSI URGENT --}}
                                    <button type="button"
                                            class="btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group {{ old('priority') === 'Urgent' ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                            data-priority="Urgent">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                <i class="fa-solid fa-bolt text-[8px] text-amber-600"></i>
                                                Urgent
                                            </span>
                                            <i class="fa-solid fa-circle-check text-amber-600 dark:text-amber-400 check-icon {{ old('priority') === 'Urgent' ? 'opacity-100' : 'opacity-0' }} transition-opacity"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Mendesak Operasional</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Stok menipis, butuh proses cepat dalam 1-3 hari</p>
                                    </button>

                                    {{-- OPSI EMERGENCY --}}
                                    <button type="button"
                                            class="btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group {{ old('priority') === 'Emergency' ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                            data-priority="Emergency">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-800 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-[8px] text-rose-600"></i>
                                                Emergency
                                            </span>
                                            <i class="fa-solid fa-circle-check text-rose-600 dark:text-rose-400 check-icon {{ old('priority') === 'Emergency' ? 'opacity-100' : 'opacity-0' }} transition-opacity"></i>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">Kritis / Kendala Fatal</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Instalasi rusak / kegagalan sistem, harus segera dibeli</p>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                {{-- DEPARTEMEN --}}
                                <div>
                                    <label for="department" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Departemen / Divisi Pemohon <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600 dark:text-sky-400">
                                            <i class="fa-solid fa-sitemap text-sm"></i>
                                        </div>
                                        <input type="text"
                                               name="department"
                                               id="department"
                                               value="{{ old('department', $defaultDepartment) }}"
                                               required
                                               class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all {{ $errors->has('department') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                               placeholder="Contoh: Operation">
                                    </div>
                                </div>

                                {{-- DELIVERY POINT / TITIK PENGIRIMAN --}}
                                <div>
                                    <label for="delivery_point" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Titik Pengiriman (Delivery Point) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-rose-500">
                                            <i class="fa-solid fa-location-dot text-sm"></i>
                                        </div>
                                        <input type="text"
                                               name="delivery_point"
                                               id="delivery_point"
                                               value="{{ old('delivery_point', $defaultDeliveryPoint) }}"
                                               required
                                               class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all {{ $errors->has('delivery_point') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                               placeholder="Contoh: Site Umbulan">
                                    </div>
                                </div>
                            </div>

                            {{-- PRESET TITIK PENGIRIMAN CEPAT --}}
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] pt-1">
                                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-map-pin text-sky-500 text-[10px]"></i>
                                    <span>Pilihan Cepat Lokasi:</span>
                                </span>
                                <button type="button" class="btn-delivery-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-val="Site Umbulan">Site Umbulan</button>
                                <button type="button" class="btn-delivery-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-val="Offtake Pasuruan">Offtake Pasuruan</button>
                                <button type="button" class="btn-delivery-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-val="Offtake Sidoarjo">Offtake Sidoarjo</button>
                                <button type="button" class="btn-delivery-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-val="Offtake Surabaya">Offtake Surabaya</button>
                                <button type="button" class="btn-delivery-chip px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-val="Kantor Surabaya">Kantor Surabaya</button>
                            </div>

                            {{-- LATEST MPR DATE (OPSIONAL) --}}
                            <div class="pt-2">
                                <label for="latest_mpr_date" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Tanggal MPR Sebelumnya <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional jika pengadaan berulang)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <i class="fa-regular fa-calendar text-sm"></i>
                                    </div>
                                    <input type="date"
                                           name="latest_mpr_date"
                                           id="latest_mpr_date"
                                           value="{{ old('latest_mpr_date') }}"
                                           class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer">
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 2: CATATAN & URGENSI LAPANGAN --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">2</span>
                                    <span>Catatan Urgensi & Justifikasi Lapangan</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium" id="char-counter-urgensi">0 / 1000 karakter</span>
                            </div>

                            <div>
                                <label for="keperluan_urgensi" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Rincian Penjelasan / Alasan Kebutuhan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="keperluan_urgensi"
                                          id="keperluan_urgensi"
                                          rows="4"
                                          maxlength="1000"
                                          required
                                          class="w-full px-4 py-3 bg-white dark:bg-slate-900 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed {{ $errors->has('keperluan_urgensi') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                          placeholder="- Mohon izin mengajukan pengadaan material untuk keperluan operasional instalasi&#10;- Kondisi aktual di site: sisa stok kritis / perlu penggantian segera&#10;- Spesifikasi telah dikoordinasikan dengan tim teknis...">{{ old('keperluan_urgensi') }}</textarea>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info text-sky-500"></i>
                                    <span>Gunakan tanda strip (-) di awal baris agar otomatis diformat sebagai butir catatan rapi pada dokumen PDF resmi.</span>
                                </p>

                                {{-- QUICK REASON TEMPLATE CHIPS --}}
                                <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-wand-magic-sparkles text-sky-500 text-[10px]"></i>
                                        <span>Template Cepat:</span>
                                    </span>
                                    <button type="button" class="btn-quick-mpr-note inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="- Stok material di site saat ini menipis / kritis, membutuhkan pengadaan segera untuk kelangsungan operasional instalasi.">
                                        <i class="fa-solid fa-boxes-stacked text-amber-500 text-[10px]"></i>
                                        <span>Stok Kritis</span>
                                    </button>
                                    <button type="button" class="btn-quick-mpr-note inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="- Pengadaan berkala terjadwal untuk kegiatan pemeliharaan preventif (maintenance rutin) unit mesin dan pompa.">
                                        <i class="fa-solid fa-wrench text-sky-500 text-[10px]"></i>
                                        <span>Maintenance Rutin</span>
                                    </button>
                                    <button type="button" class="btn-quick-mpr-note inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="- Penggantian komponen instalasi yang mengalami kerusakan / aus guna menjaga kestabilan supply air minum Umbulan.">
                                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-[10px]"></i>
                                        <span>Perbaikan Kerusakan</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 3: RINCIAN MATERIAL & JASA (REQUESTED ITEMS) --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">3</span>
                                    <span>Rincian Material / Jasa (Requested Items)</span>
                                </div>

                                <button type="button"
                                        id="btn-tambah-item"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 hover:shadow-lg hover:shadow-sky-600/30 transition-all cursor-pointer active:scale-95">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Tambah Baris Item</span>
                                </button>
                            </div>

                            {{-- PRESET CHIPS TEMPLATE MATERIAL UMUM --}}
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-cart-plus text-sky-500 text-[10px]"></i>
                                    <span>Template Cepat Item:</span>
                                </span>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Refill Gas Klorin Site Umbulan" data-satuan="Tabung" data-spec="Berat bersih @900 Kg per tabung">Klorin (Tabung)</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Filter Catridge 5 Micron" data-satuan="Pcs" data-spec="Tipe spun bonded polypropylene 20 inch">Filter Catridge</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Pelumas Mesin / Oli Genset Industri" data-satuan="Liter" data-spec="SAE 15W-40 API CI-4/SL kemasan drum / pail">Oli / Pelumas</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Peralatan Keselamatan Kerja (APD Lapangan)" data-satuan="Set" data-spec="Helm safety SNI, rompi reflektor, sarung tangan nitril">Safety / APD</button>
                                <button type="button" class="btn-item-template px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-nama="Kertas HVS A4 80gr & ATK Kantor" data-satuan="Box" data-spec="Untuk kebutuhan administrasi logistik dan laporan shift">ATK & Kertas</button>
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
                                        <div class="md:col-span-5">
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                Nama Barang / Jasa <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-sky-600/70 dark:text-sky-400">
                                                    <i class="fa-solid fa-box text-xs"></i>
                                                </div>
                                                <input type="text"
                                                       name="items[0][nama_barang]"
                                                       required
                                                       class="input-nama-barang w-full pl-9 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="Contoh: Refill tabung klorin site umbulan">
                                            </div>
                                        </div>

                                        {{-- QUANTITY, SATUAN, EST. HARGA --}}
                                        <div class="grid grid-cols-3 gap-2 md:col-span-5">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                    Jumlah <span class="text-rose-500">*</span>
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
                                                       placeholder="Tabung">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                                    Est. Harga Satuan
                                                </label>
                                                <input type="number"
                                                       name="items[0][estimasi_harga]"
                                                       min="0"
                                                       class="input-harga w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                       placeholder="0">
                                            </div>
                                        </div>

                                        {{-- SUBTOTAL PER ITEM --}}
                                        <div class="md:col-span-2 flex flex-col justify-center items-start md:items-end pt-1 md:pt-0">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Subtotal</span>
                                            <span class="label-subtotal text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rp 0</span>
                                        </div>
                                    </div>

                                    {{-- DESKRIPSI / SPESIFIKASI / PART NUMBER --}}
                                    <div class="pt-1">
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                            Description / Technical Specification / Part Number <span class="font-normal text-slate-400">(Opsional)</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fa-solid fa-circle-info text-xs"></i>
                                            </div>
                                            <input type="text"
                                                   name="items[0][keterangan_item]"
                                                   class="input-spec w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                                   placeholder="Contoh: Gas Klorin grade disinfeksi air, kemasan tabung baja @900kg / P/N: CL-PAS-01">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- DATALIST SATUAN UMUM --}}
                            <datalist id="satuan-list">
                                <option value="Tabung">
                                <option value="Pcs">
                                <option value="Unit">
                                <option value="Box">
                                <option value="Kg">
                                <option value="Liter">
                                <option value="Meter">
                                <option value="Set">
                                <option value="Lot">
                                <option value="Batang">
                                <option value="Roll">
                                <option value="Drum">
                                <option value="Paket">
                            </datalist>

                            {{-- RINGKASAN AKUMULASI GRAND TOTAL BOX --}}
                            <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-50/90 via-sky-50/40 to-emerald-50/40 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 border border-slate-200/90 dark:border-slate-700/80 shadow-xs dark:shadow-xl dark:shadow-slate-900/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-receipt text-base"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">Estimasi Total Nilai Pengadaan MPR</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium" id="label-jumlah-item">1 Macam Item Terdaftar</span>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block uppercase font-bold tracking-wider">Grand Total (Est.)</span>
                                    <span id="grand_total" class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 4: DOKUMEN PENDUKUNG / QUOTATION --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">4</span>
                                    <span>Lampiran & Berkas Pendukung (Quotation / Foto)</span>
                                </div>
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 uppercase">
                                    Opsional
                                </span>
                            </div>

                            <div>
                                <label for="dokumen_pendukung" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Dokumen Pendukung <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Penawaran Harga / Foto Fisik Barang / Brosur Teknis)</span>
                                </label>

                                {{-- CUSTOM DROPZONE AREA --}}
                                <div id="dropzone-area-mpr"
                                     class="relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all bg-slate-50/80 dark:bg-slate-900/50 hover:bg-sky-50/40 dark:hover:bg-slate-800/80 border-slate-300 dark:border-slate-700 hover:border-sky-500 dark:hover:border-sky-500 group {{ $errors->has('dokumen_pendukung') ? 'border-rose-400 bg-rose-50/20' : '' }}">

                                    <input type="file"
                                           name="dokumen_pendukung"
                                           id="dokumen_pendukung"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                    {{-- DEFAULT DROPZONE VIEW --}}
                                    <div id="dropzone-placeholder-mpr" class="space-y-2 pointer-events-none">
                                        <div class="w-12 h-12 rounded-2xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 mx-auto flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs shadow-sky-500/10">
                                            <i class="fa-solid fa-cloud-arrow-up text-lg text-sky-600 dark:text-sky-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                                <span class="text-sky-600 dark:text-sky-400 underline decoration-sky-300">Klik untuk jelajahi file</span> atau seret dokumen ke sini
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Format didukung: PDF, JPG, JPEG, PNG (Maksimal 2MB)
                                            </p>
                                        </div>
                                    </div>

                                    {{-- FILE PREVIEW VIEW (POPULATED VIA JS) --}}
                                    <div id="dropzone-preview-mpr" class="space-y-2" style="display: none;">
                                        <div class="inline-flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-xl border border-sky-200 dark:border-sky-800 shadow-sm max-w-md mx-auto">
                                            <div id="preview-file-icon-mpr" class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-file-pdf text-lg"></i>
                                            </div>
                                            <div class="text-left min-w-0 flex-1">
                                                <p id="preview-filename-mpr" class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">document.pdf</p>
                                                <p id="preview-filesize-mpr" class="text-[10px] text-slate-500 dark:text-slate-400">0 KB</p>
                                            </div>
                                            <button type="button" id="btn-hapus-file-mpr" class="w-8 h-8 rounded-xl text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/60 border border-transparent hover:border-rose-200 dark:hover:border-rose-800/60 flex items-center justify-center transition-all cursor-pointer z-20" title="Hapus File">
                                                <i class="fa-solid fa-xmark text-sm"></i>
                                            </button>
                                        </div>
                                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-circle-check text-xs text-emerald-500 dark:text-emerald-400"></i>
                                            <span>Dokumen siap diunggah</span>
                                        </p>
                                    </div>
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
                                <a href="{{ route('mpr.riwayat') }}"
                                   class="flex-1 sm:flex-none px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white hover:border-slate-400 dark:hover:border-slate-600 text-xs sm:text-sm font-bold transition-all text-center shadow-xs">
                                    Batal
                                </a>
                                <button type="submit"
                                        id="btn-submit-mpr"
                                        class="flex-1 sm:flex-none px-7 py-3 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 via-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 dark:from-sky-500 dark:via-sky-500 dark:to-cyan-500 dark:hover:from-sky-400 dark:hover:to-cyan-400 text-white text-xs sm:text-sm font-extrabold shadow-md shadow-sky-600/20 hover:shadow-lg hover:shadow-sky-600/30 dark:shadow-sky-500/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Kirim Pengajuan MPR</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: SIDEBAR WIDGETS (4 COLS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- WIDGET 1: STATUS RECORD PENGADAAN MPR USER TAHUN INI --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-6 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 relative overflow-hidden transition-all">
                {{-- Ambient Top Glow --}}
                <div class="absolute -top-12 -right-12 w-36 h-36 rounded-full bg-gradient-to-br from-sky-400/20 to-cyan-400/20 blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-500/15 px-2.5 py-1 rounded-full border border-sky-200/70 dark:border-sky-500/30">
                        STATUS PENGADAAN {{ date('Y') }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Tahun Aktif</span>
                </div>

                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-4xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                            {{ $totalMprUser ?? 0 }}
                        </span>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400 ml-1">Pengajuan MPR</span>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-600/25 dark:shadow-sky-500/30">
                        <i class="fa-solid fa-cart-flatbed text-base text-white"></i>
                    </div>
                </div>

                {{-- MINI BREAKDOWN STATS --}}
                <div class="mt-5 grid grid-cols-3 gap-2.5 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Total</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5 block">{{ $totalMprUser ?? 0 }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Disetujui</span>
                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $mprApprovedCount ?? 0 }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Pending</span>
                        <span class="text-xs font-black text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $mprPendingCount ?? 0 }}</span>
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
                        Ringkasan Pengajuan MPR (Live)
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">No. MPR</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400 truncate max-w-[170px]" id="preview-no-mpr">{{ $nomorMpr }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Pemohon</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[160px]">{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Prioritas</span>
                        <span id="preview-priority-badge" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 uppercase">
                            Normal
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Departemen</span>
                        <span id="preview-department" class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">{{ $defaultDepartment }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Lokasi Tujuan</span>
                        <span id="preview-delivery" class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">{{ $defaultDeliveryPoint }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Macam Item</span>
                        <span id="preview-total-items" class="font-bold text-slate-800 dark:text-slate-100">1 Item</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Total Estimasi</span>
                        <span id="preview-grand-total" class="font-black text-emerald-600 dark:text-emerald-400">Rp 0</span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500 dark:text-slate-400">Status Dokumen</span>
                        <span id="preview-status-dokumen-mpr" class="font-semibold text-slate-500 dark:text-slate-400">Belum ada file</span>
                    </div>
                </div>
            </div>

            {{-- WIDGET 3: KETENTUAN & SOP PENGADAAN (POLICY & GUIDELINES) --}}
            <div class="bg-gradient-to-br from-slate-50 to-sky-50/50 dark:from-slate-800/80 dark:to-slate-900/60 rounded-3xl border border-sky-200/80 dark:border-slate-700/70 p-6 space-y-3 shadow-sm">
                <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-circle-question text-xs text-sky-600 dark:text-sky-400"></i>
                    </div>
                    <span>Ketentuan Penting Pengadaan (SOP)</span>
                </h4>

                <ul class="space-y-2.5 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Spesifikasi Lengkap:</strong> Tuliskan tipe, part number, dimensi, dan merk agar bagian procurement tepat membeli.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Lampiran Pendukung:</strong> Sertakan foto barang rusak atau surat penawaran harga jika sudah tersedia untuk mempercepat PO.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Verifikasi WhatsApp:</strong> Notifikasi persetujuan instan dikirim ke Atasan/Manager via WhatsApp Gateway resmi.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Cetak PDF Resmi:</strong> Dokumen MPR resmi bertanda tangan digital dapat dicetak setelah disetujui penuh.</span>
                    </li>
                </ul>
            </div>

            {{-- WIDGET 4: PENGAJUAN MPR TERAKHIR ANDA --}}
            @if(isset($recentMprs) && $recentMprs->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-xs text-slate-600 dark:text-slate-400"></i>
                            </div>
                            <span>Pengajuan MPR Terakhir</span>
                        </h4>
                        <a href="{{ route('mpr.riwayat') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300">
                            Semua
                        </a>
                    </div>

                    <div class="space-y-2">
                        @foreach($recentMprs as $rm)
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                <div class="min-w-0 flex-1 pr-2">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $rm->nomor_mpr }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($rm->tanggal_pengajuan)->format('d M Y') }} • {{ $rm->items->count() }} Item • {{ $rm->delivery_point }}
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    @if($rm->status_akhir === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30">
                                            Disetujui
                                        </span>
                                    @elseif($rm->status_akhir === 'rejected')
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
            title: 'PENGAJUAN MPR BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'Buka Riwayat MPR',
            confirmButtonColor: '#0284c7',
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            customClass: {
                popup: `rounded-3xl border ${isDark ? 'border-slate-700 shadow-2xl' : 'border-slate-200 shadow-xl'}`,
                confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 cursor-pointer'
            }
        }).then(() => {
            window.location.href = "{{ route('mpr.riwayat') }}";
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
        const form = document.getElementById('formMpr');
        const containerItem = document.getElementById('container-item');
        const btnTambahItem = document.getElementById('btn-tambah-item');
        const grandTotalOutput = document.getElementById('grand_total');
        const labelJumlahItem = document.getElementById('label-jumlah-item');

        const inputPriority = document.getElementById('input-priority');
        const priorityCards = document.querySelectorAll('.btn-priority-card');
        const previewPriorityBadge = document.getElementById('preview-priority-badge');

        const inputDepartment = document.getElementById('department');
        const previewDepartment = document.getElementById('preview-department');

        const inputDelivery = document.getElementById('delivery_point');
        const previewDelivery = document.getElementById('preview-delivery');

        const previewTotalItems = document.getElementById('preview-total-items');
        const previewGrandTotal = document.getElementById('preview-grand-total');

        const keperluanUrgensi = document.getElementById('keperluan_urgensi');
        const charCounterUrgensi = document.getElementById('char-counter-urgensi');

        const inputDokumen = document.getElementById('dokumen_pendukung');
        const dropzoneArea = document.getElementById('dropzone-area-mpr');
        const dropzonePlaceholder = document.getElementById('dropzone-placeholder-mpr');
        const dropzonePreview = document.getElementById('dropzone-preview-mpr');
        const previewFilename = document.getElementById('preview-filename-mpr');
        const previewFilesize = document.getElementById('preview-filesize-mpr');
        const previewFileIcon = document.getElementById('preview-file-icon-mpr');
        const btnHapusFile = document.getElementById('btn-hapus-file-mpr');
        const previewStatusDokumen = document.getElementById('preview-status-dokumen-mpr');

        let itemIndex = 1;
        let isConfirmed = false;

        // 1. PRIORITY CARD SELECTOR INTERACTION
        function updatePriorityUI(priority) {
            inputPriority.value = priority;

            priorityCards.forEach(card => {
                const cardPriority = card.getAttribute('data-priority');
                const checkIcon = card.querySelector('.check-icon');

                if (cardPriority === priority) {
                    if (priority === 'Emergency') {
                        card.className = "btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-2 ring-rose-500/20";
                    } else if (priority === 'Urgent') {
                        card.className = "btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-2 ring-amber-500/20";
                    } else {
                        card.className = "btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group border-sky-500 bg-sky-50/70 dark:bg-sky-950/40 ring-2 ring-sky-500/20";
                    }
                    if (checkIcon) checkIcon.classList.remove('opacity-0');
                } else {
                    card.className = "btn-priority-card relative p-3.5 rounded-2xl border transition-all text-left cursor-pointer group border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600";
                    if (checkIcon) checkIcon.classList.add('opacity-0');
                }
            });

            // Update Live Preview Badge
            if (previewPriorityBadge) {
                if (priority === 'Emergency') {
                    previewPriorityBadge.textContent = 'Emergency';
                    previewPriorityBadge.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-800 uppercase animate-pulse';
                } else if (priority === 'Urgent') {
                    previewPriorityBadge.textContent = 'Urgent';
                    previewPriorityBadge.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800 uppercase';
                } else {
                    previewPriorityBadge.textContent = 'Normal';
                    previewPriorityBadge.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 uppercase';
                }
            }
        }

        priorityCards.forEach(card => {
            card.addEventListener('click', function () {
                const priority = this.getAttribute('data-priority');
                updatePriorityUI(priority);
            });
        });

        // 2. DELIVERY CHIPS & INPUT SYNC
        document.querySelectorAll('.btn-delivery-chip').forEach(btn => {
            btn.addEventListener('click', function () {
                const val = this.getAttribute('data-val');
                inputDelivery.value = val;
                if (previewDelivery) previewDelivery.textContent = val;
            });
        });

        if (inputDelivery) {
            inputDelivery.addEventListener('input', function () {
                if (previewDelivery) previewDelivery.textContent = this.value || '-';
            });
        }

        if (inputDepartment) {
            inputDepartment.addEventListener('input', function () {
                if (previewDepartment) previewDepartment.textContent = this.value || '-';
            });
        }

        // 3. QUICK MPR NOTE TEMPLATES
        document.querySelectorAll('.btn-quick-mpr-note').forEach(btn => {
            btn.addEventListener('click', function () {
                const text = this.getAttribute('data-text');
                if (!keperluanUrgensi.value.trim()) {
                    keperluanUrgensi.value = text;
                } else {
                    keperluanUrgensi.value = keperluanUrgensi.value.trim() + "\n" + text;
                }
                updateCharCounter();
            });
        });

        function updateCharCounter() {
            if (charCounterUrgensi && keperluanUrgensi) {
                const len = keperluanUrgensi.value.length;
                charCounterUrgensi.textContent = `${len} / 1000 karakter`;
            }
        }

        if (keperluanUrgensi) {
            keperluanUrgensi.addEventListener('input', updateCharCounter);
            updateCharCounter();
        }

        // 4. DRAG & DROP FILE UPLOAD
        if (dropzoneArea) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzoneArea.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzoneArea.classList.add('border-sky-500', 'bg-sky-50/50', 'dark:bg-slate-800');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzoneArea.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzoneArea.classList.remove('border-sky-500', 'bg-sky-50/50', 'dark:bg-slate-800');
                }, false);
            });

            dropzoneArea.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    inputDokumen.files = files;
                    handleFileSelected(files[0]);
                }
            });
        }

        if (inputDokumen) {
            inputDokumen.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    handleFileSelected(this.files[0]);
                } else {
                    resetFilePreview();
                }
            });
        }

        function handleFileSelected(file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire(getSwalTheme({
                    icon: 'error',
                    title: 'Ukuran Dokumen Terlalu Besar',
                    text: 'Maksimal ukuran file dokumen pendukung adalah 2MB. Silakan pilih dokumen lain.'
                }));
                inputDokumen.value = '';
                resetFilePreview();
                return;
            }

            previewFilename.textContent = file.name;
            const sizeInKb = (file.size / 1024).toFixed(1);
            previewFilesize.textContent = sizeInKb > 1024 ? `${(sizeInKb / 1024).toFixed(2)} MB` : `${sizeInKb} KB`;

            if (file.name.toLowerCase().endsWith('.pdf')) {
                previewFileIcon.innerHTML = '<i class="fa-solid fa-file-pdf text-rose-500 dark:text-rose-400 text-lg"></i>';
            } else {
                previewFileIcon.innerHTML = '<i class="fa-solid fa-file-image text-sky-500 dark:text-sky-400 text-lg"></i>';
            }

            dropzonePlaceholder.style.display = 'none';
            dropzonePreview.style.display = 'block';
            if (previewStatusDokumen) {
                previewStatusDokumen.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold truncate max-w-[140px] inline-block">${file.name}</span>`;
            }
        }

        function resetFilePreview() {
            if (inputDokumen) inputDokumen.value = '';
            if (dropzonePlaceholder) dropzonePlaceholder.style.display = 'block';
            if (dropzonePreview) dropzonePreview.style.display = 'none';
            if (previewStatusDokumen) previewStatusDokumen.textContent = 'Belum ada file';
        }

        if (btnHapusFile) {
            btnHapusFile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                resetFilePreview();
            });
        }

        // 5. ITEM MANAGEMENT & REAL-TIME CALCULATION
        function hitungAkumulasi() {
            let akumulasiGrandTotal = 0;
            const semuaBaris = containerItem.querySelectorAll('.baris-item');

            semuaBaris.forEach((baris, index) => {
                // Update Badge Label Nomor Item
                const labelNomor = baris.querySelector('.label-nomor-item');
                if (labelNomor) labelNomor.textContent = `Item #${index + 1}`;

                // Update Title Item
                const inputNama = baris.querySelector('.input-nama-barang');
                const titleDisplay = baris.querySelector('.item-title-display');
                if (titleDisplay && inputNama) {
                    titleDisplay.textContent = inputNama.value.trim() ? inputNama.value.trim() : 'Barang Baru';
                }

                // Kalkulasi Subtotal
                const inputJumlah = baris.querySelector('.input-jumlah');
                const inputHarga = baris.querySelector('.input-harga');
                const labelSubtotal = baris.querySelector('.label-subtotal');

                const qty = parseFloat(inputJumlah.value) || 0;
                const harga = parseFloat(inputHarga.value) || 0;
                const subtotal = qty * harga;

                akumulasiGrandTotal += subtotal;
                if (labelSubtotal) {
                    labelSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                }
            });

            const formattedGrandTotal = 'Rp ' + akumulasiGrandTotal.toLocaleString('id-ID');
            if (grandTotalOutput) grandTotalOutput.textContent = formattedGrandTotal;
            if (previewGrandTotal) previewGrandTotal.textContent = formattedGrandTotal;

            const itemsCountText = `${semuaBaris.length} Macam Item`;
            if (labelJumlahItem) labelJumlahItem.textContent = `${itemsCountText} Terdaftar`;
            if (previewTotalItems) previewTotalItems.textContent = itemsCountText;

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

        // INPUT EVENT LISTENER DI DALAM CONTAINER ITEM
        containerItem.addEventListener('input', function(e) {
            if (e.target.classList.contains('input-jumlah') ||
                e.target.classList.contains('input-harga') ||
                e.target.classList.contains('input-nama-barang')) {
                hitungAkumulasi();
            }
        });

        // 6. TAMBAH BARIS ITEM DENGAN DESAIN MODERN
        function tambahBarisItem(namaDefault = '', satuanDefault = 'Pcs', specDefault = '') {
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
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Barang / Jasa <span class="text-rose-500">*</span>
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
                                   placeholder="Nama barang atau jasa">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 md:col-span-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                                Jumlah <span class="text-rose-500">*</span>
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
                                Est. Harga Satuan
                            </label>
                            <input type="number"
                                   name="items[${itemIndex}][estimasi_harga]"
                                   min="0"
                                   class="input-harga w-full px-2.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                                   placeholder="0">
                        </div>
                    </div>

                    <div class="md:col-span-2 flex flex-col justify-center items-start md:items-end pt-1 md:pt-0">
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Subtotal</span>
                        <span class="label-subtotal text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rp 0</span>
                    </div>
                </div>

                <div class="pt-1">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                        Description / Technical Specification / Part Number <span class="font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </div>
                        <input type="text"
                               name="items[${itemIndex}][keterangan_item]"
                               value="${specDefault}"
                               class="input-spec w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                               placeholder="Spesifikasi teknis detail barang atau part number">
                    </div>
                </div>
            `;

            containerItem.appendChild(barisBaru);
            itemIndex++;
            hitungAkumulasi();
        }

        if (btnTambahItem) {
            btnTambahItem.addEventListener('click', function () {
                tambahBarisItem();
            });
        }

        // TEMPLATE PRESET CHIPS UNTUK ITEM
        document.querySelectorAll('.btn-item-template').forEach(btn => {
            btn.addEventListener('click', function () {
                const nama = this.getAttribute('data-nama');
                const satuan = this.getAttribute('data-satuan');
                const spec = this.getAttribute('data-spec');

                // Jika baris pertama masih kosong, isi baris pertama
                const barisPertama = containerItem.querySelector('.baris-item');
                const inputNama0 = barisPertama ? barisPertama.querySelector('.input-nama-barang') : null;

                if (barisPertama && inputNama0 && !inputNama0.value.trim()) {
                    inputNama0.value = nama;
                    const inputSatuan0 = barisPertama.querySelector('.input-satuan');
                    if (inputSatuan0) inputSatuan0.value = satuan;
                    const inputSpec0 = barisPertama.querySelector('.input-spec');
                    if (inputSpec0) inputSpec0.value = spec;
                    hitungAkumulasi();
                } else {
                    tambahBarisItem(nama, satuan, spec);
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

        // 8. KONFIRMASI SUBMIT DENGAN SWEETALERT2
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!isConfirmed) {
                    e.preventDefault();

                    const semuaBaris = containerItem.querySelectorAll('.baris-item');
                    if (semuaBaris.length === 0) {
                        Swal.fire(getSwalTheme({
                            icon: 'error',
                            title: 'Daftar Item Kosong',
                            text: 'Silakan tambahkan minimal 1 item barang atau jasa yang diminta.'
                        }));
                        return;
                    }

                    const priorityVal = inputPriority.value;
                    const deliveryVal = inputDelivery.value || '-';
                    const grandTotalVal = grandTotalOutput.textContent;
                    const dark = isDark();

                    Swal.fire(getSwalTheme({
                        title: 'Konfirmasi Pengajuan MPR',
                        html: `
                            <div class="text-left text-xs ${dark ? 'text-slate-200 bg-slate-900/80 border-slate-700' : 'text-slate-700 bg-slate-50 border-slate-200'} space-y-2 p-3.5 rounded-2xl border">
                                <div><span class="text-slate-400 font-medium">Prioritas:</span> <strong class="${priorityVal === 'Emergency' ? 'text-rose-600' : (priorityVal === 'Urgent' ? 'text-amber-600' : 'text-sky-600')} font-extrabold uppercase">${priorityVal}</strong></div>
                                <div><span class="text-slate-400 font-medium">Tujuan:</span> <strong class="${dark ? 'text-slate-100' : 'text-slate-800'}">${deliveryVal}</strong></div>
                                <div><span class="text-slate-400 font-medium">Jumlah Item:</span> <strong class="${dark ? 'text-slate-100' : 'text-slate-800'}">${semuaBaris.length} Macam Barang/Jasa</strong></div>
                                <div><span class="text-slate-400 font-medium">Est. Nilai:</span> <span class="font-black text-emerald-600 dark:text-emerald-400">${grandTotalVal}</span></div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 font-medium">Apakah seluruh spesifikasi barang dan justifikasi kebutuhan sudah sesuai?</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim Pengajuan',
                        cancelButtonText: 'Periksa Kembali'
                    })).then((result) => {
                        if (result.isConfirmed) {
                            isConfirmed = true;
                            const btnSubmit = document.getElementById('btn-submit-mpr');
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
        updatePriorityUI("{{ old('priority', 'Normal') }}");
        hitungAkumulasi();
    });
</script>
@endpush

@extends('layouts.app')
@section('title', 'Pengajuan Cuti Karyawan')

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
                <span class="text-sky-600 dark:text-sky-400 font-bold">Form Pengajuan Cuti</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 dark:shadow-sky-500/30 shrink-0">
                    <i class="fa-solid fa-calendar-plus text-base text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Pengajuan Cuti & Izin Kerja
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Layanan mandiri (ESS) pengajuan cuti resmi dengan verifikasi berjenjang otomatis via WhatsApp
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('cuti.riwayat') }}"
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
            @if($errors->has('error'))
                <div class="p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-start gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/80 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200 uppercase tracking-wider">Perhatian</h4>
                        <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-300 mt-0.5 font-medium leading-relaxed">
                            {{ $errors->first('error') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- DYNAMIC JS ERROR SALDO --}}
            <div id="pesan-error-saldo"
                 class="p-4 bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex items-start gap-3 shadow-xs"
                 style="display: none;">
                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/80 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-exclamation text-sm"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs font-bold text-amber-800 dark:text-amber-200 uppercase tracking-wider">Sisa Saldo Tidak Mencukupi</h4>
                    <p id="pesan-error-saldo-text" class="text-xs sm:text-sm text-amber-700 dark:text-amber-300 mt-0.5 font-medium leading-relaxed"></p>
                </div>
            </div>

            {{-- FORM CARD UTAMA --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 overflow-hidden transition-all">

                {{-- CARD HEADER --}}
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50/80 via-white to-sky-50/30 dark:from-slate-800/90 dark:via-slate-800 dark:to-slate-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center">
                            <i class="fa-solid fa-file-pen text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                Formulir Permohonan Cuti
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Pastikan rincian tanggal dan dokumen terisi dengan valid
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-200/70 dark:border-sky-500/30">
                            <i class="fa-solid fa-shield-halved text-[10px] text-sky-600 dark:text-sky-400"></i>
                            <span>Verifikasi Online</span>
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <form id="formCuti" action="{{ route('cuti.storeWeb') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- SEKSI 1: PILIHAN JENIS CUTI & SUB CUTI --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">1</span>
                                <span>Kategori & Klasifikasi Izin</span>
                            </div>

                            {{-- SELECT JENIS CUTI UTAMA --}}
                            <div>
                                <label for="jenis_cuti_id" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Jenis Cuti Utama <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600/70 dark:text-sky-400">
                                        <i class="fa-solid fa-list-check text-sm"></i>
                                    </div>
                                    <select name="jenis_cuti_id"
                                            id="jenis_cuti_id"
                                            class="w-full pl-10 pr-10 py-3 bg-white dark:bg-slate-900/90 border rounded-2xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer appearance-none {{ $errors->has('jenis_cuti_id') ? 'border-rose-400 bg-rose-50/20 dark:border-rose-500/50' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                            required>
                                        <option value="" disabled selected hidden>-- Pilih Jenis Cuti / Izin --</option>
                                        @foreach($jenisCuti as $jenis)
                                            @php
                                                $userGender = strtolower(auth()->user()->gender->name ?? auth()->user()->gender ?? '');
                                                $isPria = ($userGender === 'pria' || userGender === 'male' || userGender === '1');
                                                $namaCutiLower = strtolower($jenis->name_cuti);
                                            @endphp

                                            @if($isPria && (str_contains($namaCutiLower, 'melahirkan') || str_contains($namaCutiLower, 'haid') || str_contains($namaCutiLower, 'bersalin')))
                                                @continue
                                            @endif

                                            <option value="{{ $jenis->id }}"
                                                    data-nama-cuti="{{ $jenis->name_cuti }}"
                                                    data-kode-cuti="{{ $jenis->kode_cuti ?? '' }}"
                                                    {{ old('jenis_cuti_id') == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->name_cuti }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                @error('jenis_cuti_id')
                                    <span class="text-xs text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                                        <span>{{ $message }}</span>
                                    </span>
                                @enderror

                                {{-- DYNAMIC BADGE INFO JENIS CUTI --}}
                                <div id="info-badge-jenis" class="mt-2.5 flex flex-wrap items-center gap-2" style="display: none;">
                                    <span id="badge-potong-saldo" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30">
                                        <i class="fa-solid fa-wallet text-[10px] text-amber-600 dark:text-amber-400"></i>
                                        <span id="txt-badge-potong-saldo">Memotong Kuota Tahunan</span>
                                    </span>
                                    <span id="badge-kuota-info" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <i class="fa-solid fa-circle-info text-[10px] text-sky-500 dark:text-sky-400"></i>
                                        <span id="txt-badge-kuota-info">Sisa Saldo: {{ $sisaSaldo }} Hari</span>
                                    </span>
                                </div>
                            </div>

                            {{-- WRAPPER SUB-CUTI / DETAIL PILIHAN --}}
                            <div id="wrapper_sub_cuti" class="p-4 rounded-2xl bg-sky-50/60 dark:bg-slate-900/60 border border-sky-200/70 dark:border-slate-700/80 space-y-2 transition-all" style="display: none;">
                                <div class="flex items-center justify-between">
                                    <label for="sub_cuti_id" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Detail Keperluan / Sub-Kategori <span class="text-rose-500">*</span>
                                    </label>
                                    <span id="sub-cuti-badge-rule" class="text-[10px] font-black px-2.5 py-0.5 rounded-md bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30 uppercase tracking-wider">
                                        Pilih Kriteria
                                    </span>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600/70 dark:text-sky-400">
                                        <i class="fa-solid fa-diagram-project text-sm"></i>
                                    </div>
                                    <select id="sub_cuti_id"
                                            name="sub_cuti_id"
                                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-900 border rounded-xl text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer appearance-none {{ $errors->has('sub_cuti_id') ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                        <option value="" disabled selected hidden>-- Pilih Detail Perizinan / Sub-Cuti --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                <div id="sub-cuti-info-note" class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 pt-1" style="display: none;">
                                    <i class="fa-solid fa-lightbulb text-amber-500 dark:text-amber-400 text-xs"></i>
                                    <span id="sub-cuti-info-text">Durasi maksimal disesuaikan dengan ketentuan SOP.</span>
                                </div>
                                @error('sub_cuti_id')
                                    <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- SEKSI 2: PERIODE & JADWAL PELAKSANAAN --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">2</span>
                                    <span>Rentang Waktu & Durasi</span>
                                </div>

                                {{-- QUICK PRESET CHIPS --}}
                                <div class="hidden sm:flex items-center gap-1.5 text-[11px]">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-bolt text-amber-500 dark:text-amber-400 text-[10px]"></i>
                                        <span>Preset:</span>
                                    </span>
                                    <button type="button" class="btn-preset-durasi px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-days="1">1 Hari</button>
                                    <button type="button" class="btn-preset-durasi px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-days="2">2 Hari</button>
                                    <button type="button" class="btn-preset-durasi px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-bold transition-all shadow-2xs cursor-pointer active:scale-95" data-days="3">3 Hari</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- TANGGAL MULAI --}}
                                <div>
                                    <label for="tanggal_mulai" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Tanggal Mulai <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-regular fa-calendar-check text-sm"></i>
                                        </div>
                                        <input type="date"
                                               name="tanggal_mulai"
                                               id="tanggal_mulai"
                                               min="{{ date('Y-m-d') }}"
                                               value="{{ old('tanggal_mulai') }}"
                                               class="w-full pl-10 pr-4 py-3 border rounded-2xl bg-white dark:bg-slate-900/90 text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer {{ $errors->has('tanggal_mulai') ? 'border-rose-400 bg-rose-50/20 dark:border-rose-500/50' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                               required>
                                    </div>
                                    @error('tanggal_mulai')
                                        <span class="text-xs text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                                            <span>{{ $message }}</span>
                                        </span>
                                    @enderror
                                </div>

                                {{-- TANGGAL SELESAI --}}
                                <div>
                                    <label for="tanggal_selesai" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                        Tanggal Selesai <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-cyan-600 dark:text-cyan-400">
                                            <i class="fa-regular fa-calendar-xmark text-sm"></i>
                                        </div>
                                        <input type="date"
                                               name="tanggal_selesai"
                                               id="tanggal_selesai"
                                               min="{{ date('Y-m-d') }}"
                                               value="{{ old('tanggal_selesai') }}"
                                               class="w-full pl-10 pr-4 py-3 border rounded-2xl bg-white dark:bg-slate-900/90 text-slate-800 dark:text-slate-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all cursor-pointer {{ $errors->has('tanggal_selesai') ? 'border-rose-400 bg-rose-50/20 dark:border-rose-500/50' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                               required>
                                    </div>
                                    @error('tanggal_selesai')
                                        <span class="text-xs text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                                            <span>{{ $message }}</span>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- LIVE WORKING DAY CALCULATOR WIDGET --}}
                            <div id="live-calculator-box"
                                 class="p-4 rounded-2xl bg-gradient-to-r from-sky-50/70 via-cyan-50/40 to-slate-50/60 dark:from-slate-900/80 dark:via-slate-900/60 dark:to-slate-800/60 border border-sky-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all"
                                 style="display: none;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-calculator text-base text-sky-600 dark:text-sky-400"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Durasi Terhitung:</span>
                                            <span id="badge-total-hari-kerja" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-sky-600 dark:bg-sky-500 text-white shadow-xs shadow-sky-500/20">
                                                0 Hari Kerja
                                            </span>
                                        </div>
                                        <p id="keterangan-skip-libur" class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Hari libur & tanggal merah tidak dihitung dalam kuota
                                        </p>
                                    </div>
                                </div>

                                <div class="text-left sm:text-right text-[11px] text-slate-500 dark:text-slate-400 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200/60 dark:border-slate-700/60">
                                    <span class="block font-medium text-slate-600 dark:text-slate-300">Pola Kerja Anda:</span>
                                    <span class="font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wide">
                                        {{ str_replace('_', ' ', auth()->user()->schedule_type ?? 'reguler_5_hari') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 3: ALASAN & CATATAN --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">3</span>
                                    <span>Alasan & Penjelasan</span>
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium" id="char-counter">0 / 500 karakter</span>
                            </div>

                            <div>
                                <label id="label-alasan" for="alasan_cuti" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Alasan / Catatan Tambahan <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional)</span>
                                </label>
                                <textarea name="alasan_cuti"
                                          id="alasan_cuti"
                                          rows="3"
                                          maxlength="500"
                                          class="w-full px-4 py-3 bg-white dark:bg-slate-900/90 text-slate-800 dark:text-slate-100 border rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed {{ $errors->has('alasan_cuti') ? 'border-rose-400 bg-rose-50/20 dark:border-rose-500/50' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                          placeholder="Tuliskan keterangan keperluan cuti secara jelas (contoh: Menikahkan adik kandung di kediaman keluarga, dsb)...">{{ old('alasan_cuti') }}</textarea>
                                @error('alasan_cuti')
                                    <span class="text-xs text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                                        <span>{{ $message }}</span>
                                    </span>
                                @enderror

                                {{-- QUICK REASON TEMPLATE CHIPS --}}
                                <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold mr-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-wand-magic-sparkles text-sky-500 dark:text-sky-400 text-[10px]"></i>
                                        <span>Template Cepat:</span>
                                    </span>
                                    <button type="button" class="btn-quick-reason inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="Keperluan urusan keluarga di kampung halaman.">
                                        <i class="fa-solid fa-people-roof text-sky-500 dark:text-sky-400 text-[10px]"></i>
                                        <span>Keperluan Keluarga</span>
                                    </button>
                                    <button type="button" class="btn-quick-reason inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="Pemeriksaan medis / istirahat rawat jalan menurut anjuran dokter.">
                                        <i class="fa-solid fa-stethoscope text-emerald-500 dark:text-emerald-400 text-[10px]"></i>
                                        <span>Kesehatan / Istirahat</span>
                                    </button>
                                    <button type="button" class="btn-quick-reason inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 dark:bg-slate-800 dark:hover:bg-sky-950/60 dark:text-slate-300 dark:hover:text-sky-300 border border-slate-300/80 hover:border-sky-400 dark:border-slate-700 dark:hover:border-sky-500/40 font-semibold transition-all shadow-2xs cursor-pointer active:scale-95" data-text="Menghadiri acara pernikahan keluarga inti.">
                                        <i class="fa-solid fa-heart text-rose-500 dark:text-rose-400 text-[10px]"></i>
                                        <span>Acara Pernikahan</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 4: DOKUMEN PENDUKUNG (DRAG & DROP READY) --}}
                        <div class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <span class="w-6 h-6 rounded-full bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">4</span>
                                    <span>Lampiran & Berkas Pendukung</span>
                                </div>
                                <span id="badge-wajib-lampiran" class="text-[10px] font-bold px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 uppercase">
                                    Opsional
                                </span>
                            </div>

                            <div>
                                <label id="label-dokumen" for="input-dokumen" class="block text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                    Dokumen Pendukung <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional)</span>
                                </label>

                                {{-- CUSTOM DROPZONE AREA --}}
                                <div id="dropzone-area"
                                     class="relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all bg-slate-50/80 dark:bg-slate-900/50 hover:bg-sky-50/40 dark:hover:bg-slate-800/80 border-slate-300 dark:border-slate-700 hover:border-sky-500 dark:hover:border-sky-500 group {{ $errors->has('dokumen_pendukung') ? 'border-rose-400 bg-rose-50/20' : '' }}">

                                    <input type="file"
                                           name="dokumen_pendukung"
                                           id="input-dokumen"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                    {{-- DEFAULT DROPZONE VIEW --}}
                                    <div id="dropzone-placeholder" class="space-y-2 pointer-events-none">
                                        <div class="w-12 h-12 rounded-2xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 mx-auto flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs shadow-sky-500/10">
                                            <i class="fa-solid fa-cloud-arrow-up text-lg text-sky-600 dark:text-sky-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">
                                                <span class="text-sky-600 dark:text-sky-400 underline decoration-sky-300">Klik untuk jelajahi file</span> atau seret file ke sini
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Format didukung: PDF, JPG, JPEG, PNG (Maksimal 2MB)
                                            </p>
                                        </div>
                                    </div>

                                    {{-- FILE PREVIEW VIEW (POPULATED VIA JS) --}}
                                    <div id="dropzone-preview" class="space-y-2" style="display: none;">
                                        <div class="inline-flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-xl border border-sky-200 dark:border-sky-800 shadow-sm max-w-md mx-auto">
                                            <div id="preview-file-icon" class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-file-pdf text-lg"></i>
                                            </div>
                                            <div class="text-left min-w-0 flex-1">
                                                <p id="preview-filename" class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">document.pdf</p>
                                                <p id="preview-filesize" class="text-[10px] text-slate-500 dark:text-slate-400">0 KB</p>
                                            </div>
                                            <button type="button" id="btn-hapus-file" class="w-8 h-8 rounded-xl text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/60 border border-transparent hover:border-rose-200 dark:hover:border-rose-800/60 flex items-center justify-center transition-all cursor-pointer z-20" title="Hapus File">
                                                <i class="fa-solid fa-xmark text-sm"></i>
                                            </button>
                                        </div>
                                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-circle-check text-xs text-emerald-500 dark:text-emerald-400"></i>
                                            <span>File siap diunggah</span>
                                        </p>
                                    </div>
                                </div>
                                @error('dokumen_pendukung')
                                    <span class="text-xs text-rose-500 font-medium mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                                        <span>{{ $message }}</span>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 order-2 sm:order-1">
                                <i class="fa-solid fa-lock text-[11px] text-slate-500 dark:text-slate-400"></i>
                                <span>Data terlindungi dan tersinkronisasi</span>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                                <a href="{{ route('cuti.riwayat') }}"
                                   class="flex-1 sm:flex-none px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white hover:border-slate-400 dark:hover:border-slate-600 text-xs sm:text-sm font-bold transition-all text-center shadow-xs">
                                    Batal
                                </a>
                                <button type="submit"
                                        id="btn-submit"
                                        class="flex-1 sm:flex-none px-7 py-3 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 via-sky-600 to-cyan-600 hover:from-sky-500 hover:to-cyan-500 dark:from-sky-500 dark:via-sky-500 dark:to-cyan-500 dark:hover:from-sky-400 dark:hover:to-cyan-400 text-white text-xs sm:text-sm font-extrabold shadow-md shadow-sky-600/20 hover:shadow-lg hover:shadow-sky-600/30 dark:shadow-sky-500/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Kirim Pengajuan</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: SIDEBAR WIDGETS (4 COLS) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- WIDGET 1: KARTU SALDO CUTI TAHUNAN --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-6 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 relative overflow-hidden transition-all">
                {{-- Ambient Top Glow --}}
                <div class="absolute -top-12 -right-12 w-36 h-36 rounded-full bg-gradient-to-br from-sky-400/20 to-cyan-400/20 blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-500/15 px-2.5 py-1 rounded-full border border-sky-200/70 dark:border-sky-500/30">
                        STATUS KUOTA {{ date('Y') }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Cuti Tahunan</span>
                </div>

                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-4xl font-black text-slate-800 dark:text-slate-100 tracking-tight" id="widget-sisa-saldo">
                            {{ $sisaSaldo }}
                        </span>
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400 ml-1">Hari Kerja</span>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-600/25 dark:shadow-sky-500/30">
                        <i class="fa-solid fa-calendar-days text-base text-white"></i>
                    </div>
                </div>

                {{-- PROGRESS BAR KUOTA --}}
                @php
                    $kuota = $kuotaAwal ?? 12;
                    $terpakai = $cutiTerpakai ?? max(0, $kuota - $sisaSaldo);
                    $persenSisa = $kuota > 0 ? min(100, max(0, round(($sisaSaldo / $kuota) * 100))) : 0;
                @endphp
                <div class="mt-4 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 dark:text-slate-400">
                        <span>Ketersediaan Kuota</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $persenSisa }}%</span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-slate-700/80 overflow-hidden">
                        <div class="h-full rounded-full bg-sky-600 bg-gradient-to-r from-sky-500 to-cyan-500 dark:from-sky-500 dark:to-cyan-400 transition-all duration-500" style="width: {{ $persenSisa }}%;"></div>
                    </div>
                </div>

                {{-- MINI BREAKDOWN STATS --}}
                <div class="mt-5 grid grid-cols-3 gap-2.5 pt-4 border-t border-slate-200/80 dark:border-slate-700 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Total</span>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5 block">{{ $kuota }} H</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Terpakai</span>
                        <span class="text-xs font-black text-rose-600 dark:text-rose-400 mt-0.5 block">{{ $terpakai }} H</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 shadow-2xs">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block uppercase">Pending</span>
                        <span class="text-xs font-black text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $pendingHari ?? 0 }} H</span>
                    </div>
                </div>
            </div>

            {{-- WIDGET 2: RINGKASAN PENGAJUAN REAL-TIME --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-6 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-700 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-receipt text-xs text-sky-600 dark:text-sky-400"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Ringkasan Pengajuan (Live)
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Pemohon</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[160px]">{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Jenis Cuti</span>
                        <span id="preview-jenis-cuti" class="font-bold text-sky-600 dark:text-sky-400">-</span>
                    </div>

                    <div id="row-preview-sub-cuti" class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50" style="display: none;">
                        <span class="text-slate-500 dark:text-slate-400">Sub-Kategori</span>
                        <span id="preview-sub-cuti" class="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">-</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Rentang Tanggal</span>
                        <span id="preview-rentang-tanggal" class="font-semibold text-slate-700 dark:text-slate-300">-</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Hari Kerja Efektif</span>
                        <span id="preview-durasi-hari" class="font-bold text-slate-800 dark:text-slate-100">0 Hari</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-700/50">
                        <span class="text-slate-500 dark:text-slate-400">Efek Pemotongan</span>
                        <span id="preview-efek-saldo" class="font-bold text-emerald-600 dark:text-emerald-400">Bebas Kuota</span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500 dark:text-slate-400">Status Dokumen</span>
                        <span id="preview-status-dokumen" class="font-semibold text-slate-500 dark:text-slate-400">Belum ada file</span>
                    </div>
                </div>
            </div>

            {{-- WIDGET 3: KETENTUAN & PANDUAN CEPAT (POLICY CHECKLIST) --}}
            <div class="bg-gradient-to-br from-slate-50 to-sky-50/50 dark:from-slate-800/80 dark:to-slate-900/60 rounded-3xl border border-sky-200/80 dark:border-slate-700/70 p-6 space-y-3 shadow-sm">
                <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-circle-question text-xs text-sky-600 dark:text-sky-400"></i>
                    </div>
                    <span>Ketentuan Penting HR</span>
                </h4>

                <ul class="space-y-2.5 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Hari Libur:</strong> Akhir pekan dan hari libur nasional otomatis dikecualikan dari pemotongan saldo cuti.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Cuti Sakit:</strong> Wajib melampirkan Surat Keterangan Dokter yang sah dan terbaca jelas.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Notifikasi:</strong> Setelah dikirim, atasan Anda akan menerima tautan persetujuan via WhatsApp secara instan.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 text-xs mt-0.5 shrink-0"></i>
                        <span><strong>Surat Cuti:</strong> Dokumen cetak PDF resmi akan otomatis aktif setelah permohonan disetujui penuh.</span>
                    </li>
                </ul>
            </div>

            {{-- WIDGET 4: PENGAJUAN TERAKHIR ANDA --}}
            @if(isset($recentLeaves) && $recentLeaves->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-xs text-slate-600 dark:text-slate-400"></i>
                            </div>
                            <span>Pengajuan Terakhir</span>
                        </h4>
                        <a href="{{ route('cuti.riwayat') }}" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300">
                            Semua
                        </a>
                    </div>

                    <div class="space-y-2">
                        @foreach($recentLeaves as $rl)
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                <div class="min-w-0 flex-1 pr-2">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $rl->jenisCuti->name_cuti ?? 'Cuti' }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($rl->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($rl->tanggal_selesai)->format('d M Y') }} ({{ $rl->total_hari }} H)
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    @if($rl->status_akhir === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30">
                                            Disetujui
                                        </span>
                                    @elseif($rl->status_akhir === 'rejected')
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
            title: 'PENGAJUAN BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'Buka Riwayat',
            confirmButtonColor: '#0284c7',
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            customClass: {
                popup: `rounded-3xl border ${isDark ? 'border-slate-700 shadow-2xl' : 'border-slate-200 shadow-xl'}`,
                confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 cursor-pointer'
            }
        }).then(() => {
            window.location.href = "{{ route('cuti.riwayat') }}";
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataJenisCuti = @json($jenisCuti);
        const oldJenisCutiId = "{{ old('jenis_cuti_id') }}";
        const oldSubCutiId = "{{ old('sub_cuti_id') }}";

        const userGender = "{{ strtolower(auth()->user()->gender->name ?? auth()->user()->gender ?? '') }}";
        const isPria = (userGender === 'pria' || userGender === 'male' || userGender === '1');

        // SINKRONISASI JADWAL DAN LIBUR NASIONAL
        const scheduleType = "{{ auth()->user()->schedule_type ?? 'reguler_5_hari' }}";
        const rosterOffDates = @json($rosterOffDates ?? []);
        const rawHolidays = @json($holidays ?? []);

        let holidaysList = [];
        let holidayNamesMap = {};

        if (Array.isArray(rawHolidays)) {
            holidaysList = rawHolidays;
        } else if (typeof rawHolidays === 'object' && rawHolidays !== null) {
            holidaysList = Object.keys(rawHolidays);
            holidayNamesMap = rawHolidays;
        }

        // DOM ELEMENTS
        const jenisCutiSelect = document.getElementById('jenis_cuti_id');
        const wrapperSubCuti = document.getElementById('wrapper_sub_cuti');
        const subCutiSelect = document.getElementById('sub_cuti_id');
        const subCutiBadgeRule = document.getElementById('sub-cuti-badge-rule');
        const subCutiInfoNote = document.getElementById('sub-cuti-info-note');
        const subCutiInfoText = document.getElementById('sub-cuti-info-text');

        const infoBadgeJenis = document.getElementById('info-badge-jenis');
        const txtBadgePotongSaldo = document.getElementById('txt-badge-potong-saldo');
        const txtBadgeKuotaInfo = document.getElementById('txt-badge-kuota-info');

        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalSelesai = document.getElementById('tanggal_selesai');
        const labelAlasan = document.getElementById('label-alasan');
        const alasanCuti = document.getElementById('alasan_cuti');
        const charCounter = document.getElementById('char-counter');

        const labelDokumen = document.getElementById('label-dokumen');
        const inputDokumen = document.getElementById('input-dokumen');
        const badgeWajibLampiran = document.getElementById('badge-wajib-lampiran');
        const dropzonePlaceholder = document.getElementById('dropzone-placeholder');
        const dropzonePreview = document.getElementById('dropzone-preview');
        const previewFilename = document.getElementById('preview-filename');
        const previewFilesize = document.getElementById('preview-filesize');
        const previewFileIcon = document.getElementById('preview-file-icon');
        const btnHapusFile = document.getElementById('btn-hapus-file');

        const liveCalcBox = document.getElementById('live-calculator-box');
        const badgeTotalHariKerja = document.getElementById('badge-total-hari-kerja');
        const keteranganSkipLibur = document.getElementById('keterangan-skip-libur');

        // PREVIEW WIDGET ELEMENTS
        const previewJenisCuti = document.getElementById('preview-jenis-cuti');
        const rowPreviewSubCuti = document.getElementById('row-preview-sub-cuti');
        const previewSubCuti = document.getElementById('preview-sub-cuti');
        const previewRentangTanggal = document.getElementById('preview-rentang-tanggal');
        const previewDurasiHari = document.getElementById('preview-durasi-hari');
        const previewEfekSaldo = document.getElementById('preview-efek-saldo');
        const previewStatusDokumen = document.getElementById('preview-status-dokumen');

        const sisaSaldoCutiTahunan = parseInt('{{ $sisaSaldo ?? 0 }}', 10);
        const tombolSubmit = document.getElementById('btn-submit');
        const pesanErrorSaldo = document.getElementById('pesan-error-saldo');
        const pesanErrorSaldoText = document.getElementById('pesan-error-saldo-text');
        const form = document.getElementById('formCuti');

        let isConfirmed = false;

        // HELPER THEME-AWARE SWEETALERT CONFIGURATION
        function getSwalTheme(custom = {}) {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#0284c7',
                cancelButtonColor: isDark ? '#475569' : '#94a3b8',
                customClass: {
                    popup: `rounded-3xl border ${isDark ? 'border-slate-700 shadow-2xl' : 'border-slate-200 shadow-xl'}`,
                    confirmButton: 'px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-sky-500/20 cursor-pointer',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm cursor-pointer',
                    ...(custom.customClass || {})
                },
                ...custom
            };
        }

        // FUNGSI MEMERIKSA APAKAH TANGGAL TERMASUK HARI LIBUR
        function getKeteranganLibur(dateString) {
            if (!dateString) return null;

            const parts = dateString.split('-');
            const dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
            const dayOfWeek = dateObj.getDay(); // 0 = Minggu, 6 = Sabtu

            if (scheduleType === 'reguler_6_hari') {
                if (dayOfWeek === 0) return "Hari Minggu (Libur Kerja)";
                if (holidaysList.includes(dateString)) {
                    return holidayNamesMap[dateString] ? `Libur Nasional: ${holidayNamesMap[dateString]}` : "Hari Libur Nasional";
                }
            } else if (scheduleType === 'roster') {
                if (rosterOffDates.includes(dateString)) {
                    return "Jadwal OFF Roster (Libur Shift)";
                }
            } else {
                // Default: reguler_5_hari
                if (dayOfWeek === 0) return "Hari Minggu (Libur Kerja)";
                if (dayOfWeek === 6) return "Hari Sabtu (Libur Kerja)";
                if (holidaysList.includes(dateString)) {
                    return holidayNamesMap[dateString] ? `Libur Nasional: ${holidayNamesMap[dateString]}` : "Hari Libur Nasional";
                }
            }

            return null;
        }

        // VALIDASI POPUP SAAT USER MEMILIH HARI LIBUR
        function validasiTanggalLibur(inputElem, fieldLabel) {
            const dateValue = inputElem.value;
            if (!dateValue) return true;

            const ketLibur = getKeteranganLibur(dateValue);
            if (ketLibur) {
                Swal.fire(getSwalTheme({
                    icon: 'warning',
                    title: 'Pilihan Tanggal Libur!',
                    html: `Tanggal yang Anda pilih (<b class="text-sky-600 dark:text-sky-400">${dateValue}</b>) bertepatan dengan <b class="text-rose-500 dark:text-rose-400">${ketLibur}</b>.<br><br>Silakan pilih tanggal pada hari dinas / kerja aktif Anda!`,
                    confirmButtonText: 'Pahami & Ganti Tanggal'
                }));

                inputElem.value = '';
                hitungDanUpdateDurasi();
                return false;
            }
            return true;
        }

        // BUKA KALENDER SAAT INPUT DIKLIK
        [tanggalMulai, tanggalSelesai].forEach(input => {
            input.addEventListener('click', function() {
                if (typeof this.showPicker === 'function') {
                    this.showPicker();
                }
            });
        });

        // HITUNG HARI KERJA EFEKTIF CLIENT-SIDE
        function hitungHariKerjaEfektifClient(startStr, endStr) {
            if (!startStr || !endStr) return 0;
            if (startStr > endStr) return 0;

            let totalHariKerja = 0;
            let current = new Date(startStr);
            const end = new Date(endStr);

            while (current <= end) {
                const yyyy = current.getFullYear();
                const mm = String(current.getMonth() + 1).padStart(2, '0');
                const dd = String(current.getDate()).padStart(2, '0');
                const dateStr = `${yyyy}-${mm}-${dd}`;

                const ketLibur = getKeteranganLibur(dateStr);
                if (!ketLibur) {
                    totalHariKerja++;
                }

                current.setDate(current.getDate() + 1);
            }

            return totalHariKerja;
        }

        // EVALUASI APAKAH JENIS CUTI MEMOTONG SALDO TAHUNAN
        function apakahMemotongSaldoTahunan() {
            const optionTerpilih = jenisCutiSelect.options[jenisCutiSelect.selectedIndex];
            if (!optionTerpilih || !optionTerpilih.value) return false;

            const namaCuti = (optionTerpilih.getAttribute('data-nama-cuti') || '').toLowerCase().trim();
            const kodeCuti = (optionTerpilih.getAttribute('data-kode-cuti') || '').toUpperCase().trim();

            const isCutiTahunan = (kodeCuti === 'CT') || (namaCuti === 'cuti') || namaCuti.includes('tahunan');
            if (!isCutiTahunan) return false;

            // Jika ada sub-cuti khusus, periksa pengecualian
            const subTerpilih = subCutiSelect.options[subCutiSelect.selectedIndex];
            if (subTerpilih && subTerpilih.value) {
                const namaSub = (subTerpilih.getAttribute('data-nama-sub') || subTerpilih.textContent || '').toLowerCase();
                const pengecualian = ['haid', 'sakit', 'ibadah', 'haji', 'umroh', 'nikah', 'lahir', 'duka', 'kematian'];
                for (const k of pengecualian) {
                    if (namaSub.includes(k)) return false;
                }
            }

            return true;
        }

        // UPDATE TAMPILAN LIVE SUMMARY & KALKULATOR
        function hitungDanUpdateDurasi() {
            const m = tanggalMulai.value;
            const s = tanggalSelesai.value;

            if (m && s && s >= m) {
                const totalHariKerja = hitungHariKerjaEfektifClient(m, s);
                badgeTotalHariKerja.textContent = `${totalHariKerja} Hari Kerja`;
                previewDurasiHari.textContent = `${totalHariKerja} Hari Kerja`;
                previewRentangTanggal.textContent = `${formatTanggalID(m)} - ${formatTanggalID(s)}`;

                liveCalcBox.style.display = 'flex';

                const partsM = m.split('-');
                const partsS = s.split('-');
                const d1 = new Date(partsM[0], partsM[1] - 1, partsM[2]);
                const d2 = new Date(partsS[0], partsS[1] - 1, partsS[2]);
                const totalKalender = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                const liburTerlewati = totalKalender - totalHariKerja;

                if (liburTerlewati > 0) {
                    keteranganSkipLibur.innerHTML = `Dari total ${totalKalender} hari, <b class="text-sky-600 dark:text-sky-400">${liburTerlewati} hari libur</b> otomatis dikecualikan.`;
                } else {
                    keteranganSkipLibur.textContent = 'Seluruh hari masuk dalam jam kerja dinas aktif.';
                }

                // Cek efek saldo
                if (apakahMemotongSaldoTahunan()) {
                    const sisaSetelah = sisaSaldoCutiTahunan - totalHariKerja;
                    if (sisaSetelah < 0) {
                        previewEfekSaldo.innerHTML = `<span class="text-rose-500 dark:text-rose-400 font-bold">Kurang (${sisaSetelah} Hari)</span>`;
                    } else {
                        previewEfekSaldo.innerHTML = `<span class="text-amber-600 dark:text-amber-400 font-bold">-${totalHariKerja} Hari (Sisa ${sisaSetelah} H)</span>`;
                    }
                } else {
                    previewEfekSaldo.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold">Bebas Kuota</span>`;
                }
            } else {
                liveCalcBox.style.display = 'none';
                previewDurasiHari.textContent = '0 Hari';
                previewRentangTanggal.textContent = m ? `${formatTanggalID(m)} - ...` : '-';
            }

            periksaSaldo();
        }

        function formatTanggalID(dateStr) {
            if (!dateStr) return '-';
            const [y, m, d] = dateStr.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${parseInt(d)} ${months[parseInt(m) - 1]} ${y}`;
        }

        // HANDLER CHANGE INPUT TANGGAL
        tanggalMulai.addEventListener('change', function() {
            if (validasiTanggalLibur(this, 'Tanggal Mulai')) {
                if (this.value) {
                    tanggalSelesai.min = this.value;
                    if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
                        tanggalSelesai.value = this.value;
                    }
                    batasiKalenderSelesai();
                }
                hitungDanUpdateDurasi();
            }
        });

        tanggalSelesai.addEventListener('change', function() {
            if (validasiTanggalLibur(this, 'Tanggal Selesai')) {
                hitungDanUpdateDurasi();
            }
        });

        // PRESET CHIPS DURATION BUTTONS (+1, +2, +3 HARI)
        document.querySelectorAll('.btn-preset-durasi').forEach(btn => {
            btn.addEventListener('click', function() {
                const daysToAdd = parseInt(this.getAttribute('data-days')) || 1;
                let startDateVal = tanggalMulai.value;

                if (!startDateVal) {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    startDateVal = `${y}-${m}-${d}`;
                    tanggalMulai.value = startDateVal;
                    validasiTanggalLibur(tanggalMulai, 'Tanggal Mulai');
                }

                if (tanggalMulai.value) {
                    let [y, m, d] = tanggalMulai.value.split('-').map(Number);
                    let targetDate = new Date(y, m - 1, d);
                    targetDate.setDate(targetDate.getDate() + (daysToAdd - 1));

                    const yTarget = targetDate.getFullYear();
                    const mTarget = String(targetDate.getMonth() + 1).padStart(2, '0');
                    const dTarget = String(targetDate.getDate()).padStart(2, '0');

                    tanggalSelesai.value = `${yTarget}-${mTarget}-${dTarget}`;
                    batasiKalenderSelesai();
                    validasiTanggalLibur(tanggalSelesai, 'Tanggal Selesai');
                    hitungDanUpdateDurasi();
                }
            });
        });

        // QUICK REASON CHIPS
        document.querySelectorAll('.btn-quick-reason').forEach(btn => {
            btn.addEventListener('click', function() {
                const txt = this.getAttribute('data-text');
                if (alasanCuti.value.trim() === '') {
                    alasanCuti.value = txt;
                } else {
                    alasanCuti.value = alasanCuti.value + ' ' + txt;
                }
                updateCharCounter();
            });
        });

        // CHARACTER COUNTER
        function updateCharCounter() {
            const len = alasanCuti.value.length;
            charCounter.textContent = `${len} / 500 karakter`;
        }
        alasanCuti.addEventListener('input', updateCharCounter);
        updateCharCounter();

        // DRAG & DROP FILE HANDLING
        const dropzoneArea = document.getElementById('dropzone-area');

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

        inputDokumen.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleFileSelected(this.files[0]);
            } else {
                resetFilePreview();
            }
        });

        function handleFileSelected(file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire(getSwalTheme({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal ukuran file dokumen pendukung adalah 2MB. Silakan kompres atau pilih file lain.'
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
            previewStatusDokumen.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-bold truncate max-w-[140px] inline-block">${file.name}</span>`;
        }

        function resetFilePreview() {
            inputDokumen.value = '';
            dropzonePlaceholder.style.display = 'block';
            dropzonePreview.style.display = 'none';
            previewStatusDokumen.textContent = 'Belum ada file';
        }

        if (btnHapusFile) {
            btnHapusFile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                resetFilePreview();
            });
        }

        // VALIDASI SALDO
        function periksaSaldo() {
            const potongSaldo = apakahMemotongSaldoTahunan();
            const totalHari = hitungHariKerjaEfektifClient(tanggalMulai.value, tanggalSelesai.value);

            if (potongSaldo && sisaSaldoCutiTahunan <= 0) {
                pesanErrorSaldoText.textContent = 'Sisa kuota Cuti Tahunan Anda saat ini telah habis (0 hari). Pengajuan cuti yang memotong kuota tahunan tidak dapat dilanjutkan.';
                pesanErrorSaldo.style.display = 'flex';
                tombolSubmit.disabled = true;
                tombolSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            if (potongSaldo && totalHari > sisaSaldoCutiTahunan && totalHari > 0) {
                pesanErrorSaldoText.textContent = `Permohonan Anda (${totalHari} hari kerja) melebihi sisa kuota yang tersedia (${sisaSaldoCutiTahunan} hari). Silakan sesuaikan durasi cuti Anda.`;
                pesanErrorSaldo.style.display = 'flex';
                tombolSubmit.disabled = true;
                tombolSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            pesanErrorSaldo.style.display = 'none';
            tombolSubmit.disabled = false;
            tombolSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        // HANDLER PERUBAHAN JENIS CUTI
        function handleJenisCutiChange(selectedId, isInitialLoad = false) {
            const optionTerpilih = jenisCutiSelect.options[jenisCutiSelect.selectedIndex];
            const namaCuti = optionTerpilih ? (optionTerpilih.getAttribute('data-nama-cuti') || '') : '';
            const kodeCuti = optionTerpilih ? (optionTerpilih.getAttribute('data-kode-cuti') || '') : '';

            previewJenisCuti.textContent = namaCuti || '-';

            // Update badge info
            if (optionTerpilih && optionTerpilih.value) {
                infoBadgeJenis.style.display = 'flex';
                const isTahunan = (kodeCuti === 'CT') || (namaCuti.toLowerCase() === 'cuti') || namaCuti.toLowerCase().includes('tahunan');
                if (isTahunan) {
                    txtBadgePotongSaldo.textContent = 'Memotong Kuota Tahunan';
                    txtBadgePotongSaldo.parentElement.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30';
                } else {
                    txtBadgePotongSaldo.textContent = 'Izin Khusus (Bebas Kuota)';
                    txtBadgePotongSaldo.parentElement.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-500/30';
                }
            } else {
                infoBadgeJenis.style.display = 'none';
            }

            if (namaCuti === 'Cuti') {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-rose-500 font-bold">*</span>';
                alasanCuti.setAttribute('required', 'required');
            } else {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional)</span>';
                alasanCuti.removeAttribute('required');
            }

            subCutiSelect.innerHTML = '<option value="" disabled selected hidden>-- Pilih Detail Perizinan / Sub-Cuti --</option>';
            const jenisTerpilih = dataJenisCuti.find(item => item.id == selectedId);

            if (jenisTerpilih) {
                let subCutis = jenisTerpilih.sub_cutis || jenisTerpilih.subCutis || [];

                if (isPria) {
                    subCutis = subCutis.filter(sub => {
                        const namaLower = sub.nama_sub_cuti.toLowerCase();
                        if (namaLower.includes('haid') || namaLower.includes('bersalin')) return false;
                        if (namaLower.includes('melahirkan') && !namaLower.includes('istri')) return false;
                        return true;
                    });
                } else {
                    subCutis = subCutis.filter(sub => {
                        const namaLower = sub.nama_sub_cuti.toLowerCase();
                        return !namaLower.includes('istri melahirkan');
                    });
                }

                if (subCutis.length > 0) {
                    wrapperSubCuti.style.display = 'block';
                    rowPreviewSubCuti.style.display = 'flex';
                    subCutiSelect.setAttribute('required', 'required');

                    subCutis.forEach(function (sub) {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = `${sub.nama_sub_cuti} ${sub.durasi_default ? '(' + sub.durasi_default + ' Hari)' : ''}`;
                        option.setAttribute('data-nama-sub', sub.nama_sub_cuti);
                        option.setAttribute('data-durasi', sub.durasi_default || '');
                        option.setAttribute('data-wajib-dokumen', sub.apakah_wajib_dokumen ? '1' : '0');

                        if (isInitialLoad && String(oldSubCutiId) === String(sub.id)) {
                            option.selected = true;
                        }

                        subCutiSelect.appendChild(option);
                    });

                    if (isInitialLoad && oldSubCutiId) {
                        subCutiSelect.value = oldSubCutiId;
                    }

                    checkDokumenRequirement();
                    batasiKalenderSelesai();
                    hitungDanUpdateDurasi();
                    return;
                }
            }

            wrapperSubCuti.style.display = 'none';
            rowPreviewSubCuti.style.display = 'none';
            subCutiSelect.removeAttribute('required');
            subCutiSelect.value = '';
            previewSubCuti.textContent = '-';
            resetStatusDokumen();
            batasiKalenderSelesai();
            hitungDanUpdateDurasi();
        }

        // CEK KEWAJIBAN DOKUMEN PENDUKUNG
        function checkDokumenRequirement() {
            const selectedIdx = subCutiSelect.selectedIndex;
            const optionTerpilih = selectedIdx >= 0 ? subCutiSelect.options[selectedIdx] : null;

            if (optionTerpilih && optionTerpilih.value && optionTerpilih.value !== "") {
                const namaSubCuti = (optionTerpilih.getAttribute('data-nama-sub') || optionTerpilih.textContent).toLowerCase().trim();
                const valWajib = optionTerpilih.getAttribute('data-wajib-dokumen');
                const durasi = optionTerpilih.getAttribute('data-durasi');

                previewSubCuti.textContent = optionTerpilih.getAttribute('data-nama-sub') || optionTerpilih.textContent;

                if (durasi) {
                    subCutiBadgeRule.textContent = `Maks. ${durasi} Hari`;
                    subCutiInfoNote.style.display = 'flex';
                    subCutiInfoText.textContent = `Batas permohonan untuk keperluan ini maksimal ${durasi} hari kerja.`;
                } else {
                    subCutiBadgeRule.textContent = 'Sesuai Pengajuan';
                    subCutiInfoNote.style.display = 'none';
                }

                if (namaSubCuti.includes('sakit') || valWajib === '1' || valWajib === 'true') {
                    labelDokumen.innerHTML = 'Dokumen Pendukung <span class="text-rose-500 font-bold">* (Wajib Lampiran Surat Keterangan / Medis)</span>';
                    badgeWajibLampiran.textContent = 'Wajib Diunggah';
                    badgeWajibLampiran.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/80 dark:border-rose-500/30 uppercase';
                    inputDokumen.required = true;
                } else {
                    resetStatusDokumen();
                }
            } else {
                previewSubCuti.textContent = '-';
                subCutiBadgeRule.textContent = 'Pilih Kriteria';
                subCutiInfoNote.style.display = 'none';
                resetStatusDokumen();
            }
        }

        // BATASI KALENDER SELESAI
        function batasiKalenderSelesai() {
            const sekarang = new Date();
            const yyyy = sekarang.getFullYear();
            const mm = String(sekarang.getMonth() + 1).padStart(2, '0');
            const dd = String(sekarang.getDate()).padStart(2, '0');
            const hariIniLokal = `${yyyy}-${mm}-${dd}`;

            if (tanggalMulai.value) {
                tanggalSelesai.min = tanggalMulai.value;
            } else {
                tanggalSelesai.min = hariIniLokal;
            }

            const selectedOption = subCutiSelect.options[subCutiSelect.selectedIndex];
            if (!selectedOption || selectedOption.value === "") {
                tanggalSelesai.removeAttribute('max');
                return;
            }

            const durasi = selectedOption.getAttribute('data-durasi');
            if (!durasi || durasi === '') {
                tanggalSelesai.removeAttribute('max');
            } else {
                if (tanggalMulai.value) {
                    const maxDays = parseInt(durasi);
                    let [y, m, d] = tanggalMulai.value.split('-').map(Number);
                    let dateMulai = new Date(y, m - 1, d);

                    dateMulai.setDate(dateMulai.getDate() + (maxDays - 1));

                    const yyyyMax = dateMulai.getFullYear();
                    const mmMax = String(dateMulai.getMonth() + 1).padStart(2, '0');
                    const ddMax = String(dateMulai.getDate()).padStart(2, '0');
                    const maxDateString = `${yyyyMax}-${mmMax}-${ddMax}`;

                    tanggalSelesai.max = maxDateString;

                    if (tanggalSelesai.value && tanggalSelesai.value > maxDateString) {
                        tanggalSelesai.value = '';
                    }
                }
            }
        }

        function resetStatusDokumen() {
            if (labelDokumen && inputDokumen) {
                labelDokumen.innerHTML = 'Dokumen Pendukung <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Opsional)</span>';
                badgeWajibLampiran.textContent = 'Opsional';
                badgeWajibLampiran.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 uppercase';
                inputDokumen.required = false;
            }
        }

        // EVENT LISTENERS
        jenisCutiSelect.addEventListener('change', function () {
            handleJenisCutiChange(this.value, false);
            hitungDanUpdateDurasi();
        });

        subCutiSelect.addEventListener('change', function() {
            checkDokumenRequirement();
            batasiKalenderSelesai();
            hitungDanUpdateDurasi();
        });

        // POPUP KONFIRMASI SEBELUM SUBMIT
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!isConfirmed) {
                    e.preventDefault();

                    const durasiHari = hitungHariKerjaEfektifClient(tanggalMulai.value, tanggalSelesai.value);
                    if (durasiHari <= 0) {
                        Swal.fire(getSwalTheme({
                            icon: 'error',
                            title: 'Rentang Tanggal Tidak Valid',
                            text: 'Seluruh tanggal pada rentang yang Anda pilih bertepatan dengan hari libur kerja atau tidak ada hari kerja aktif.'
                        }));
                        return;
                    }

                    if (getKeteranganLibur(tanggalMulai.value) || getKeteranganLibur(tanggalSelesai.value)) {
                        Swal.fire(getSwalTheme({
                            icon: 'error',
                            title: 'Pilihan Tanggal Tidak Valid',
                            text: 'Tanggal mulai atau selesai jatuh tepat pada hari libur kerja!',
                            confirmButtonColor: '#e11d48'
                        }));
                        return;
                    }

                    const jenisNama = jenisCutiSelect.options[jenisCutiSelect.selectedIndex]?.text || '';
                    const isDark = document.documentElement.classList.contains('dark');

                    Swal.fire(getSwalTheme({
                        title: 'Konfirmasi Pengajuan Cuti',
                        html: `
                            <div class="text-left text-xs ${isDark ? 'text-slate-200 bg-slate-900/80 border-slate-700' : 'text-slate-700 bg-slate-50 border-slate-200'} space-y-2 p-3.5 rounded-2xl border">
                                <div><span class="text-slate-400 font-medium">Jenis:</span> <strong class="${isDark ? 'text-slate-100' : 'text-slate-800'}">${jenisNama}</strong></div>
                                <div><span class="text-slate-400 font-medium">Periode:</span> <strong class="${isDark ? 'text-slate-100' : 'text-slate-800'}">${formatTanggalID(tanggalMulai.value)} s/d ${formatTanggalID(tanggalSelesai.value)}</strong></div>
                                <div><span class="text-slate-400 font-medium">Total Efektif:</span> <span class="font-extrabold text-sky-600 dark:text-sky-400">${durasiHari} Hari Kerja</span></div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 font-medium">Apakah seluruh informasi di atas sudah benar dan siap dikirimkan ke atasan?</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim Pengajuan',
                        cancelButtonText: 'Periksa Kembali'
                    })).then((result) => {
                        if (result.isConfirmed) {
                            isConfirmed = true;
                            tombolSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Mengirim...</span>';
                            tombolSubmit.disabled = true;
                            form.submit();
                        }
                    });
                }
            });
        }

        // INITIAL LOAD
        batasiKalenderSelesai();
        if (oldJenisCutiId) {
            handleJenisCutiChange(oldJenisCutiId, true);
        }
        if (tanggalMulai.value && tanggalSelesai.value) {
            hitungDanUpdateDurasi();
        }
        periksaSaldo();
    });
</script>
@endpush
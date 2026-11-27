@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- BANNER SISTEM PERINGATAN KELENGKAPAN AKUN (ACCOUNT READINESS GUARD) --}}
    @php
        $accStatus = auth()->user()->getAccountCompletionStatus();
        $completedCount = collect([
            $accStatus['email_verified'],
            $accStatus['signature_set'],
            $accStatus['schedule_set'],
        ])->filter()->count();
        $percentComplete = round(($completedCount / 3) * 100);
    @endphp

    @if(!$accStatus['is_complete'])
        <div class="p-5 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-rose-500/10 border border-amber-300 dark:border-amber-700/60 rounded-3xl shadow-sm backdrop-blur-md transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-amber-200/60 dark:border-amber-800/50 pb-4">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shadow-md shadow-amber-500/20 shrink-0 mt-0.5">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base">Peringatan Kelengkapan Akun Karyawan</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-300">
                                {{ $completedCount }}/3 Syarat Selesai ({{ $percentComplete }}%)
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed max-w-2xl">
                            Akses pembuatan formulir pengajuan (Cuti, CAR, & MPR) <strong>terkunci</strong> sampai seluruh 3 syarat kepegawaian di bawah ini diverifikasi dan dilengkapi.
                        </p>
                    </div>
                </div>

                {{-- Progress Indicator Bar --}}
                <div class="w-full lg:w-56 shrink-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Progress Profil</span>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $percentComplete }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2.5 overflow-hidden mt-1 shadow-inner">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $percentComplete }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- 3 Checklist Items Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4">
                {{-- 1. Verifikasi Email --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['email_verified'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-envelope text-base {{ $accStatus['email_verified'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Verifikasi Email</span>
                            </div>
                            @if($accStatus['email_verified'])
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['email_verified'] ? 'Alamat email aktif & valid' : 'Tautan konfirmasi email belum dibuka' }}
                        </div>
                    </div>
                    @if(!$accStatus['email_verified'])
                        <a href="{{ route('verification.notice') }}" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs">
                            Belum Verifikasi
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-check text-xs"></i> Terverifikasi
                        </span>
                    @endif
                </div>

                {{-- 2. Tanda Tangan Digital (TTD) --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['signature_set'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-file-signature text-base {{ $accStatus['signature_set'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Tanda Tangan Digital</span>
                            </div>
                            @if($accStatus['signature_set'])
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['signature_set'] ? 'Tanda tangan digital tersimpan' : 'Tanda tangan belum diunggah' }}
                        </div>
                    </div>
                    @if(!$accStatus['signature_set'])
                        <a href="{{ url('/profile?signature_required=1#signature') }}" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs">
                            Unggah TTD
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-check text-xs"></i> Sudah Ada TTD
                        </span>
                    @endif
                </div>

                {{-- 3. Jadwal Kerja --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['schedule_set'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-calendar-week text-base {{ $accStatus['schedule_set'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Jadwal Kerja</span>
                            </div>
                            @if($accStatus['schedule_set'])
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['schedule_set'] ? auth()->user()->schedule_label : 'Jadwal kerja belum diatur' }}
                        </div>
                    </div>
                    @if(!$accStatus['schedule_set'])
                        <a href="{{ url('/profile?schedule_required=1#schedule_setting') }}" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs">
                            Belum Diatur
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-check text-xs"></i> Jadwal Aktif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Hero Operasional & Aksi Cepat --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-900 via-sky-800 to-cyan-800 dark:from-slate-900 dark:via-sky-950 dark:to-slate-900 text-white p-5 sm:p-6 border border-sky-700/40 dark:border-sky-800/40 shadow-lg shadow-sky-950/15">
        <div class="absolute -right-12 -top-12 w-52 h-52 rounded-full bg-cyan-400/15 blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-14 w-44 h-44 rounded-full bg-sky-300/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/15 border border-white/20 text-cyan-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>{{ auth()->user()->station->name ?? 'Stasiun Umbulan' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/10 border border-white/15 text-sky-100">
                        <i class="fa-solid fa-clock text-[10px] text-cyan-300"></i>
                        <span>{{ auth()->user()->schedule_label ?? ($user->schedule_type === 'roster' ? 'Sistem Roster' : 'Jadwal Normal') }}</span>
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                    Selamat Datang, {{ auth()->user()->name }}
                </h2>
                <p class="text-xs sm:text-sm text-sky-100/85 max-w-2xl leading-relaxed">
                    Kelola pengajuan cuti, permintaan dana operasional (CAR), pengadaan material (MPR), serta pantau jadwal kerja Anda dalam satu ringkasan terpadu.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ url('/cuti/ajukan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-sky-900 hover:bg-sky-50 font-extrabold text-xs shadow-md shadow-slate-950/15 active:scale-95 transition-all">
                    <i class="fa-solid fa-calendar-plus text-sky-600"></i>
                    <span>Ajukan Cuti</span>
                </a>
                <a href="{{ url('/car/ajukan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-500/20 hover:bg-teal-500/30 border border-teal-300/35 text-white font-bold text-xs backdrop-blur-xs active:scale-95 transition-all">
                    <i class="fa-solid fa-file-invoice-dollar text-teal-200"></i>
                    <span>Ajukan CAR</span>
                </a>
                <a href="{{ url('/mpr/ajukan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-500/25 hover:bg-indigo-500/35 border border-indigo-300/35 text-white font-bold text-xs backdrop-blur-xs active:scale-95 transition-all">
                    <i class="fa-solid fa-boxes-packing text-indigo-200"></i>
                    <span>Ajukan MPR</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Statistik Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $isPerempuan ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4">
        <div class="bg-white dark:bg-slate-800/90 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs flex items-center space-x-4 transition-colors">
            <div class="p-3 bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 rounded-xl">
                <i class="fa-solid fa-calendar-days text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-400 font-medium uppercase tracking-wider">Hak Cuti Tahunan</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $kuotaTahunan }} Hari</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800/90 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs flex items-center space-x-4 transition-colors">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <i class="fa-solid fa-umbrella-beach text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-400 font-medium uppercase tracking-wider">Cuti Telah Diambil</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $totalCutiDiambil }} Hari</h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800/90 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs flex items-center space-x-4 transition-colors">
            <div class="p-3 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl">
                <i class="fa-solid fa-hourglass-half text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-400 font-medium uppercase tracking-wider">Menunggu Review</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                    {{ $totalPending }} Pengajuan
                </h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800/90 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs flex items-center space-x-4 transition-colors">
            <div class="p-3 bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 rounded-xl">
                <i class="fa-solid fa-circle-check text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-400 font-medium uppercase tracking-wider">Sisa Kuota Cuti</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $sisaKuota }} Hari</h3>
            </div>
        </div>

        @if($isPerempuan)
        <div class="bg-white dark:bg-slate-800/90 p-5 rounded-2xl border border-rose-100 dark:border-rose-900/40 shadow-xs flex items-center space-x-4 transition-colors">
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl">
                <i class="fa-solid fa-person-dress text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-400 font-medium uppercase tracking-wider">Saldo Cuti Haid (Bulan Ini)</p>
                <h3 class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-0.5">{{ $saldoCutiHaid }} Hari</h3>
            </div>
        </div>
        @endif
    </div>

    {{-- Widget Jadwal Kerja Hari Ini --}}
    <div class="bg-white dark:bg-slate-800/90 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs mb-6 transition-colors">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Status Operasional Hari Ini</span>

                    @if($user->schedule_type === 'roster')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                            Sistem Roster
                        </span>
                    @elseif($user->schedule_type === 'normal' || $user->schedule_type === 'reguler_5_hari')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800/50">
                            Jadwal Normal
                        </span>
                    @endif
                </div>

                <div class="flex items-center space-x-3 mt-1">
                    @php
                        $isWorkingNow = app(App\Services\ScheduleService::class)->isUserWorkingNow($user);
                    @endphp

                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                        @if(is_null($user->schedule_type))
                            <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Jadwal Kerja Kosong
                            </span>
                        @elseif($user->schedule_type === 'roster')
                            @if(isset($todaySchedule['is_day_off']) && $todaySchedule['is_day_off'])
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif(isset($todaySchedule['shift_type']) && $todaySchedule['shift_type'] === 'pagi')
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Pagi <span class="{{ $isWorkingNow ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Di Luar Jam Shift' }})</span>
                                </span>
                            @else
                                <span class="text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-indigo-600 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Malam <span class="{{ $isWorkingNow ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Di Luar Jam Shift' }})</span>
                                </span>
                            @endif
                        @else
                            @if(isset($todaySchedule['is_day_off']) && $todaySchedule['is_day_off'])
                                <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif($isWorkingNow)
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> Sedang Bekerja
                                </span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Di Luar Jam Kerja
                                </span>
                            @endif
                        @endif
                    </h3>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                    @if(is_null($user->schedule_type))
                        Harap atur jadwal kerja Anda melalui menu pengaturan profil agar hak cuti & kalender aktif.
                    @elseif(isset($todaySchedule['is_day_off']) && !$todaySchedule['is_day_off'])
                        Ketentuan Jam Kerja: <strong class="text-slate-700 dark:text-slate-200">{{ $todaySchedule['scheduled_in'] ?? '--:--' }} - {{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB</strong>
                    @else
                        Jadwal Anda hari ini berstatus <strong>OFF (Libur)</strong> berdasarkan kalender kerja & SKB 3 Menteri.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if(is_null($user->schedule_type))
                    <a href="{{ url('/profile?schedule_required=1#schedule_setting') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Atur Jadwal Kerja</span>
                    </a>
                @elseif($accStatus['is_complete'])
                    <a href="{{ route('cuti.create') }}" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Ajukan Cuti</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Widget Kalender Aktivitas Jadwal --}}
    <div class="relative bg-white dark:bg-slate-800/95 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs hover:shadow-sm mb-6 transition-all overflow-hidden">
        {{-- Subtle decorative ambient background glow --}}
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-blue-500/5 dark:bg-blue-400/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-indigo-500/5 dark:bg-indigo-400/10 blur-3xl pointer-events-none"></div>

        <div class="relative p-5 sm:p-6 lg:p-7 space-y-5">
            {{-- Header Kalender & Navigasi Bulan --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/70 pb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-md shadow-blue-500/10 dark:shadow-blue-500/20 shrink-0 ring-4 ring-blue-500/10 dark:ring-blue-400/20 border border-blue-200/60 dark:border-blue-800/60">
                        <i class="fa-solid fa-calendar-days text-lg sm:text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base sm:text-lg tracking-tight">Kalender Jadwal Kerja & Aktivitas</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                {{ $user->schedule_type === 'roster' ? 'Sistem Roster 12 Jam' : 'Sistem Reguler' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                            <span>Visualisasi jadwal shift, libur nasional, dan histori cuti Anda.</span>
                        </p>
                    </div>
                </div>

                {{-- Kontrol Navigasi Bulan --}}
                <div class="flex items-center gap-1.5 sm:gap-2 self-start lg:self-auto bg-slate-50 dark:bg-slate-900/80 p-1 sm:p-1.5 rounded-2xl border border-slate-200/70 dark:border-slate-700/70 shadow-2xs">
                    @php
                        $prevMonth = $currentCarbonDate->copy()->subMonth();
                        $nextMonth = $currentCarbonDate->copy()->addMonth();
                        $isCurrentMonth = ($currentCarbonDate->format('Y-m') === now()->format('Y-m'));
                    @endphp
                    <a href="{{ route('dashboard', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" 
                       title="Bulan Sebelumnya"
                       class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl shadow-2xs transition-all active:scale-95 border border-slate-200/60 dark:border-slate-700/60">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </a>

                    <div class="px-3 sm:px-4 py-1.5 flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-xs text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 min-w-[110px] sm:min-w-[130px] text-center tracking-tight">
                            {{ $currentCarbonDate->isoFormat('MMMM YYYY') }}
                        </span>
                    </div>

                    <a href="{{ route('dashboard', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" 
                       title="Bulan Berikutnya"
                       class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl shadow-2xs transition-all active:scale-95 border border-slate-200/60 dark:border-slate-700/60">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>

                    @if(!$isCurrentMonth)
                        <a href="{{ route('dashboard', ['month' => now()->month, 'year' => now()->year]) }}"
                           title="Kembali ke Bulan Ini"
                           class="px-2.5 py-1 text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-2xs transition-all active:scale-95 flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left text-[10px]"></i>
                            <span class="hidden sm:inline">Bulan Ini</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Keterangan Indikator (Legend Bar) --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800/80 p-3 sm:p-3.5 rounded-2xl">
                <span class="text-slate-400 dark:text-slate-400 font-extrabold text-[11px] uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                    <span>Keterangan:</span>
                </span>

                @if($user->schedule_type === 'roster')
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Shift Pagi</span>
                        <span class="text-[10px] text-slate-500 font-mono hidden sm:inline">(07:00 - 19:00)</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shadow-[0_0_8px_rgba(79,70,229,0.5)]"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Shift Malam</span>
                        <span class="text-[10px] text-slate-500 font-mono hidden sm:inline">(19:00 - 07:00)</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Libur Roster (Off)</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Hari Kerja Aktif</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Libur / Tanggal Merah</span>
                    </div>
                @endif

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.5)]"></span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200 text-xs">Histori Cuti</span>
                </div>
            </div>

            {{-- Grid Kalender Matriks Harian --}}
            <div class="grid grid-cols-7 sm:grid-cols-10 md:grid-cols-11 lg:grid-cols-16 gap-2 sm:gap-2.5 pt-2">
                @foreach($calendarDays as $day)
                    @php
                        $isToday = ($day['date'] === now()->format('Y-m-d'));
                    @endphp
                    <button type="button"
                            data-date="{{ $day['full_date'] }}"
                            data-day-name="{{ $day['day_name'] }}"
                            data-title="{{ $day['title'] }}"
                            data-desc="{{ $day['description'] }}"
                            data-status="{{ $day['status_type'] }}"
                            data-color="{{ $day['color_class'] }}"
                            onclick="bukaDetailJadwal(this.dataset.date, this.dataset.title, this.dataset.desc, this.dataset.dayName, this.dataset.status, this.dataset.color)"
                            title="{{ $day['full_date'] }} - {{ $day['title'] }}"
                            class="{{ $day['color_class'] }} h-15 sm:h-16 rounded-2xl flex flex-col items-center justify-center text-white transition-all duration-200 transform hover:scale-[1.06] hover:-translate-y-0.5 active:scale-95 shadow-xs hover:shadow-lg border border-white/20 dark:border-white/10 cursor-pointer relative group select-none {{ $isToday ? 'ring-3 ring-sky-400 ring-offset-2 dark:ring-offset-slate-900 shadow-md shadow-sky-500/30 font-black' : '' }}">
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider opacity-85 leading-none">{{ substr($day['day_name'], 0, 3) }}</span>
                        <span class="text-xs sm:text-sm md:text-base font-black tabular-nums tracking-tight leading-none mt-1">{{ $day['day_number'] }}</span>

                        @if($isToday)
                            <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5" title="Hari Ini">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-300 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-sky-400 ring-2 ring-white dark:ring-slate-900"></span>
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modal Popup Detail Keterangan Jadwal (Pusat Viewport Sempurna & Elegan) --}}
    <div id="modalDetailJadwal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-200">
        {{-- Area klik latar belakang untuk menutup modal --}}
        <div class="fixed inset-0 -z-10 cursor-pointer" onclick="tutupDetailJadwal()" aria-hidden="true"></div>

        {{-- Kartu Dialog Modal (Tepat di Pusat Viewport Layar) --}}
        <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden my-auto transform transition-all animate-in fade-in zoom-in-95 duration-200">
            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-slate-50 via-sky-50/40 to-indigo-50/30 dark:from-slate-800/90 dark:via-slate-800/70 dark:to-slate-800/90 p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div id="detailIconBox" class="w-11 h-11 rounded-2xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 flex items-center justify-center text-lg shrink-0 border border-sky-300 dark:border-sky-700">
                        <i id="detailIcon" class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 id="detailTanggal" class="font-extrabold text-slate-900 dark:text-slate-100 text-sm sm:text-base leading-tight truncate">--</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5 truncate">
                            <span id="detailHariName" class="font-semibold text-sky-600 dark:text-sky-400">--</span>
                            <span>• Detail Jadwal Kerja</span>
                        </p>
                    </div>
                </div>
                <button type="button" onclick="tutupDetailJadwal()" class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer border border-slate-200/70 dark:border-slate-700 shadow-2xs shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Badan Isi Modal --}}
            <div class="p-5 sm:p-6 space-y-4">
                {{-- Status Badge --}}
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div id="detailBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200 dark:border-sky-800">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        <span>Informasi Jadwal</span>
                    </div>
                </div>

                {{-- Judul Jadwal --}}
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Agenda / Status Shift:</span>
                    <h5 id="detailTitle" class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-slate-100 mt-0.5 leading-snug">--</h5>
                </div>

                {{-- Kotak Keterangan & Jam Operasional --}}
                <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 space-y-1.5">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <i class="fa-solid fa-clock-rotate-left text-sky-500"></i>
                        <span>Keterangan Waktu & Aktivitas:</span>
                    </div>
                    <p id="detailDesc" class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 font-medium leading-relaxed">
                        --
                    </p>
                </div>

                {{-- Info Penempatan & Tipe Jadwal --}}
                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Penempatan</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200 truncate block mt-0.5">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Tipe Jadwal</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200 truncate block mt-0.5">{{ Auth::user()->schedule_type === 'roster' ? 'Shift Roster (12 Jam)' : 'Hari Kerja Reguler' }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="p-4 sm:p-5 bg-slate-100/80 dark:bg-slate-950/70 border-t border-slate-200/90 dark:border-slate-800 flex justify-end items-center gap-3 shrink-0">
                <button type="button" onclick="tutupDetailJadwal()" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-300/80 dark:border-slate-700 shadow-2xs hover:shadow-xs text-xs font-bold rounded-xl transition-all active:scale-95 cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark text-slate-400 dark:text-slate-400"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Cuti Anda --}}
    <div class="bg-white/95 dark:bg-slate-900/90 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 bg-sky-50/60 dark:bg-sky-950/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-regular fa-calendar-check text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight">Riwayat Cuti Anda</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 border border-sky-200/60 dark:border-sky-500/30">
                            {{ count($riwayatCuti) }} Pengajuan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar permohonan izin cuti Anda pada periode tahun berjalan.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <a href="{{ url('/cuti/riwayat') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-all border border-slate-200/60 dark:border-slate-700 inline-flex items-center gap-1.5">
                    <span>Semua Riwayat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <a href="{{ url('/cuti/ajukan') }}" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-sky-600/20 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-[10px] text-white"></i>
                    <span class="text-white">Ajukan Cuti</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-extrabold text-[11px] border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider select-none">
                        <th class="px-6 py-3.5">Jenis Cuti</th>
                        <th class="px-6 py-3.5">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-3.5">Durasi</th>
                        <th class="px-6 py-3.5">Keterangan / Alasan</th>
                        <th class="px-6 py-3.5">Status & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatCuti as $cuti)
                        <tr class="btn-detail-cuti hover:bg-sky-50/40 dark:hover:bg-slate-800/40 transition-all cursor-pointer group" data-id="{{ $cuti->id }}">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors flex items-center gap-2">
                                    <span>{{ $cuti->name_cuti }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-0 group-hover:opacity-100 text-sky-500 transition-opacity"></i>
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">Klik baris untuk rincian</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div class="inline-flex items-center gap-1.5 font-semibold text-xs bg-slate-100/80 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-regular fa-calendar text-sky-500 text-[11px]"></i>
                                    <span>{{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 font-extrabold text-xs border border-sky-200/60 dark:border-sky-500/30">
                                    <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                    <span>{{ $cuti->total_hari }} Hari</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs max-w-xs text-slate-600 dark:text-slate-400 truncate"
                                title="{{ $cuti->alasan_cuti ?? ($cuti->nama_sub_cuti ?? 'Tanpa Keterangan') }}">
                                @if(!empty($cuti->alasan_cuti))
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $cuti->alasan_cuti }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic">
                                        {{ $cuti->nama_sub_cuti ?? 'Tanpa Keterangan' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4" onclick="event.stopPropagation();">
                                @if(trim(strtolower($cuti->status_akhir ?? '')) === 'approved')
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Disetujui</span>
                                        </span>

                                        <button type="button"
                                                data-url="{{ route('cuti.cetak', $cuti->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                title="Pratinjau Cetak PDF Dokumen Cuti"
                                                class="px-2.5 py-1 bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white dark:bg-sky-500/15 dark:hover:bg-sky-600 dark:text-sky-300 dark:hover:text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition-all border border-sky-200/80 dark:border-sky-500/30 shadow-2xs cursor-pointer active:scale-95">
                                            <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                            <span>Cetak PDF</span>
                                        </button>
                                    </div>
                                @elseif(trim(strtolower($cuti->status_akhir ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_2 ?? '')) === 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200/60 dark:border-rose-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                        @if($cuti->catatan_penolakan)
                                            <div class="text-[11px] bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 p-2 rounded-xl max-w-[220px] text-slate-600 dark:text-slate-300 leading-relaxed shadow-2xs">
                                                <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan:</span>
                                                "{{ $cuti->catatan_penolakan }}"
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200/60 dark:border-amber-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-500 flex items-center justify-center text-2xl mx-auto mb-3 border border-sky-100 dark:border-sky-500/20">
                                    <i class="fa-regular fa-calendar-xmark"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan Cuti</h4>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 mb-3">Anda belum pernah mengajukan permohonan cuti pada periode ini.</p>
                                <a href="{{ url('/cuti/ajukan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Ajukan Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat MPR Anda --}}
    <div class="bg-white/95 dark:bg-slate-900/90 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all overflow-hidden mt-6">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 bg-indigo-50/60 dark:bg-indigo-950/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-600/25 shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight">Riwayat MPR Anda</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30">
                            {{ count($riwayatMpr) }} Pengajuan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar permohonan Material Purchase Request (MPR) Anda.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <a href="{{ url('/mpr/riwayat') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-all border border-slate-200/60 dark:border-slate-700 inline-flex items-center gap-1.5">
                    <span>Semua Riwayat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <a href="{{ url('/mpr/ajukan') }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-indigo-600/20 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-[10px] text-white"></i>
                    <span class="text-white">Ajukan MPR</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-extrabold text-[11px] border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider select-none">
                        <th class="px-6 py-3.5">Nomor & Tanggal</th>
                        <th class="px-6 py-3.5">Alasan Pengajuan</th>
                        <th class="px-6 py-3.5">Daftar Material</th>
                        <th class="px-6 py-3.5">Status & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatMpr as $mpr)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/40 transition-all">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-xs px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40 inline-block mb-1">
                                    {{ $mpr->nomor_mpr }}
                                </span>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span>{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700 dark:text-slate-300 max-w-xs truncate" title="{{ $mpr->keperluan_urgensi }}">
                                {{ $mpr->keperluan_urgensi }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="space-y-1">
                                    @foreach($mpr->items->take(2) as $item)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs border border-slate-200/60 dark:border-slate-700 mr-1 mb-1">
                                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $item->nama_barang }}</span>
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">({{ $item->jumlah }} {{ $item->satuan }})</span>
                                        </div>
                                    @endforeach
                                    @if($mpr->items->count() > 2)
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold block">+{{ $mpr->items->count() - 2 }} item lainnya</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if(trim(strtolower($mpr->status_akhir ?? '')) === 'approved')
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Disetujui</span>
                                        </span>

                                        <button type="button"
                                                data-url="{{ route('mpr.cetak', $mpr->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                title="Pratinjau Cetak PDF Dokumen MPR"
                                                class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-500/15 dark:hover:bg-indigo-600 dark:text-indigo-300 dark:hover:text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition-all border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs cursor-pointer active:scale-95">
                                            <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                            <span>Cetak PDF</span>
                                        </button>
                                    @elseif(trim(strtolower($mpr->status_akhir ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200/60 dark:border-rose-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($mpr->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 p-2 rounded-xl max-w-[220px] text-slate-600 dark:text-slate-300 leading-relaxed shadow-2xs">
                                                    <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan:</span>
                                                    "{{ $mpr->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200/60 dark:border-amber-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-2xl mx-auto mb-3 border border-indigo-100 dark:border-indigo-500/20">
                                    <i class="fa-solid fa-boxes-packing"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan MPR</h4>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 mb-3">Anda belum pernah mengajukan permohonan MPR pada periode ini.</p>
                                <a href="{{ url('/mpr/ajukan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Ajukan Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat CAR Anda --}}
    <div class="bg-white/95 dark:bg-slate-900/90 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all overflow-hidden mt-6">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 bg-teal-50/70 dark:bg-teal-950/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-600 dark:bg-teal-500 text-white flex items-center justify-center text-lg shadow-md shadow-teal-600/25 shrink-0">
                    <i class="fa-solid fa-money-bill-transfer text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight">Riwayat CAR Anda</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 border border-teal-200/60 dark:border-teal-500/30">
                            {{ count($riwayatCar) }} Pengajuan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar permohonan Cash Advance Request (CAR) Anda.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <a href="{{ url('/car/riwayat') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition-all border border-slate-200/60 dark:border-slate-700 inline-flex items-center gap-1.5">
                    <span>Semua Riwayat</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <a href="{{ url('/car/ajukan') }}" class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 dark:bg-teal-500 dark:hover:bg-teal-600 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-teal-600/20 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-[10px] text-white"></i>
                    <span class="text-white">Ajukan CAR</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-extrabold text-[11px] border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider select-none">
                        <th class="px-6 py-3.5">Nomor & Tanggal</th>
                        <th class="px-6 py-3.5">Alasan Pengajuan</th>
                        <th class="px-6 py-3.5">Daftar Material</th>
                        <th class="px-6 py-3.5">Status & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatCar as $car)
                        <tr class="hover:bg-teal-50/35 dark:hover:bg-slate-800/40 transition-all">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-xs px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/40 inline-block mb-1">
                                    {{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}
                                </span>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    <i class="fa-regular fa-clock text-[10px] text-teal-500"></i>
                                    <span>{{ $car->created_at ? $car->created_at->format('d M Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-slate-800 dark:text-slate-200 font-medium text-xs" title="{{ $car->alasan_pembelian }}">
                                {{ $car->alasan_pembelian }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($car->details && $car->details->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($car->details->take(2) as $detail)
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs border border-slate-200/60 dark:border-slate-700 mr-1 mb-1">
                                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $detail->nama_barang }}</span>
                                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-bold">({{ $detail->jumlah }} {{ $detail->satuan }})</span>
                                            </div>
                                        @endforeach
                                        @if($car->details->count() > 2)
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold block">+{{ $car->details->count() - 2 }} item lainnya</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if(trim(strtolower($car->status_akhir ?? '')) === 'approved')
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Disetujui</span>
                                        </span>

                                        <button type="button"
                                                data-url="{{ route('car.print', $car->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                title="Pratinjau Cetak PDF Dokumen CAR"
                                                class="px-2.5 py-1 bg-teal-50 hover:bg-teal-600 text-teal-700 hover:text-white dark:bg-teal-500/15 dark:hover:bg-teal-600 dark:text-teal-300 dark:hover:text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition-all border border-teal-200/80 dark:border-teal-500/30 shadow-2xs cursor-pointer active:scale-95">
                                            <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                            <span>Cetak PDF</span>
                                        </button>
                                    @elseif(trim(strtolower($car->status_akhir ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200/60 dark:border-rose-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($car->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/70 dark:border-rose-800/50 p-2 rounded-xl max-w-[220px] text-slate-600 dark:text-slate-300 leading-relaxed shadow-2xs">
                                                    <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan:</span>
                                                    "{{ $car->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200/60 dark:border-amber-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-teal-50 dark:bg-teal-500/10 text-teal-500 flex items-center justify-center text-2xl mx-auto mb-3 border border-teal-100 dark:border-teal-500/20">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan CAR</h4>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 mb-3">Anda belum pernah mengajukan permohonan CAR pada periode ini.</p>
                                <a href="{{ url('/car/ajukan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Ajukan Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
{{-- Modal Detail Cuti (Center Viewport Sempurna & Resisten Turbo) --}}
<div id="detailCutiModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-3 sm:p-5 overflow-y-auto">
    <div id="cutiModalBackdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity -z-10 cursor-pointer" onclick="closeDetailCutiModal()" aria-hidden="true"></div>
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        {{-- Modal Header --}}
        <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center gap-3 bg-sky-50/60 dark:bg-sky-950/20 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 dark:bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-file-invoice text-white"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight truncate">Detail Pengajuan Cuti</h3>
                    <p class="text-[11px] sm:text-xs text-slate-400 dark:text-slate-500 truncate">Rincian parameter permohonan & status persetujuan</p>
                </div>
            </div>
            <button type="button" id="closeCutiModalBtn" onclick="closeDetailCutiModal()" class="p-2 sm:px-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/60 dark:hover:text-rose-400 transition-all border border-slate-200/80 dark:border-slate-700/80 cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
            <div id="cutiModalLoading" class="hidden text-center py-10 space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xl mx-auto animate-spin">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Sedang menyinkronkan data cuti...</p>
            </div>

            <div id="cutiModalContent" class="space-y-4">
                {{-- Kategori Ringkasan Top Banner --}}
                <div class="bg-sky-50 dark:bg-sky-950/40 p-4 rounded-2xl border border-sky-200/80 dark:border-sky-500/30 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-black uppercase tracking-wider">Kategori Cuti</span>
                        <span id="txt_jenis_cuti" class="font-extrabold text-slate-800 dark:text-slate-100 mt-0.5 block">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-black uppercase tracking-wider">Sub Kategori</span>
                        <span id="txt_sub_cuti" class="font-bold text-sky-600 dark:text-sky-400 mt-0.5 block truncate">-</span>
                    </div>
                </div>

                {{-- Detail Parameter Table List --}}
                <div class="border border-slate-200/80 dark:border-slate-800 rounded-2xl divide-y divide-slate-100 dark:divide-slate-800 text-xs overflow-hidden bg-white dark:bg-slate-900/60">
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-400 dark:text-slate-400 font-bold flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-sky-500 text-sm"></i> Rentang Waktu
                        </span>
                        <span id="txt_rentang_tanggal" class="col-span-2 text-slate-800 dark:text-slate-100 font-bold">-</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-400 dark:text-slate-400 font-bold flex items-center gap-2">
                            <i class="fa-solid fa-hourglass-half text-sky-500 text-sm"></i> Total Durasi
                        </span>
                        <span id="txt_total_hari" class="col-span-2 text-slate-800 dark:text-slate-100 font-black text-sm">-</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5">
                        <span class="text-slate-400 dark:text-slate-400 font-bold flex items-center gap-2">
                            <i class="fa-regular fa-comment-dots text-sky-500 text-sm"></i> Alasan Izin
                        </span>
                        <div id="txt_alasan_cuti" class="col-span-2 text-slate-700 dark:text-slate-300 leading-relaxed font-medium bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60">-</div>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-400 dark:text-slate-400 font-bold flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-sky-500 text-sm"></i> Status Akhir
                        </span>
                        <div id="wrapper_status" class="col-span-2"></div>
                    </div>
                </div>

                {{-- Berkas Lampiran --}}
                <div class="border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 space-y-2 bg-slate-50/60 dark:bg-slate-800/30">
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 block uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-paperclip text-sky-500"></i> Berkas Lampiran Pendukung
                    </span>
                    <div id="dokumen_render_area"></div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 sm:p-5 bg-slate-100/80 dark:bg-slate-950/70 border-t border-slate-200/90 dark:border-slate-800 flex justify-end items-center gap-3 shrink-0">
            <button type="button" id="closeCutiModalBtn2" onclick="closeDetailCutiModal()" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-300/80 dark:border-slate-700 shadow-2xs hover:shadow-xs text-xs font-bold rounded-xl transition-all cursor-pointer active:scale-95 flex items-center gap-1.5">
                <i class="fa-solid fa-xmark text-slate-400 dark:text-slate-400"></i>
                <span>Tutup</span>
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
{{-- Load Pustaka SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. DETAIL KALENDER JADWAL
    function bukaDetailJadwal(tanggal, judul, deskripsi, hari = '', status = '', colorClass = '') {
        const modal = document.getElementById('modalDetailJadwal');
        if (!modal) return;

        // Pindahkan ke document.body jika belum berada di root body untuk jaminan 100% tepat di tengah viewport
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const detailTanggal = document.getElementById('detailTanggal');
        const detailHariName = document.getElementById('detailHariName');
        const detailTitle = document.getElementById('detailTitle');
        const detailDesc = document.getElementById('detailDesc');
        const detailBadge = document.getElementById('detailBadge');
        const detailIcon = document.getElementById('detailIcon');
        const detailIconBox = document.getElementById('detailIconBox');

        if (detailTanggal) detailTanggal.innerText = tanggal || '--';
        if (detailHariName) detailHariName.innerText = hari || 'Jadwal Harian';
        if (detailTitle) detailTitle.innerText = judul || '--';
        if (detailDesc) detailDesc.innerText = deskripsi || 'Tidak ada catatan tambahan untuk tanggal ini.';

        // Pengaturan Ikon & Badge Status Dinamis
        const judulLower = (judul || '').toLowerCase();

        let iconClass = 'fa-solid fa-calendar-day';
        let iconBoxClasses = 'bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 border-sky-300 dark:border-sky-700';
        let badgeText = 'Hari Kerja Aktif';
        let badgeClasses = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        let badgeDot = 'bg-emerald-500';

        if (judulLower.includes('malam') || status === 'malam') {
            iconClass = 'fa-solid fa-moon';
            iconBoxClasses = 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 border-indigo-300 dark:border-indigo-700';
            badgeText = 'Shift Malam (19:00 - 07:00)';
            badgeClasses = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800';
            badgeDot = 'bg-indigo-600';
        } else if (judulLower.includes('pagi') || status === 'pagi') {
            iconClass = 'fa-solid fa-sun';
            iconBoxClasses = 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700';
            badgeText = 'Shift Pagi (07:00 - 19:00)';
            badgeClasses = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
            badgeDot = 'bg-emerald-500';
        } else if (judulLower.includes('libur') || status === 'libur') {
            iconClass = 'fa-solid fa-mug-hot';
            iconBoxClasses = 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 border-rose-300 dark:border-rose-700';
            badgeText = judulLower.includes('nasional') ? 'Libur Nasional' : 'Libur / Off Day';
            badgeClasses = 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800';
            badgeDot = 'bg-rose-500';
        } else if (judulLower.includes('cuti') || status === 'cuti') {
            iconClass = 'fa-solid fa-calendar-check';
            iconBoxClasses = 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 border-amber-300 dark:border-amber-700';
            badgeText = 'Sedang Cuti';
            badgeClasses = 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800';
            badgeDot = 'bg-amber-500';
        }

        if (detailIcon) detailIcon.className = iconClass;
        if (detailIconBox) detailIconBox.className = `w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 border ${iconBoxClasses}`;
        if (detailBadge) {
            detailBadge.innerHTML = `<span class="w-2 h-2 rounded-full ${badgeDot}"></span><span>${badgeText}</span>`;
            detailBadge.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${badgeClasses}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function tutupDetailJadwal() {
        const modal = document.getElementById('modalDetailJadwal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function pastikanSemuaModalDiBody() {
        const modalIds = [
            'modalDetailJadwal',
            'detailCutiModal'
        ];
        modalIds.forEach(function (id) {
            const modal = document.getElementById(id);
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", pastikanSemuaModalDiBody);
    document.addEventListener("turbo:load", pastikanSemuaModalDiBody);
    document.addEventListener("turbo:render", pastikanSemuaModalDiBody);
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        pastikanSemuaModalDiBody();
    }

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            const modalJadwal = document.getElementById("modalDetailJadwal");
            if (modalJadwal && !modalJadwal.classList.contains("hidden")) {
                tutupDetailJadwal();
            }
            const modalCuti = document.getElementById("detailCutiModal");
            if (modalCuti && !modalCuti.classList.contains("hidden")) {
                window.closeDetailCutiModal();
            }
        }
    });

    // 3. DETAIL CUTI AJAX (TURBO RESILIENT & VIEWPORT CENTERED)
    window.openDetailCutiModal = function(cutiId) {
        const modal = document.getElementById("detailCutiModal");
        if (!modal) return;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.classList.add("overflow-hidden");

        const loadingSection = document.getElementById("cutiModalLoading");
        const contentSection = document.getElementById("cutiModalContent");
        if (loadingSection) loadingSection.classList.remove("hidden");
        if (contentSection) contentSection.classList.add("hidden");

        fetch(`/cuti/riwayat/${cutiId}/detail`)
            .then(response => {
                if (!response.ok) throw new Error("Jaringan bermasalah");
                return response.json();
            })
            .then(data => {
                if (loadingSection) loadingSection.classList.add("hidden");
                if (contentSection) contentSection.classList.remove("hidden");

                const txtJenis = document.getElementById("txt_jenis_cuti");
                const txtSub = document.getElementById("txt_sub_cuti");
                const txtRentang = document.getElementById("txt_rentang_tanggal");
                const txtHari = document.getElementById("txt_total_hari");
                const txtAlasan = document.getElementById("txt_alasan_cuti");

                if (txtJenis) txtJenis.innerText = data.name_cuti || '-';
                if (txtSub) txtSub.innerText = data.nama_sub_cuti || '-';
                if (txtRentang) txtRentang.innerText = `${data.tanggal_mulai_formatted} s/d ${data.tanggal_selesai_formatted}`;
                if (txtHari) txtHari.innerText = `${data.total_hari} Hari`;
                if (txtAlasan) txtAlasan.innerText = data.alasan_cuti || '-';

                const wrapperStatus = document.getElementById("wrapper_status");
                if (wrapperStatus) {
                    if (data.status_manager === 'approved') {
                        wrapperStatus.innerHTML = `
                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-emerald-200/60 dark:border-emerald-800/40 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Disetujui</span>
                            </span>`;
                    } else if (data.status_supervisor === 'rejected' || data.status_manager === 'rejected' || data.status_akhir === 'rejected') {
                        let note = data.catatan_penolakan ? `
                            <div class="mt-2 text-[11px] bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/70 dark:border-rose-800/60 p-2.5 rounded-xl text-slate-600 dark:text-slate-300 leading-relaxed shadow-2xs">
                                <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Catatan Penolakan:</span>
                                "${data.catatan_penolakan}"
                            </div>` : '';
                        wrapperStatus.innerHTML = `
                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-rose-200/60 dark:border-rose-800/40 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span>Ditolak</span>
                            </span>${note}`;
                    } else {
                        wrapperStatus.innerHTML = `
                            <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center gap-1.5 border border-amber-200/60 dark:border-amber-800/40 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                <span>Menunggu Review</span>
                            </span>`;
                    }
                }

                const docArea = document.getElementById("dokumen_render_area");
                if (docArea) {
                    if (data.dokumen_pendukung) {
                        const fileUrl = `/storage/${data.dokumen_pendukung}`;
                        const fileName = data.dokumen_pendukung.split('/').pop();
                        docArea.innerHTML = `
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900/80 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shrink-0 border border-sky-100 dark:border-sky-500/20">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate block">${fileName}</span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block">Dokumen Lampiran Cuti</span>
                                    </div>
                                </div>
                                <button type="button" onclick="bukaPratinjauLampiran('${fileUrl}', '${fileName}')" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0 cursor-pointer inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-eye text-[11px]"></i>
                                    <span>Lihat Lampiran</span>
                                </button>
                            </div>`;
                    } else {
                        docArea.innerHTML = `<span class="text-xs italic text-slate-400 dark:text-slate-500 bg-white/50 dark:bg-slate-900/40 border border-dashed border-slate-200/90 dark:border-slate-800 rounded-2xl p-3.5 block text-center">Tidak ada berkas lampiran yang dilampirkan.</span>`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Gagal memuat data detail pengajuan cuti.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
                window.closeDetailCutiModal();
            });
    };

    window.closeDetailCutiModal = function() {
        const modal = document.getElementById("detailCutiModal");
        if (modal) {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
        }
        document.body.classList.remove("overflow-hidden");
    };

    if (!window.dashboardCutiListenerAttached) {
        window.dashboardCutiListenerAttached = true;
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-detail-cuti');
            if (btn) {
                e.preventDefault();
                const cutiId = btn.getAttribute('data-id');
                if (cutiId && typeof window.openDetailCutiModal === 'function') {
                    window.openDetailCutiModal(cutiId);
                }
            }
        });
    }

</script>
@endpush

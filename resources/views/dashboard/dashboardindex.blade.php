@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- BANNER SISTEM PERINGATAN KELENGKAPAN AKUN (ACCOUNT READINESS GUARD) --}}
    @php
        $accStatus = auth()->user()->getAccountCompletionStatus();
        $completedCount = collect([
            $accStatus['email_verified'],
            $accStatus['phone_verified'],
            $accStatus['face_registered'],
            $accStatus['signature_set'],
            $accStatus['schedule_set'],
        ])->filter()->count();
        $percentComplete = round(($completedCount / 5) * 100);
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
                                {{ $completedCount }}/5 Syarat Selesai ({{ $percentComplete }}%)
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed max-w-2xl">
                            Akses pembuatan formulir pengajuan (Cuti, CAR, & MPR) <strong>terkunci</strong> sampai seluruh 5 syarat kepegawaian di bawah ini diverifikasi dan dilengkapi.
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

            {{-- 5 Checklist Items Grid Sesuai Urutan Baku --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-4">
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

                {{-- 2. Nomor Telepon / WA --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['phone_verified'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-brands fa-whatsapp text-base {{ $accStatus['phone_verified'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">No. WhatsApp</span>
                            </div>
                            @if($accStatus['phone_verified'])
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['phone_verified'] ? 'Nomor telah diverifikasi OTP' : 'Nomor WhatsApp belum terverifikasi' }}
                        </div>
                    </div>
                    @if(!$accStatus['phone_verified'])
                        <a href="{{ url('/profile?phone_required=1#phone_number') }}" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs">
                            Belum Verifikasi
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-check text-xs"></i> Terverifikasi
                        </span>
                    @endif
                </div>

                {{-- 3. Biometrik Wajah AI --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['face_registered'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-face-viewfinder text-base {{ $accStatus['face_registered'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Biometrik Wajah</span>
                            </div>
                            @if($accStatus['face_registered'])
                                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['face_registered'] ? 'Embedding biometrik AI aktif' : 'Wajah belum direkam untuk presensi' }}
                        </div>
                    </div>
                    @if(!$accStatus['face_registered'])
                        <button type="button" onclick="bukaModalRekamWajah()" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs cursor-pointer">
                            Rekam Wajah AI
                        </button>
                    @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-check text-xs"></i> Sudah Direkam
                        </span>
                    @endif
                </div>

                {{-- 4. Tanda Tangan Digital (TTD) --}}
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

                {{-- 5. Jadwal Kerja --}}
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
                            {{ $accStatus['schedule_set'] ? 'Sistem kerja ' . strtoupper(auth()->user()->schedule_type) : 'Jadwal kerja belum diatur' }}
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

    {{-- Statistik Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
    </div>

    {{-- Widget Absensi & Jadwal Kerja --}}
    <div class="bg-white dark:bg-slate-800/90 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs mb-6 transition-colors">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/60 pb-4 mb-4">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Status Operasional Hari Ini</span>

                    @if($user->schedule_type === 'roster')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                            Sistem Roster
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
                            @php
                                $activeSchedule = app(App\Services\ScheduleService::class)->getTodaySchedule($user);
                            @endphp

                            @if(isset($activeSchedule['is_day_off']) && $activeSchedule['is_day_off'])
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif(isset($activeSchedule['shift_type']) && $activeSchedule['shift_type'] === 'pagi')
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Pagi <span class="{{ $isWorkingNow ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Sedang OFF - Di Luar Jam Kerja' }})</span>
                                </span>
                            @else
                                <span class="text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-indigo-600 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Malam <span class="{{ $isWorkingNow ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Sedang OFF - Di Luar Jam Kerja' }})</span>
                                </span>
                            @endif
                        @else
                            @if(isset($todaySchedule['is_day_off']) && $todaySchedule['is_day_off'])
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif($isWorkingNow)
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> Sedang Bekerja
                                </span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Di Luar Jam Kerja)
                                </span>
                            @endif
                        @endif
                    </h3>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                    @if(is_null($user->schedule_type))
                        Harap lakukan atur jam kerja pada menu profil Anda.
                    @elseif(isset($todaySchedule['is_day_off']) && !$todaySchedule['is_day_off'])
                        Ketentuan Jam Kerja: <strong class="text-slate-700 dark:text-slate-200">{{ $todaySchedule['scheduled_in'] ?? '--:--' }} - {{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB</strong>
                    @else
                        Hari ini Anda tidak memiliki jadwal shift aktif.
                    @endif
                </p>

                {{-- TANDA DANGER TERLAMBAT ATAU INFO WAKTUNYA PULANG --}}
                @if(isset($isLateNotCheckedIn) && $isLateNotCheckedIn)
                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl text-xs font-semibold text-rose-700 dark:text-rose-300">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 animate-pulse"></i>
                        <span><strong>Peringatan:</strong> Jam kerja Anda sudah dimulai ({{ $todaySchedule['scheduled_in'] ?? '--:--' }} WIB) dan Anda belum melakukan absen masuk (Terlambat).</span>
                    </div>
                @elseif(isset($canCheckOutNow) && $canCheckOutNow)
                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span><strong>Informasi:</strong> Jam kerja telah berakhir ({{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB). Anda sudah boleh melakukan absen pulang.</span>
                    </div>
                @endif
            </div>

            @if(!is_null($user->schedule_type) && isset($todaySchedule['is_day_off']) && !$todaySchedule['is_day_off'])
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Status / Tombol Rekam Wajah Biometrik --}}
                    @if(!empty($user->face_descriptor))
                        <button type="button" onclick="bukaModalRekamWajah()" title="Klik untuk merekam ulang / memperbarui data wajah biometrik" class="bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 text-[11px] font-bold py-2 px-3 rounded-xl transition-colors flex items-center space-x-1.5 cursor-pointer shadow-2xs">
                            <i class="fa-solid fa-shield-check text-sky-600 dark:text-sky-400"></i>
                            <span>Biometrik Aktif</span>
                        </button>
                    @else
                        <button type="button" onclick="bukaModalRekamWajah()" class="bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-[11px] font-bold py-2 px-3 rounded-xl transition-colors flex items-center space-x-1.5 cursor-pointer shadow-2xs animate-pulse">
                            <i class="fa-solid fa-camera text-amber-600 dark:text-amber-400"></i>
                            <span>Rekam Wajah</span>
                        </button>
                    @endif

                    @if(!$todayAttendance || !$todayAttendance->check_in)
                        <button type="button" onclick="bukaModalAbsen('in')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition-colors flex items-center space-x-2 shadow-sm cursor-pointer">
                            <i class="fa-solid fa-camera"></i>
                            <span>Absen Masuk</span>
                        </button>
                    @elseif(!$todayAttendance->check_out)
                        <button type="button" onclick="bukaModalAbsen('out')" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition-colors flex items-center space-x-2 shadow-sm cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Absen Pulang</span>
                        </button>
                    @else
                        <span class="bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-xs font-bold py-2 px-4 rounded-xl border border-slate-200 dark:border-slate-600">
                            <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Absensi Selesai
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-700/60 flex items-center justify-between transition-colors">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Waktu Absen Masuk:</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $todayAttendance->check_in ?? '--:--' }}</span>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-slate-700/60 flex items-center justify-between transition-colors">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Waktu Absen Pulang:</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $todayAttendance->check_out ?? '--:--' }}</span>
            </div>
        </div>
    </div>

    {{-- Widget Kalender Aktivitas Jadwal --}}
    <div class="bg-white dark:bg-slate-800/90 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs mb-6 transition-colors">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/60 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-10 bg-blue-600 rounded-full"></div>
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Kalender Jadwal Kerja & Aktivitas</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 pl-0.5">Visualisasi jadwal shift, libur nasional, dan histori cuti Anda.</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                @php
                    $prevMonth = $currentCarbonDate->copy()->subMonth();
                    $nextMonth = $currentCarbonDate->copy()->addMonth();
                @endphp
                <a href="{{ route('dashboard', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 min-w-[120px] text-center">
                    {{ $currentCarbonDate->isoFormat('MMMM YYYY') }}
                </span>
                <a href="{{ route('dashboard', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600 dark:text-slate-300 mb-4 bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-3 rounded-xl">
            <span class="text-slate-400 dark:text-slate-400 font-bold">Keterangan:</span>

            @if($user->schedule_type === 'roster')
                <div class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                    <span>Shift Pagi</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-md bg-indigo-600"></span>
                    <span>Shift Malam</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-md bg-rose-500"></span>
                    <span>Libur</span>
                </div>
            @else
                <div class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                    <span>Masuk Kerja</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 rounded-md bg-rose-500"></span>
                    <span>Libur / Libur Nasional</span>
                </div>
            @endif

            <div class="flex items-center space-x-1.5">
                <span class="w-3 h-3 rounded-md bg-amber-400"></span>
                <span>Histori Cuti</span>
            </div>
        </div>

        <div class="grid grid-cols-7 sm:grid-cols-10 md:grid-cols-15 gap-2 pt-2">
            @foreach($calendarDays as $day)
                <button type="button"
                        data-date="{{ $day['full_date'] }}"
                        data-title="{{ $day['title'] }}"
                        data-desc="{{ $day['description'] }}"
                        onclick="bukaDetailJadwal(this.dataset.date, this.dataset.title, this.dataset.desc)"
                        class="{{ $day['color_class'] }} h-10 rounded-xl flex flex-col items-center justify-center text-white transition-all transform hover:scale-105 shadow-sm cursor-pointer relative group">
                    <span class="text-[11px] font-bold">{{ $day['day_number'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Modal Popup Detail Keterangan Jadwal --}}
    <div id="modalDetailJadwal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2">
                <h4 id="detailTanggal" class="font-bold text-slate-800 dark:text-slate-100 text-sm">--</h4>
                <button type="button" onclick="tutupDetailJadwal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div>
                <span id="detailTitle" class="font-bold text-sky-600 dark:text-sky-400 text-xs block">--</span>
                <p id="detailDesc" class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed bg-slate-50 dark:bg-slate-800/80 p-3 rounded-xl border border-slate-100 dark:border-slate-700">--</p>
            </div>
            <div class="text-right pt-2">
                <button type="button" onclick="tutupDetailJadwal()" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL KHUSUS: PEREKAMAN / UPDATE BIOMETRIK WAJAH --}}
    <div id="modalRekamWajah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-md w-full max-h-[90vh] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/80 shrink-0">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-face-viewfinder text-sky-600 dark:text-sky-400"></i>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Perekaman Biometrik Wajah</h3>
                </div>
                <button type="button" onclick="tutupModalRekamWajah()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-5 space-y-3 flex-1 overflow-y-auto">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Posisikan wajah Anda tepat di dalam frame kamera. Pastikan pencahayaan cukup dan wajah terlihat jelas tanpa masker atau kacamata gelap.
                </p>

                {{-- Container Video Kamera Perekaman --}}
                <div class="relative bg-black rounded-xl overflow-hidden w-full aspect-[3/4] sm:aspect-video flex items-center justify-center border border-slate-200 dark:border-slate-700 shadow-inner">
                    <video id="videoRekamWajah"
                           autoplay
                           playsinline
                           muted
                           style="transform: scaleX(-1) !important; -webkit-transform: scaleX(-1) !important;"
                           class="w-full h-full object-cover"></video>
                    <canvas id="canvasRekamWajah" class="absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1) !important;"></canvas>

                    <div id="statusRekamWajahBox" class="absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-medium">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat model biometrik AI...
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="tutupModalRekamWajah()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl">Batal</button>
                <button type="button" onclick="simpanBiometrikWajah()" id="btnSimpanWajah" disabled class="px-5 py-2 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-colors flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Data Wajah</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL STEP 1: VERIFIKASI BIOMETRIK WAJAH & GPS (PRESENSI HARIAN) --}}
    <div id="modalAbsensi" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-md w-full max-h-[90vh] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/80 shrink-0">
                <h3 id="judulModalAbsen" class="font-bold text-slate-800 dark:text-slate-100 text-sm">Verifikasi Absensi</h3>
                <button type="button" onclick="tutupModalAbsen()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Isian Content --}}
            <div class="p-5 space-y-3 flex-1 overflow-y-auto">

                {{-- Status GPS --}}
                <div id="statusLokasiBox" class="p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between transition-all shrink-0">
                    <div class="flex items-center space-x-2.5">
                        <i id="iconLokasi" class="fa-solid fa-location-dot text-slate-400 text-sm"></i>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi Terkini:</span>
                            <span id="textNamaLokasi" class="text-xs font-bold text-slate-600 dark:text-slate-300">Mendeteksi lokasi GPS...</span>
                        </div>
                    </div>
                    <div id="reloadContainer" class="hidden text-right pl-2 border-l border-rose-200/60">
                        <span class="text-[10px] text-rose-600 block font-semibold leading-tight">Memuat Ulang</span>
                        <span id="reloadCountdown" class="text-xs font-mono font-bold text-rose-700 animate-pulse">5s</span>
                    </div>
                </div>

                {{-- Container Video Kamera Verifikasi Wajah --}}
                <div class="relative bg-black rounded-xl overflow-hidden w-full aspect-[3/4] sm:aspect-video flex items-center justify-center border border-slate-200 dark:border-slate-700 shadow-inner shrink-0">
                    <video id="webcamVideo"
                        autoplay
                        playsinline
                        muted
                        style="transform: scaleX(-1) !important; -webkit-transform: scaleX(-1) !important;"
                        class="w-full h-full object-cover"></video>

                    <canvas id="webcamCanvas" class="absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1) !important;"></canvas>
                    
                    <div id="cameraStatus" class="absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-semibold">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mempersiapkan verifikasi biometrik...
                    </div>
                </div>

                <div id="facePromptNotice" class="hidden p-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-info text-amber-600 dark:text-amber-400 text-sm"></i>
                        <span>Belum ada data wajah biometrik terdaftar.</span>
                    </div>
                    <button type="button" onclick="tutupModalAbsen(); bukaModalRekamWajah();" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-[10px]">
                        Rekam Sekarang
                    </button>
                </div>
            </div>

            {{-- Footer Tombol --}}
            <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="tutupModalAbsen()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl">Batal</button>
                <button type="button" onclick="verifikasiDanLanjut()" id="btnVerifikasiLanjut" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center space-x-1.5 shadow-sm cursor-pointer">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Lanjutkan Presensi</span>
                </button>
            </div>

        </div>
    </div>

    {{-- MODAL STEP 2: KONFIRMASI DETAIL ABSENSI, ALASAN WAJIB & BUKTI WATERMARK --}}
    <div id="modalKonfirmasiAbsen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-md w-full max-h-[90vh] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/80 shrink-0">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Konfirmasi Detail Absensi</h3>
                <button type="button" onclick="kembaliKeKamera()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Form & Isian Modal --}}
            <form id="formAbsensi" onsubmit="submitAbsensi(event)" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-y-auto">
                @csrf
                <input type="hidden" id="absen_type" name="type" value="in">
                <input type="hidden" id="absen_lat" name="latitude">
                <input type="hidden" id="absen_long" name="longitude">
                <input type="hidden" id="absen_is_face_verified" name="is_face_verified" value="1">

                <div class="p-5 space-y-3.5 flex-1 overflow-y-auto">

                    {{-- Informasi Detail Rincian Absen --}}
                    <div class="space-y-2 bg-slate-50 dark:bg-slate-800/80 p-3.5 rounded-xl border border-slate-100 dark:border-slate-700 text-xs">
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Status Lokasi:</span>
                            <span id="txtStatusRadius" class="font-bold text-emerald-600 dark:text-emerald-400">Di Dalam Area kerja</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Lokasi GPS:</span>
                            <span id="txtNamaLokasiConfirm" class="font-semibold text-slate-800 dark:text-slate-100 truncate max-w-[180px]">--</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Verifikasi Wajah:</span>
                            <span id="txtStatusBiometrik" class="font-bold text-sky-600 dark:text-sky-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-sky-500"></i> Terverifikasi AI
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Waktu Absen:</span>
                            <span id="txtWaktuSekarang" class="font-bold text-slate-800 dark:text-slate-100">--:-- WIB</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Ketentuan Jam Kerja:</span>
                            <span id="txtJamJadwal" class="font-bold text-slate-700 dark:text-slate-200">
                                {{ $todaySchedule['scheduled_in'] ?? '--:--' }} - {{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB
                            </span>
                        </div>
                    </div>

                    {{-- Status Peringatan Terlambat / Di Luar Radius --}}
                    <div id="boxWarningStatus" class="hidden p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-300 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-base mt-0.5 shrink-0"></i>
                        <span id="txtWarningMessage" class="leading-relaxed">Harap lengkapi alasan di bawah ini sebelum mengirim absensi.</span>
                    </div>

                    {{-- Input Alasan Khusus --}}
                    <div id="wrapperAlasan" class="space-y-1.5">
                        <label id="labelAlasan" class="text-xs font-bold text-rose-700 dark:text-rose-400 block">Alasan Khusus / Keterangan:</label>
                        <textarea id="inputAlasan" name="reason" rows="2" class="w-full p-2.5 text-xs border border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20 text-slate-800 dark:text-slate-100 rounded-xl focus:ring-2 focus:ring-rose-400 outline-none resize-none" placeholder="Tuliskan alasan lengkap Anda di sini..."></textarea>
                        <span id="errorAlasanMsg" class="text-[11px] text-rose-600 dark:text-rose-400 hidden font-semibold">* Alasan wajib diisi!</span>
                    </div>

                    {{-- Input Bukti Pendukung (Opsional dengan Watermark) --}}
                    <div id="wrapperBukti" class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-700">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-200 block flex items-center justify-between">
                            <span>Lampiran Bukti Pendukung <span class="text-slate-400 font-normal">(Opsional):</span></span>
                            <span id="txtNamaFileTerpilih" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold truncate max-w-[150px]"></span>
                        </label>

                        <input type="file" id="inputEvidenceFile" name="evidence" accept="image/*,.pdf" class="hidden" onchange="handleEvidenceFileChange(this)">

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="ambilFotoBuktiKamera()" class="flex-1 py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-slate-200 dark:border-slate-700">
                                <i class="fa-solid fa-camera text-sky-600 dark:text-sky-400"></i>
                                <span>Kamera Langsung</span>
                            </button>
                            <button type="button" onclick="pilihDokumenBukti()" class="flex-1 py-2 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-slate-200 dark:border-slate-700">
                                <i class="fa-solid fa-folder-open text-amber-600 dark:text-amber-400"></i>
                                <span>Galeri / File</span>
                            </button>
                        </div>

                        {{-- Preview Thumbnail Bukti Jika Ada --}}
                        <div id="previewBuktiContainer" class="hidden relative p-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between">
                            <div class="flex items-center space-x-2.5 overflow-hidden">
                                <img id="imgPreviewBukti" src="" class="w-10 h-10 object-cover rounded-lg border border-slate-200 dark:border-slate-700 hidden" alt="Preview Bukti">
                                <div id="iconPdfBukti" class="w-10 h-10 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-lg flex items-center justify-center text-lg hidden">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <span id="labelFileNameBukti" class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate max-w-[200px]">file.jpg</span>
                            </div>
                            <button type="button" onclick="hapusBuktiTerpilih()" class="text-rose-500 hover:text-rose-700 p-1.5 text-xs cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                        <p class="text-[11px] text-slate-500 dark:text-slate-400 italic flex items-start gap-1.5 leading-snug">
                            <i class="fa-solid fa-circle-info text-sky-500 mt-0.5 shrink-0"></i>
                            <span>Dokumen/Foto pendukung bersifat opsional. Foto yang dilampirkan akan otomatis diberi watermark tanggal, waktu, dan nama karyawan.</span>
                        </p>
                    </div>

                </div>

                {{-- Footer Tombol Aksi --}}
                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2 shrink-0">
                    <button type="button" onclick="kembaliKeKamera()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" id="btnSubmitAbsen" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm cursor-pointer">
                        Kirim Absensi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat Cuti Anda --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs overflow-hidden transition-colors">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 border-l-4 border-sky-500 pl-3">Riwayat Cuti Anda</h3>
                <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">Daftar permohonan izin cuti Anda pada periode tahun berjalan.</p>
            </div>
            <a href="{{ url('/cuti/ajukan') }}" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1 shadow-xs">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan Cuti</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/70 text-slate-500 dark:text-slate-400 font-semibold text-xs border-b border-slate-100 dark:border-slate-700/60 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Jenis Cuti</th>
                        <th class="px-6 py-3.5">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-3.5">Durasi</th>
                        <th class="px-6 py-3.5">Keterangan / Alasan</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatCuti as $cuti)
                        <tr class="btn-detail-cuti hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" data-id="{{ $cuti->id }}">
                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">{{ $cuti->name_cuti }}</td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 font-medium">{{ $cuti->total_hari }} Hari</td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs max-w-xs truncate"
                                title="{{ $cuti->alasan_cuti ?? ($cuti->nama_sub_cuti ?? 'Tanpa Keterangan') }}">
                                @if(!empty($cuti->alasan_cuti))
                                    {{ $cuti->alasan_cuti }}
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic font-medium">
                                        {{ isset($cuti->nama_sub_cuti) ? $cuti->nama_sub_cuti : 'Tanpa Keterangan' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4" onclick="event.stopPropagation();">
                                @if(trim(strtolower($cuti->status_akhir ?? '')) === 'approved')
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>

                                        <button type="button"
                                                data-url="{{ route('cuti.cetak', $cuti->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-xs cursor-pointer">
                                            <i class="fa-solid fa-print text-[10px]"></i>
                                            <span>PDF</span>
                                        </button>
                                    </div>
                                @elseif(trim(strtolower($cuti->status_akhir ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_2 ?? '')) === 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-rose-200/60 dark:border-rose-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                        @if($cuti->catatan_penolakan)
                                            <div class="text-[11px] bg-rose-50/50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-800/50 p-2 rounded-lg max-w-[200px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                                <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan Penolakan:</span>
                                                "{{ $cuti->catatan_penolakan }}"
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-amber-200/60 dark:border-amber-800/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block text-slate-200 dark:text-slate-700"></i>
                                Anda belum pernah mengajukan permohonan cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat MPR Anda --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs overflow-hidden mt-6 transition-colors">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 border-l-4 border-indigo-500 pl-3">Riwayat MPR Anda</h3>
                <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">Daftar permohonan Material Purchase Request (MPR) Anda.</p>
            </div>
            <a href="{{ url('/mpr/ajukan') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1 shadow-xs">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan MPR</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/70 text-slate-500 dark:text-slate-400 font-semibold text-xs border-b border-slate-100 dark:border-slate-700/60 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Nomor & Tanggal</th>
                        <th class="px-6 py-3.5">Alasan Pengajuan</th>
                        <th class="px-6 py-3.5">Daftar Material</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatMpr as $mpr)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">{{ $mpr->nomor_mpr }}</span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-400">{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700 dark:text-slate-300 max-w-xs truncate" title="{{ $mpr->keperluan_urgensi }}">
                                {{ $mpr->keperluan_urgensi }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach($mpr->items->take(2) as $item)
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-200">{{ $item->nama_barang }}</span> ({{ $item->jumlah }} {{ $item->satuan }})</li>
                                    @endforeach
                                    @if($mpr->items->count() > 2)
                                        <li class="text-slate-400 dark:text-slate-400 italic">+{{ $mpr->items->count() - 2 }} item lainnya</li>
                                    @endif
                                </ul>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    @if(trim(strtolower($mpr->status_akhir ?? '')) === 'approved')
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-emerald-200/60 dark:border-emerald-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Disetujui</span>
                                            </span>

                                            <button type="button"
                                                    data-url="{{ route('mpr.cetak', $mpr->id) }}"
                                                    onclick="bukaPratinjauCetak(this.dataset.url)"
                                                    class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold inline-flex items-center space-x-1 transition-colors shadow-xs cursor-pointer">
                                                <i class="fa-solid fa-print text-[10px]"></i>
                                                <span>PDF</span>
                                            </button>
                                        </div>
                                    @elseif(trim(strtolower($mpr->status_akhir ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-rose-200/60 dark:border-rose-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($mpr->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-800/50 p-2 rounded-lg max-w-[200px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                                    <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan Penolakan:</span>
                                                    "{{ $mpr->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-amber-200/60 dark:border-amber-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-boxes-packing text-3xl mb-2 block text-slate-200 dark:text-slate-700"></i>
                                Anda belum pernah mengajukan permohonan MPR.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat CAR Anda --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-xs overflow-hidden mt-6 transition-colors">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 border-l-4 border-purple-500 pl-3">Riwayat CAR Anda</h3>
                <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">Daftar permohonan Cash Advance Request (CAR) Anda.</p>
            </div>
            <a href="{{ url('/car/ajukan') }}" class="bg-purple-500 hover:bg-purple-600 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1 shadow-xs">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan CAR</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/70 text-slate-500 dark:text-slate-400 font-semibold text-xs border-b border-slate-100 dark:border-slate-700/60 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Tanggal Pengajuan</th>
                        <th class="px-6 py-3.5">Alasan Pengajuan</th>
                        <th class="px-6 py-3.5">Daftar Material</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatCar as $car)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-medium">
                                {{ $car->created_at ? $car->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-slate-800 dark:text-slate-200" title="{{ $car->alasan_pembelian }}">
                                {{ $car->alasan_pembelian }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($car->details && $car->details->count() > 0)
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach($car->details->take(2) as $detail)
                                            <li>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $detail->nama_barang }}</span>
                                                ({{ $detail->jumlah }} {{ $detail->satuan }})
                                            </li>
                                        @endforeach

                                        @if($car->details->count() > 2)
                                            <li class="text-slate-400 dark:text-slate-400 italic">+{{ $car->details->count() - 2 }} item lainnya</li>
                                        @endif
                                    </ul>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    @if(trim(strtolower($car->status_akhir ?? '')) === 'approved')
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-emerald-200/60 dark:border-emerald-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Disetujui</span>
                                            </span>

                                            <button type="button"
                                                    data-url="{{ route('car.print', $car->id) }}"
                                                    onclick="bukaPratinjauCetak(this.dataset.url)"
                                                    class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-xs cursor-pointer">
                                                <i class="fa-solid fa-print text-[10px]"></i>
                                                <span>PDF</span>
                                            </button>
                                        </div>
                                    @elseif(trim(strtolower($car->status_akhir ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-rose-200/60 dark:border-rose-800/40">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($car->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-800/50 p-2 rounded-lg max-w-[200px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                                    <span class="font-bold text-rose-700 dark:text-rose-400 block mb-0.5">Alasan Penolakan:</span>
                                                    "{{ $car->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold inline-flex items-center space-x-1 border border-amber-200/60 dark:border-amber-800/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-200 dark:text-slate-700"></i>
                                Anda belum pernah mengajukan permohonan CAR.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Pratinjau Dokumen Lampiran --}}
<div id="modalPreviewLampiran" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-2xl w-full h-[80vh] flex flex-col shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 id="judulModalLampiran" class="font-bold text-slate-800 dark:text-slate-100 text-sm">Pratinjau Dokumen</h3>
            <button onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div id="containerKontenLampiran" class="flex-1 bg-slate-50 dark:bg-slate-950 p-4 flex items-center justify-center overflow-hidden"></div>
    </div>
</div>

{{-- Modal Detail Cuti --}}
<div id="detailCutiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="cutiModalBackdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-xl overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-slate-100">Detail Pengajuan Cuti</h3>
            <button id="closeCutiModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div id="cutiModalLoading" class="hidden text-center py-6 text-xs font-semibold text-slate-400 animate-pulse">Memuat data...</div>
            <div id="cutiModalContent" class="space-y-3">
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Jenis Cuti</span><p id="txt_jenis_cuti" class="text-sm font-semibold text-slate-800 dark:text-slate-100 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Sub Kategori Cuti</span><p id="txt_sub_cuti" class="text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Rentang Tanggal</span><p id="txt_rentang_tanggal" class="text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Durasi</span><p id="txt_total_hari" class="text-sm font-semibold text-slate-800 dark:text-slate-100 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Alasan</span><p id="txt_alasan_cuti" class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed bg-slate-50 dark:bg-slate-800/80 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block mb-1">Status</span><div id="wrapper_status"></div></div>
                <div class="pt-2"><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block mb-1">Berkas Pendukung</span><div id="dokumen_render_area"></div></div>
            </div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex justify-end"><button id="closeCutiModalBtn2" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors">Tutup</button></div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Load Pustaka SweetAlert2 & Face-API Biometrik CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<script>
    // 1. DETAIL KALENDER JADWAL
    function bukaDetailJadwal(tanggal, judul, deskripsi) {
        document.getElementById('detailTanggal').innerText = tanggal;
        document.getElementById('detailTitle').innerText = judul;
        document.getElementById('detailDesc').innerText = deskripsi;

        const modal = document.getElementById('modalDetailJadwal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function tutupDetailJadwal() {
        const modal = document.getElementById('modalDetailJadwal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    }

    // 2. PRATINJAU DOKUMEN
    function bukaPratinjauLampiran(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Lampiran Dokumen';
        tampilkanModalPratinjau(urlFile);
    }

    function bukaPratinjauCetak(urlFile) {
        const judulModal = document.getElementById('judulModalLampiran');
        if (urlFile.includes('car')) {
            judulModal.innerText = 'Pratinjau Dokumen Cetak Dokumen CAR';
        } else if (urlFile.includes('cuti')) {
            judulModal.innerText = 'Pratinjau Dokumen Cetak Dokumen Cuti';
        } else if (urlFile.includes('mpr')) {
            judulModal.innerText = 'Pratinjau Dokumen Cetak Dokumen MPR';
        } else {
            judulModal.innerText = 'Pratinjau Dokumen Cetak';
        }
        tampilkanModalPratinjau(urlFile, true);
    }

    function tampilkanModalPratinjau(urlFile, isPdfFormated = false) {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');

        if (!modal || !container) return;

        container.innerHTML = '<div class="text-xs text-slate-400 font-medium animate-pulse text-center p-4">Memuat dokumen...</div>';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const ekstensi = urlFile.split('.').pop().toLowerCase();

        if (isPdfFormated || ekstensi === 'pdf') {
            container.innerHTML = `<iframe src="${urlFile}" class="w-full h-full rounded-xl border-0 shadow-inner" allow="autoplay"></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ekstensi)) {
            container.innerHTML = `<img src="${urlFile}" class="max-w-full max-h-full rounded-xl shadow-md object-contain mx-auto" alt="Pratinjau Lampiran">`;
        } else {
            container.innerHTML = `
                <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-slate-200 max-w-xs mx-auto">
                    <i class="fa-solid fa-file-arrow-down text-amber-500 text-3xl mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700 mb-3">Format file tidak mendukung pratinjau langsung.</p>
                    <a href="${urlFile}" download class="inline-flex items-center gap-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-download"></i> Unduh File
                    </a>
                </div>
            `;
        }
    }

    function tutupPratinjauLampiran() {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
        if (container) {
            container.innerHTML = '';
        }
        document.body.style.overflow = 'auto';
    }

    const modalPreviewElement = document.getElementById('modalPreviewLampiran');
    if (modalPreviewElement) {
        modalPreviewElement.addEventListener('click', function(e) {
            if (e.target === this) {
                tutupPratinjauLampiran();
            }
        });
    }

    // 3. DETAIL CUTI AJAX
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("detailCutiModal");
        const backdrop = document.getElementById("cutiModalBackdrop");
        const closeBtn = document.getElementById("closeCutiModalBtn");
        const closeBtn2 = document.getElementById("closeCutiModalBtn2");
        const loadingSection = document.getElementById("cutiModalLoading");
        const contentSection = document.getElementById("cutiModalContent");

        function openModal() {
            if (modal) {
                modal.classList.remove("hidden");
                modal.classList.add("flex");
                document.body.classList.add("overflow-hidden");
            }
        }

        function closeModal() {
            if (modal) {
                modal.classList.remove("flex");
                modal.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            }
        }

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (closeBtn2) closeBtn2.addEventListener("click", closeModal);
        if (backdrop) backdrop.addEventListener("click", closeModal);

        document.querySelectorAll(".btn-detail-cuti").forEach(btn => {
            btn.addEventListener("click", function () {
                const cutiId = this.getAttribute("data-id");

                openModal();
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

                        document.getElementById("txt_jenis_cuti").innerText = data.name_cuti;
                        document.getElementById("txt_sub_cuti").innerText = data.nama_sub_cuti ? data.nama_sub_cuti : '-';
                        document.getElementById("txt_rentang_tanggal").innerText = `${data.tanggal_mulai_formatted} s/d ${data.tanggal_selesai_formatted}`;
                        document.getElementById("txt_total_hari").innerText = `${data.total_hari} Hari`;
                        document.getElementById("txt_alasan_cuti").innerText = data.alasan_cuti ? data.alasan_cuti : '-';

                        const wrapperStatus = document.getElementById("wrapper_status");
                        if (wrapperStatus) {
                            if (data.status_manager === 'approved') {
                                wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Disetujui</span>`;
                            } else if (data.status_supervisor === 'rejected' || data.status_manager === 'rejected' || data.status_akhir === 'rejected') {
                                let note = data.catatan_penolakan ? `<p class="text-xs text-rose-600 mt-1 italic font-medium">"${data.catatan_penolakan}"</p>` : '';
                                wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center border border-rose-100"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>Ditolak</span> ${note}`;
                            } else {
                                wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center border border-amber-100"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>Menunggu Review</span>`;
                            }
                        }

                        const docArea = document.getElementById("dokumen_render_area");
                        if (docArea) {
                            if (data.dokumen_pendukung) {
                                const fileUrl = `/storage/${data.dokumen_pendukung}`;
                                docArea.innerHTML = `
                                    <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                        <div class="flex items-center space-x-2.5 overflow-hidden">
                                            <div class="p-2 bg-sky-50 text-sky-600 rounded-lg text-lg"><i class="fa-solid fa-file"></i></div>
                                            <div class="flex flex-col truncate">
                                                <span class="text-xs font-semibold text-slate-700 truncate">${data.dokumen_pendukung.split('/').pop()}</span>
                                            </div>
                                        </div>
                                        <button type="button" onclick="bukaPratinjauLampiran('${fileUrl}')" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors shrink-0 cursor-pointer">
                                            Lihat Lampiran
                                        </button>
                                    </div>`;
                            } else {
                                docArea.innerHTML = `<span class="text-xs italic text-slate-400 bg-white border border-dashed rounded-xl p-3 block text-center">Tidak melampirkan berkas dokumen apapun.</span>`;
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
                        closeModal();
                    });
            });
        });
    });

    // 4. BIOMETRIC FACE RECOGNITION & GPS GEOFENCING ATTENDANCE
    let mediaStream = null;
    let rekamMediaStream = null;
    let faceApiModelsLoaded = false;
    let faceDetectionInterval = null;
    let rekamFaceDetectionInterval = null;
    let latestRecordedDescriptor = null;
    let isFaceVerified = false;

    let isSubmittingFaceRegister = false;
    let stableDetectionCount = 0;
    let isAutoSubmittingAttendance = false;
    let stableAttendanceFaceCount = 0;

    let countdownInterval = null;
    let secondsLeft = 5;
    let isUserInRadius = false;
    let isLateOrEarly = false;

    const daftarStasiun = JSON.parse('{!! json_encode($daftarStasiun ?? []) !!}');
    const todaySchedule = JSON.parse('{!! json_encode($todaySchedule ?? []) !!}');
    let userFaceDescriptor = {!! json_encode(auth()->user()->face_descriptor) !!};

    const FACE_API_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    function checkLateOrEarlyStatus(type) {
        const now = new Date();
        let isLate = false;
        if (todaySchedule && !todaySchedule.is_day_off) {
            const currentMinutes = now.getHours() * 60 + now.getMinutes();
            if (type === 'in' && todaySchedule.scheduled_in) {
                const [hIn, mIn] = todaySchedule.scheduled_in.split(':');
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);
                if (currentMinutes > schedInMinutes) {
                    isLate = true;
                }
            } else if (type === 'out' && todaySchedule.scheduled_out) {
                const [hOut, mOut] = todaySchedule.scheduled_out.split(':');
                const [hIn, mIn] = (todaySchedule.scheduled_in || "00:00").split(':');
                const schedOutMinutes = parseInt(hOut) * 60 + parseInt(mOut);
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);
                const isCrossDayShift = schedOutMinutes < schedInMinutes;
                if (isCrossDayShift) {
                    if (currentMinutes >= schedInMinutes || currentMinutes < schedOutMinutes) {
                        isLate = true;
                    }
                } else {
                    if (currentMinutes < schedOutMinutes) {
                        isLate = true;
                    }
                }
            }
        }
        return isLate;
    }

    async function ensureFaceApiLoaded() {
        if (faceApiModelsLoaded) return true;
        if (typeof faceapi === 'undefined') {
            console.warn("Library face-api.js belum terpasang atau sedang memuat.");
            return false;
        }

        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(FACE_API_MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(FACE_API_MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(FACE_API_MODEL_URL)
            ]);
            faceApiModelsLoaded = true;
            console.log("Model AI Biometrik Wajah berhasil dimuat.");
            return true;
        } catch (err) {
            console.error("Gagal memuat model face-api:", err);
            return false;
        }
    }

    // ==========================================
    // ALUR PEREKAMAN BIOMETRIK WAJAH (REGISTRATION)
    // ==========================================
    async function bukaModalRekamWajah() {
        const modal = document.getElementById('modalRekamWajah');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        const statusBox = document.getElementById('statusRekamWajahBox');
        const btnSimpan = document.getElementById('btnSimpanWajah');
        if (btnSimpan) btnSimpan.disabled = true;
        latestRecordedDescriptor = null;
        isSubmittingFaceRegister = false;
        stableDetectionCount = 0;

        if (statusBox) {
            statusBox.className = "absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-medium";
            statusBox.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mempersiapkan model biometrik AI...`;
        }

        const isMobile = window.innerWidth < 640;
        const constraints = {
            audio: false,
            video: {
                facingMode: "user",
                width: { ideal: isMobile ? 720 : 1280 },
                height: { ideal: isMobile ? 1280 : 720 }
            }
        };

        try {
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            rekamMediaStream = stream;
            const video = document.getElementById('videoRekamWajah');
            if (video) {
                video.srcObject = stream;
                await video.play();
            }

            await ensureFaceApiLoaded();
            if (statusBox) {
                statusBox.innerHTML = `<i class="fa-solid fa-camera mr-1"></i> Posisikan wajah Anda tepat di tengah frame...`;
            }

            mulaiDeteksiWajahPerekaman();
        } catch (err) {
            console.error("Gagal akses kamera untuk perekaman:", err);
            if (statusBox) {
                statusBox.className = "absolute bottom-2 left-2 right-2 bg-rose-600/90 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-bold";
                statusBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Izin kamera ditolak atau tidak tersedia.`;
            }
        }
    }

    function mulaiDeteksiWajahPerekaman() {
        const video = document.getElementById('videoRekamWajah');
        const canvas = document.getElementById('canvasRekamWajah');
        const statusBox = document.getElementById('statusRekamWajahBox');
        const btnSimpan = document.getElementById('btnSimpanWajah');

        if (!video || !canvas) return;

        if (rekamFaceDetectionInterval) {
            clearInterval(rekamFaceDetectionInterval);
        }

        rekamFaceDetectionInterval = setInterval(async () => {
            if (!faceApiModelsLoaded || video.paused || video.ended || !video.videoWidth) return;

            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection && detection.descriptor && detection.descriptor.length === 128) {
                latestRecordedDescriptor = Array.from(detection.descriptor);
                if (btnSimpan) btnSimpan.disabled = false;

                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                stableDetectionCount++;
                if (!isSubmittingFaceRegister && stableDetectionCount >= 2) {
                    isSubmittingFaceRegister = true;
                    if (rekamFaceDetectionInterval) {
                        clearInterval(rekamFaceDetectionInterval);
                        rekamFaceDetectionInterval = null;
                    }

                    if (statusBox) {
                        statusBox.className = "absolute bottom-2 left-2 right-2 bg-emerald-600/95 text-white text-[11px] px-3 py-2 rounded-xl backdrop-blur-sm z-10 text-center font-bold shadow-lg flex items-center justify-center gap-2 animate-pulse";
                        statusBox.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> <span>Wajah Terdeteksi Jelas! Menyimpan biometrik otomatis...</span>`;
                    }

                    setTimeout(() => {
                        simpanBiometrikWajah();
                    }, 500);
                } else if (!isSubmittingFaceRegister) {
                    if (statusBox) {
                        statusBox.className = "absolute bottom-2 left-2 right-2 bg-sky-600/90 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-bold shadow-sm";
                        statusBox.innerHTML = `<i class="fa-solid fa-camera mr-1"></i> Wajah Terdeteksi! Tahan posisi...`;
                    }
                }
            } else {
                stableDetectionCount = 0;
                latestRecordedDescriptor = null;
                if (btnSimpan) btnSimpan.disabled = true;

                if (statusBox && !isSubmittingFaceRegister) {
                    statusBox.className = "absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-medium";
                    statusBox.innerHTML = `<i class="fa-solid fa-arrows-to-eye mr-1"></i> Arahkan wajah ke kamera...`;
                }
            }
        }, 300);
    }

    function tutupModalRekamWajah() {
        if (rekamFaceDetectionInterval) {
            clearInterval(rekamFaceDetectionInterval);
            rekamFaceDetectionInterval = null;
        }

        if (rekamMediaStream) {
            rekamMediaStream.getTracks().forEach(t => t.stop());
            rekamMediaStream = null;
        }

        const video = document.getElementById('videoRekamWajah');
        if (video) video.srcObject = null;

        const modal = document.getElementById('modalRekamWajah');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    }

    async function simpanBiometrikWajah() {
        if (!latestRecordedDescriptor || latestRecordedDescriptor.length === 0) {
            Swal.fire({
                title: 'Wajah Belum Terdeteksi',
                text: 'Pastikan wajah Anda terdeteksi dengan jelas di dalam kamera sebelum menyimpan.',
                icon: 'warning',
                confirmButtonColor: '#0284c7'
            });
            return;
        }

        const btnSimpan = document.getElementById('btnSimpanWajah');
        btnSimpan.disabled = true;
        btnSimpan.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...`;

        try {
            const response = await fetch('/user/face/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    face_descriptor: latestRecordedDescriptor
                })
            });

            const res = await response.json();

            if (response.ok) {
                userFaceDescriptor = latestRecordedDescriptor;
                tutupModalRekamWajah();

                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message || 'Perekaman biometrik wajah berhasil disimpan!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(res.message || 'Gagal menyimpan biometrik wajah.');
            }
        } catch (err) {
            console.error("Gagal simpan biometrik wajah:", err);
            Swal.fire({
                title: 'Gagal Menyimpan',
                text: err.message || 'Terjadi kesalahan saat menyimpan data biometrik.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
            btnSimpan.disabled = false;
            btnSimpan.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> <span>Simpan Data Wajah</span>`;
        }
    }

    // ==========================================
    // ALUR VERIFIKASI PRESENSI HARIAN (CHECK IN / OUT)
    // ==========================================
    function calculateDistanceMeter(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const radLat1 = lat1 * Math.PI / 180;
        const radLat2 = lat2 * Math.PI / 180;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(radLat1) * Math.cos(radLat2) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function checkUserLocation() {
        const statusBox = document.getElementById('statusLokasiBox');
        const textLokasi = document.getElementById('textNamaLokasi');
        const iconLokasi = document.getElementById('iconLokasi');
        const reloadContainer = document.getElementById('reloadContainer');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;

                    document.getElementById('absen_lat').value = userLat;
                    document.getElementById('absen_long').value = userLng;

                    let matchedStation = null;
                    let closestDistance = Infinity;
                    let nearestStation = null;
                    let nearestDistance = Infinity;

                    if (Array.isArray(daftarStasiun) && daftarStasiun.length > 0) {
                        for (let station of daftarStasiun) {
                            if (station.latitude && station.longitude) {
                                const distance = calculateDistanceMeter(
                                    userLat,
                                    userLng,
                                    parseFloat(station.latitude),
                                    parseFloat(station.longitude)
                                );
                                const radiusLimit = parseFloat(station.radius_meters) || 100;

                                if (distance < nearestDistance) {
                                    nearestDistance = distance;
                                    nearestStation = station;
                                }

                                if (distance <= radiusLimit) {
                                    if (!matchedStation || distance < closestDistance) {
                                        matchedStation = station;
                                        closestDistance = distance;
                                    }
                                }
                            }
                        }
                    }

                    if (statusBox && textLokasi && iconLokasi) {
                        if (matchedStation) {
                            isUserInRadius = true;
                            statusBox.className = "p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-xl flex items-center justify-between transition-all";
                            iconLokasi.className = "fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-sm";
                            textLokasi.className = "text-xs font-bold text-emerald-700 dark:text-emerald-300";
                            const stLabel = (matchedStation.type === 'rumah_meter' && matchedStation.kode_stasiun)
                                ? `${matchedStation.kode_stasiun} - ${matchedStation.name}`
                                : matchedStation.name;
                            textLokasi.innerText = "📍 Lokasi Terdeteksi: " + stLabel + " (Jarak: " + Math.round(closestDistance) + " meter)";
                            document.getElementById('txtNamaLokasiConfirm').innerText = stLabel + " (" + Math.round(closestDistance) + "m)";

                            if (reloadContainer) reloadContainer.classList.add('hidden');
                            stopGpsTimer();
                        } else {
                            isUserInRadius = false;
                            statusBox.className = "p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/80 rounded-xl flex items-center justify-between transition-all";
                            iconLokasi.className = "fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-sm";
                            textLokasi.className = "text-xs font-bold text-rose-700 dark:text-rose-300";
                            const nearestMsg = nearestStation ? " (Terdekat: " + nearestStation.name + ", " + Math.round(nearestDistance) + "m)" : "";
                            textLokasi.innerText = "⚠️ Lokasi berada di luar seluruh area stasiun kerja resmi" + nearestMsg;
                            document.getElementById('txtNamaLokasiConfirm').innerText = "Di Luar Radius Resmi" + (nearestStation ? " (" + Math.round(nearestDistance) + "m dari " + nearestStation.name + ")" : "");

                            if (reloadContainer) reloadContainer.classList.remove('hidden');
                            startGpsTimer();
                        }
                    }
                },
                (err) => {
                    isUserInRadius = false;
                    if (statusBox && textLokasi && iconLokasi) {
                        statusBox.className = "p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between transition-all";
                        iconLokasi.className = "fa-solid fa-circle-exclamation text-amber-600 text-sm";
                        textLokasi.className = "text-xs font-bold text-amber-700";
                        textLokasi.innerText = "Gagal mengakses GPS. Pastikan izin lokasi aktif!";
                        document.getElementById('txtNamaLokasiConfirm').innerText = "GPS Tidak Aktif";
                        if (reloadContainer) reloadContainer.classList.add('hidden');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }

    function startGpsTimer() {
        if (countdownInterval) return;
        secondsLeft = 5;
        const reloadCountdown = document.getElementById('reloadCountdown');
        if (reloadCountdown) reloadCountdown.innerText = secondsLeft + 's';

        countdownInterval = setInterval(() => {
            secondsLeft--;
            if (reloadCountdown) reloadCountdown.innerText = secondsLeft + 's';
            if (secondsLeft <= 0) {
                secondsLeft = 5;
                if (reloadCountdown) reloadCountdown.innerText = '5s';
                checkUserLocation();
            }
        }, 1000);
    }

    function stopGpsTimer() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }

    async function bukaModalAbsen(type) {
        document.getElementById('absen_type').value = type;
        document.getElementById('judulModalAbsen').innerText = type === 'in' ? 'Verifikasi Absen Masuk' : 'Verifikasi Absen Pulang';

        isFaceVerified = false;
        isAutoSubmittingAttendance = false;
        stableAttendanceFaceCount = 0;
        const btnLanjut = document.getElementById('btnVerifikasiLanjut');
        if (btnLanjut) btnLanjut.disabled = false; // Enabled so user can advance once face matches or with camera active

        const statusBox = document.getElementById('statusLokasiBox');
        const textLokasi = document.getElementById('textNamaLokasi');
        const iconLokasi = document.getElementById('iconLokasi');
        const reloadContainer = document.getElementById('reloadContainer');
        const faceNotice = document.getElementById('facePromptNotice');

        if (statusBox && textLokasi && iconLokasi) {
            statusBox.className = "p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between transition-all";
            iconLokasi.className = "fa-solid fa-spinner fa-spin text-slate-400 text-sm";
            textLokasi.innerText = "Mendeteksi posisi GPS Anda...";
            if (reloadContainer) reloadContainer.classList.add('hidden');
        }

        if (faceNotice) {
            if (!userFaceDescriptor || userFaceDescriptor.length === 0) {
                faceNotice.classList.remove('hidden');
            } else {
                faceNotice.classList.add('hidden');
            }
        }

        const modal = document.getElementById('modalAbsensi');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        checkUserLocation();

        const isMobile = window.innerWidth < 640;
        const constraints = {
            audio: false,
            video: {
                facingMode: "user",
                width: { ideal: isMobile ? 720 : 1280 },
                height: { ideal: isMobile ? 1280 : 720 }
            }
        };

        const camStatus = document.getElementById('cameraStatus');

        try {
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            mediaStream = stream;
            const video = document.getElementById('webcamVideo');
            if (video) {
                video.srcObject = stream;
                await video.play();
            }

            if (camStatus) {
                camStatus.className = "absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-semibold";
                camStatus.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat AI Biometrik...`;
            }

            await ensureFaceApiLoaded();
            mulaiVerifikasiWajahRealtime();
        } catch (err) {
            console.error("Gagal Akses Kamera:", err);
            if (camStatus) {
                camStatus.className = "absolute bottom-2 left-2 right-2 bg-rose-600/90 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-bold";
                camStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Kamera tidak dapat diakses`;
            }
        }
    }

    function mulaiVerifikasiWajahRealtime() {
        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('webcamCanvas');
        const camStatus = document.getElementById('cameraStatus');

        if (!video || !canvas) return;

        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
        }

        faceDetectionInterval = setInterval(async () => {
            if (!faceApiModelsLoaded || video.paused || video.ended || !video.videoWidth) return;

            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                let isMatch = false;
                let confidence = 100;

                if (userFaceDescriptor && userFaceDescriptor.length > 0) {
                    const distance = faceapi.euclideanDistance(detection.descriptor, userFaceDescriptor);
                    confidence = Math.max(0, Math.min(100, Math.round((1 - distance) * 100)));

                    // Threshold Euclidean Distance: <= 0.55 adalah MATCH
                    if (distance <= 0.55) {
                        isMatch = true;
                    }
                } else {
                    // Jika belum terdaftar descriptor, anggap deteksi wajah aktif
                    isMatch = true;
                }

                if (isMatch) {
                    isFaceVerified = true;
                    stableAttendanceFaceCount++;

                    if (!isAutoSubmittingAttendance && stableAttendanceFaceCount >= 2) {
                        isAutoSubmittingAttendance = true;
                        if (faceDetectionInterval) {
                            clearInterval(faceDetectionInterval);
                            faceDetectionInterval = null;
                        }

                        const type = document.getElementById('absen_type').value;
                        const isLate = checkLateOrEarlyStatus(type);

                        // Evaluasi apakah terlambat atau di luar radius
                        if (isLate || !isUserInRadius) {
                            // Jeda auto-submit untuk memunculkan form alasan wajib & bukti
                            if (camStatus) {
                                camStatus.className = "absolute bottom-2 left-2 right-2 bg-amber-600/95 text-white text-[11px] px-3 py-2 rounded-xl backdrop-blur-sm z-10 text-center font-bold shadow-lg flex items-center justify-center gap-2 animate-pulse";
                                camStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Wajah Cocok! Anda ${isLate ? 'Terlambat / Pulang Cepat' : 'Di Luar Radius'}. Membuka form konfirmasi alasan...`;
                            }
                            setTimeout(() => {
                                verifikasiDanLanjut();
                            }, 700);
                        } else {
                            // Auto-Submit Presensi Langsung (Tanpa harus klik submit manual)
                            if (camStatus) {
                                camStatus.className = "absolute bottom-2 left-2 right-2 bg-emerald-600/95 text-white text-[11px] px-3 py-2 rounded-xl backdrop-blur-sm z-10 text-center font-bold shadow-lg flex items-center justify-center gap-2 animate-pulse";
                                camStatus.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> Wajah Terverifikasi AI (${confidence}%)! Memproses presensi otomatis...`;
                            }
                            setTimeout(() => {
                                if (mediaStream) {
                                    mediaStream.getTracks().forEach(track => track.stop());
                                    mediaStream = null;
                                }
                                submitAbsensiKeBackend();
                            }, 800);
                        }
                    } else if (!isAutoSubmittingAttendance) {
                        if (camStatus) {
                            camStatus.className = "absolute bottom-2 left-2 right-2 bg-emerald-600/90 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-bold shadow-sm";
                            camStatus.innerHTML = `<i class="fa-solid fa-circle-check mr-1"></i> Wajah Terverifikasi AI (${confidence}%)! Tahan posisi...`;
                        }
                    }
                } else {
                    stableAttendanceFaceCount = 0;
                    isFaceVerified = false;
                    if (camStatus && !isAutoSubmittingAttendance) {
                        camStatus.className = "absolute bottom-2 left-2 right-2 bg-rose-600/90 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-bold shadow-sm";
                        camStatus.innerHTML = `<i class="fa-solid fa-circle-xmark mr-1"></i> Wajah Tidak Cocok (${confidence}%). Harap hadap kamera langsung.`;
                    }
                }
            } else {
                stableAttendanceFaceCount = 0;
                if (camStatus && !isAutoSubmittingAttendance) {
                    camStatus.className = "absolute bottom-2 left-2 right-2 bg-slate-900/75 text-white text-[11px] px-3 py-1.5 rounded-lg backdrop-blur-sm z-10 text-center font-semibold";
                    camStatus.innerHTML = `<i class="fa-solid fa-arrows-to-eye mr-1"></i> Posisikan wajah Anda di depan kamera...`;
                }
            }
        }, 300);
    }

    function verifikasiDanLanjut() {
        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
            faceDetectionInterval = null;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }

        document.getElementById('modalAbsensi').classList.add('hidden');
        document.getElementById('modalKonfirmasiAbsen').classList.remove('hidden');
        document.getElementById('modalKonfirmasiAbsen').classList.add('flex');

        prosesEvaluasiKonfirmasi();
    }

    function prosesEvaluasiKonfirmasi() {
        const type = document.getElementById('absen_type').value;
        const now = new Date();
        const jamSekarangStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('txtWaktuSekarang').innerText = jamSekarangStr + ' WIB';

        const txtStatusRadius = document.getElementById('txtStatusRadius');
        if (isUserInRadius) {
            txtStatusRadius.className = "font-bold text-emerald-600";
            txtStatusRadius.innerText = "Di Dalam Area Kerja";
        } else {
            txtStatusRadius.className = "font-bold text-rose-600";
            txtStatusRadius.innerText = "Di Luar Area Kerja";
        }

        const txtStatusBiometrik = document.getElementById('txtStatusBiometrik');
        if (isFaceVerified) {
            txtStatusBiometrik.className = "font-bold text-emerald-600 flex items-center gap-1";
            txtStatusBiometrik.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500"></i> Terverifikasi AI`;
        } else {
            txtStatusBiometrik.className = "font-bold text-amber-600 flex items-center gap-1";
            txtStatusBiometrik.innerHTML = `<i class="fa-solid fa-circle-exclamation text-amber-500"></i> Kamera Aktif`;
        }

        isLateOrEarly = false;
        let warningText = "";

        if (todaySchedule && !todaySchedule.is_day_off) {
            const currentMinutes = now.getHours() * 60 + now.getMinutes();

            if (type === 'in' && todaySchedule.scheduled_in) {
                const [hIn, mIn] = todaySchedule.scheduled_in.split(':');
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);

                if (currentMinutes > schedInMinutes) {
                    isLateOrEarly = true;
                    warningText += "Anda melakukan absen masuk MELEBIHI batas jam masuk kerja (Terlambat). ";
                }
            } else if (type === 'out' && todaySchedule.scheduled_out) {
                const [hOut, mOut] = todaySchedule.scheduled_out.split(':');
                const [hIn, mIn] = (todaySchedule.scheduled_in || "00:00").split(':');

                const schedOutMinutes = parseInt(hOut) * 60 + parseInt(mOut);
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);

                const isCrossDayShift = schedOutMinutes < schedInMinutes;

                if (isCrossDayShift) {
                    if (currentMinutes >= schedInMinutes || currentMinutes < schedOutMinutes) {
                        isLateOrEarly = true;
                        warningText += "Anda melakukan absen pulang SEBELUM jam kerja berakhir (Pulang Cepat). ";
                    }
                } else {
                    if (currentMinutes < schedOutMinutes) {
                        isLateOrEarly = true;
                        warningText += "Anda melakukan absen pulang SEBELUM jam kerja berakhir (Pulang Cepat). ";
                    }
                }
            }
        }

        if (!isUserInRadius) {
            warningText += "Posisi GPS Anda berada di luar radius stasiun kerja. ";
        }

        const boxWarning = document.getElementById('boxWarningStatus');
        const txtWarningMessage = document.getElementById('txtWarningMessage');
        const inputAlasan = document.getElementById('inputAlasan');

        if (!isUserInRadius || isLateOrEarly) {
            boxWarning.classList.remove('hidden');
            txtWarningMessage.innerText = warningText + "Harap lengkapi alasan wajib di bawah ini sebelum mengirim absensi.";
            inputAlasan.required = true;
        } else {
            boxWarning.classList.add('hidden');
            inputAlasan.required = false;
        }
    }

    function kembaliKeKamera() {
        document.getElementById('modalKonfirmasiAbsen').classList.add('hidden');
        bukaModalAbsen(document.getElementById('absen_type').value);
    }

    function tutupModalAbsen() {
        stopGpsTimer();

        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
            faceDetectionInterval = null;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }

        const video = document.getElementById('webcamVideo');
        if (video) video.srcObject = null;

        const inputAlasan = document.getElementById('inputAlasan');
        if (inputAlasan) inputAlasan.value = '';

        hapusBuktiTerpilih();

        document.getElementById('modalAbsensi').classList.remove('flex');
        document.getElementById('modalAbsensi').classList.add('hidden');
        document.getElementById('modalKonfirmasiAbsen').classList.remove('flex');
        document.getElementById('modalKonfirmasiAbsen').classList.add('hidden');
    }

    // ==========================================
    // HANDLER LAMPIRAN BUKTI DOKUMEN / FOTO WATERMARK
    // ==========================================
    function ambilFotoBuktiKamera() {
        const fileInput = document.getElementById('inputEvidenceFile');
        fileInput.setAttribute('capture', 'environment');
        fileInput.click();
    }

    function pilihDokumenBukti() {
        const fileInput = document.getElementById('inputEvidenceFile');
        fileInput.removeAttribute('capture');
        fileInput.click();
    }

    function handleEvidenceFileChange(input) {
        if (!input.files || input.files.length === 0) {
            hapusBuktiTerpilih();
            return;
        }

        const file = input.files[0];
        const previewContainer = document.getElementById('previewBuktiContainer');
        const imgPreview = document.getElementById('imgPreviewBukti');
        const iconPdf = document.getElementById('iconPdfBukti');
        const labelName = document.getElementById('labelFileNameBukti');
        const txtTerpilih = document.getElementById('txtNamaFileTerpilih');

        previewContainer.classList.remove('hidden');
        labelName.innerText = file.name;
        if (txtTerpilih) txtTerpilih.innerText = file.name;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                iconPdf.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            imgPreview.classList.add('hidden');
            iconPdf.classList.remove('hidden');
        }
    }

    function hapusBuktiTerpilih() {
        const fileInput = document.getElementById('inputEvidenceFile');
        if (fileInput) fileInput.value = '';

        const previewContainer = document.getElementById('previewBuktiContainer');
        if (previewContainer) previewContainer.classList.add('hidden');

        const txtTerpilih = document.getElementById('txtNamaFileTerpilih');
        if (txtTerpilih) txtTerpilih.innerText = '';
    }

    // ==========================================
    // SUBMIT FORM ABSENSI (MULTIPART FORMDATA)
    // ==========================================
    function submitAbsensi(e) {
        if (e && e.preventDefault) e.preventDefault();
        submitAbsensiKeBackend();
    }

    function submitAbsensiKeBackend() {
        const inputAlasan = document.getElementById('inputAlasan');
        const errorAlasanMsg = document.getElementById('errorAlasanMsg');

        if (inputAlasan && inputAlasan.required && inputAlasan.value.trim() === '') {
            if (errorAlasanMsg) errorAlasanMsg.classList.remove('hidden');
            inputAlasan.focus();
            return;
        }
        if (errorAlasanMsg) errorAlasanMsg.classList.add('hidden');

        const btnSubmit = document.getElementById('btnSubmitAbsen');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim Presensi...`;
        }

        const type = document.getElementById('absen_type').value || 'in';
        const url = type === 'in' ? '/attendance/check-in' : '/attendance/check-out';

        const reasonValue = inputAlasan ? inputAlasan.value : '';
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('latitude', document.getElementById('absen_lat').value || '0');
        formData.append('longitude', document.getElementById('absen_long').value || '0');
        formData.append('is_face_verified', isFaceVerified ? '1' : '0');
        formData.append('reason', reasonValue);
        formData.append('reason_out_of_radius', reasonValue);
        formData.append('reason_checkout', reasonValue);

        const evidenceInput = document.getElementById('inputEvidenceFile');
        if (evidenceInput && evidenceInput.files.length > 0) {
            formData.append('evidence', evidenceInput.files[0]);
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            return { status: res.status, body: data };
        })
        .then(res => {
            if (res.status === 200) {
                tutupModalAbsen();

                Swal.fire({
                    title: 'Berhasil!',
                    text: res.body.message || 'Presensi berhasil dicatat!',
                    icon: 'success',
                    confirmButtonText: 'Selesai',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });

            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: res.body.message || 'Gagal mengirim presensi.',
                    icon: 'error',
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#e11d48'
                });

                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Kirim Absensi Sekarang';
            }
        })
        .catch(err => {
            console.error("Detail Error Absensi:", err);

            Swal.fire({
                title: 'Kesalahan Sistem!',
                text: 'Gagal mengirim data presensi. Silakan coba lagi.',
                icon: 'warning',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#d97706'
            });

            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Kirim Absensi Sekarang';
        });
    }
</script>
@endpush

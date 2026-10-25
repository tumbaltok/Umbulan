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

                {{-- 3. Biometrik Wajah --}}
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between {{ $accStatus['face_registered'] ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40' : 'bg-white/80 border-amber-200 dark:bg-slate-800/80 dark:border-amber-800/40' }}">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-face-viewfinder text-base {{ $accStatus['face_registered'] ? 'text-emerald-600' : 'text-amber-600' }}"></i>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Biometrik Wajah</span>
                            </div>
                            @if($accStatus['face_registered'])
                                <i class="fa-solid fa-lock text-emerald-600 text-sm"></i>
                            @else
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                            {{ $accStatus['face_registered'] ? 'Data biometrik wajah aktif & terkunci' : 'Wajah belum direkam untuk presensi' }}
                        </div>
                    </div>
                    @if(!$accStatus['face_registered'])
                        <button type="button" onclick="bukaModalRekamWajah()" class="w-full text-center py-1.5 px-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[11px] font-bold transition-all shadow-xs cursor-pointer">
                            Rekam Wajah
                        </button>
                    @else
                        <span class="w-full text-center py-1.5 px-2 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-[10px] font-bold flex items-center justify-center gap-1.5 cursor-default select-none shadow-xs" title="Data biometrik terkunci permanen demi keamanan">
                            <i class="fa-solid fa-lock text-xs text-emerald-600"></i> Biometrik Aktif
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
                    @elseif($user->schedule_type === 'reguler_6_hari')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-100 dark:border-teal-800/50">
                            Reguler 6 Hari
                        </span>
                    @elseif($user->schedule_type === 'reguler_5_hari' || $user->schedule_type === 'normal')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800/50">
                            Reguler 5 Hari
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
                        <span title="Data biometrik wajah telah terkunci & aktif demi keamanan presensi" class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold py-2 px-3 rounded-xl flex items-center space-x-1.5 cursor-default select-none shadow-2xs">
                            <i class="fa-solid fa-lock text-emerald-600 dark:text-emerald-400"></i>
                            <span>Biometrik Aktif</span>
                        </span>
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
            <div class="grid grid-cols-7 sm:grid-cols-10 md:grid-cols-11 lg:grid-cols-16 gap-2 sm:gap-2.5 pt-1">
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
                            class="{{ $day['color_class'] }} h-14 sm:h-16 rounded-2xl flex flex-col items-center justify-center text-white transition-all duration-200 transform hover:scale-105 hover:-translate-y-0.5 active:scale-95 shadow-2xs hover:shadow-md cursor-pointer relative group select-none {{ $isToday ? 'ring-3 ring-sky-400 ring-offset-2 dark:ring-offset-slate-900 shadow-md shadow-sky-500/25 font-black' : '' }}">
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider opacity-85 leading-none">{{ substr($day['day_name'], 0, 3) }}</span>
                        <span class="text-xs sm:text-sm md:text-base font-black tracking-tight leading-none mt-1">{{ $day['day_number'] }}</span>

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

    <style>
        /* Biometrik AI & Liveness Scanner Style (Standar Fintech / KYC Perbankan) */
        @keyframes biometricScanSweep {
            0% {
                top: 10%;
                opacity: 0.15;
            }
            15% {
                opacity: 0.95;
            }
            85% {
                opacity: 0.95;
            }
            100% {
                top: 86%;
                opacity: 0.15;
            }
        }

        @keyframes biometricPulseGlow {
            0%, 100% {
                box-shadow: 0 0 15px 2px rgba(56, 189, 248, 0.4), inset 0 0 15px 2px rgba(56, 189, 248, 0.2);
            }
            50% {
                box-shadow: 0 0 30px 6px rgba(56, 189, 248, 0.75), inset 0 0 25px 4px rgba(56, 189, 248, 0.35);
            }
        }

        @keyframes biometricSuccessGlow {
            0%, 100% {
                box-shadow: 0 0 25px 4px rgba(16, 185, 129, 0.6), inset 0 0 20px 3px rgba(16, 185, 129, 0.3);
            }
            50% {
                box-shadow: 0 0 45px 8px rgba(16, 185, 129, 0.95), inset 0 0 30px 6px rgba(16, 185, 129, 0.5);
            }
        }

        .laser-scanner-beam {
            position: absolute;
            left: 4%;
            right: 4%;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.8) 20%, #38bdf8 50%, rgba(56, 189, 248, 0.8) 80%, transparent);
            box-shadow: 0 0 16px 3px rgba(56, 189, 248, 0.9), 0 0 30px 6px rgba(96, 165, 250, 0.5);
            border-radius: 9999px;
            animation: biometricScanSweep 2.2s ease-in-out infinite alternate;
        }

        .laser-scanner-beam-success {
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.8) 20%, #10b981 50%, rgba(16, 185, 129, 0.8) 80%, transparent) !important;
            box-shadow: 0 0 18px 4px rgba(16, 185, 129, 0.95), 0 0 35px 8px rgba(52, 211, 153, 0.6) !important;
        }

        .oval-reticle-default {
            border: 2.5px solid rgba(56, 189, 248, 0.85);
            animation: biometricPulseGlow 2.5s ease-in-out infinite;
        }

        .oval-reticle-active {
            border: 2.5px solid rgba(245, 158, 11, 0.95) !important;
            box-shadow: 0 0 30px 6px rgba(245, 158, 11, 0.75), inset 0 0 20px 4px rgba(245, 158, 11, 0.35) !important;
        }

        .oval-reticle-success {
            border: 2.5px solid rgba(16, 185, 129, 0.95) !important;
            animation: biometricSuccessGlow 2s ease-in-out infinite;
        }

        .oval-reticle-error {
            border: 2.5px solid rgba(244, 63, 94, 0.95) !important;
            box-shadow: 0 0 30px 6px rgba(244, 63, 94, 0.75), inset 0 0 20px 4px rgba(244, 63, 94, 0.35) !important;
        }
    </style>

    {{-- MODAL KHUSUS: PEREKAMAN / UPDATE BIOMETRIK WAJAH (PUSAT VIEWPORT) --}}
    <div id="modalRekamWajah" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-200">
        {{-- Area klik luar untuk menutup modal --}}
        <div class="fixed inset-0 -z-10 cursor-pointer" onclick="tutupModalRekamWajah()" aria-hidden="true"></div>

        <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[92vh] shadow-2xl overflow-hidden flex flex-col my-auto transform transition-all animate-in fade-in zoom-in-95 duration-200">
            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-sky-50 via-slate-50 to-blue-50/40 dark:from-slate-800/90 dark:via-slate-800/80 dark:to-slate-800/90 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/60 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-face-viewfinder"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base leading-tight truncate">Perekaman Biometrik Wajah</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Pendaftaran data AI Face-Recognition karyawan</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalRekamWajah()" class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer border border-slate-200/70 dark:border-slate-700 shadow-2xs shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-5 sm:p-6 space-y-3.5 flex-1 overflow-y-auto">
                <div class="p-3 bg-sky-50/70 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/50 rounded-2xl text-xs text-sky-800 dark:text-sky-300 flex items-start gap-2.5 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-sky-500 mt-0.5 shrink-0 text-sm"></i>
                    <span>Posisikan wajah Anda tegak lurus di dalam bingkai scanner. Pastikan pencahayaan cukup dan wajah terlihat jelas tanpa masker atau kacamata gelap.</span>
                </div>

                {{-- Container Video Kamera Perekaman Potret Canggih Standar Fintech KYC --}}
                <div class="relative bg-slate-950 rounded-3xl overflow-hidden w-full max-w-[320px] sm:max-w-[340px] mx-auto aspect-[3/4] flex items-center justify-center border-2 border-slate-700/80 shadow-2xl shrink-0 group select-none">
                    
                    {{-- 1. Video Stream --}}
                    <video id="videoRekamWajah"
                           autoplay
                           playsinline
                           muted
                           style="transform: scaleX(-1) !important; -webkit-transform: scaleX(-1) !important;"
                           class="w-full h-full object-cover"></video>

                    {{-- 2. Canvas Landmark AI Processing --}}
                    <canvas id="canvasRekamWajah" class="absolute inset-0 w-full h-full pointer-events-none object-cover" style="transform: scaleX(-1) !important;"></canvas>

                    {{-- 3. SVG Mask Cutout: Meneduhkan area di luar lingkaran oval wajah --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" width="100%" height="100%">
                        <defs>
                            <mask id="faceMaskRekam" maskContentUnits="objectBoundingBox">
                                <rect width="1" height="1" fill="white" />
                                <ellipse cx="0.5" cy="0.46" rx="0.33" ry="0.36" fill="black" />
                            </mask>
                        </defs>
                        <rect width="100%" height="100%" fill="rgba(2, 6, 23, 0.45)" mask="url(#faceMaskRekam)" />
                    </svg>

                    {{-- 4. Cybernetic HUD Corner Brackets --}}
                    <div class="absolute top-3.5 left-3.5 w-5 h-5 border-t-2 border-l-2 border-sky-400/80 rounded-tl-lg pointer-events-none z-20"></div>
                    <div class="absolute top-3.5 right-3.5 w-5 h-5 border-t-2 border-r-2 border-sky-400/80 rounded-tr-lg pointer-events-none z-20"></div>
                    <div class="absolute bottom-16 left-3.5 w-5 h-5 border-b-2 border-l-2 border-sky-400/80 rounded-bl-lg pointer-events-none z-20"></div>
                    <div class="absolute bottom-16 right-3.5 w-5 h-5 border-b-2 border-r-2 border-sky-400/80 rounded-br-lg pointer-events-none z-20"></div>

                    {{-- 5. Top Bar HUD: Security Badge & AI Status --}}
                    <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-20 pointer-events-none">
                        <div class="bg-slate-900/85 backdrop-blur-md text-sky-400 text-[10px] font-mono font-bold px-2.5 py-1 rounded-full border border-sky-500/40 flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                            <span>AI ENROLLMENT • 3D</span>
                        </div>

                        <div class="bg-slate-900/85 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full border border-slate-700/80 flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>ISO/IEC 30107</span>
                        </div>
                    </div>

                    {{-- 6. The Biometric Face Oval Guide (Reticle) --}}
                    <div id="ovalReticleRekam" class="absolute top-[10%] left-[17%] right-[17%] bottom-[18%] rounded-[50%] oval-reticle-default pointer-events-none z-10 transition-all duration-300 flex items-center justify-center">
                        {{-- Reticle Tick Crosshairs --}}
                        <span class="absolute -top-1 w-3 h-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -bottom-1 w-3 h-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -left-1 h-3 w-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -right-1 h-3 w-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>

                        {{-- Head Silhouette Wireframe Guide when idle --}}
                        <div id="faceSilhouetteRekam" class="opacity-25 transition-opacity duration-300 text-sky-400 flex flex-col items-center select-none">
                            <i class="fa-solid fa-user text-6xl sm:text-7xl"></i>
                        </div>

                        {{-- Sweeping Laser Scan Line --}}
                        <div id="laserBeamRekam" class="laser-scanner-beam pointer-events-none"></div>
                    </div>

                    {{-- 7. Kotak Status Scanning HUD --}}
                    <div id="statusRekamWajahBox" class="absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-slate-700/80 shadow-2xl flex items-center justify-center gap-1.5 transition-all">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat model biometrik...
                    </div>
                </div>

                {{-- Footnote Standar Keamanan Finansial --}}
                <div class="flex items-center justify-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium pt-0.5">
                    <i class="fa-solid fa-shield-halved text-sky-500 text-xs"></i>
                    <span>Enkripsi Biometrik 128-Point Facial Vector • Aman & Rahasia</span>
                </div>
            </div>

            <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800 flex justify-end items-center gap-3 shrink-0">
                <button type="button" onclick="tutupModalRekamWajah()" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-xl text-xs sm:text-sm font-semibold transition-all active:scale-95 cursor-pointer flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-xmark text-slate-400 dark:text-slate-400"></i>
                    <span>Batal</span>
                </button>
                <button type="button" onclick="simpanBiometrikWajah()" id="btnSimpanWajah" disabled class="px-5 py-2.5 bg-slate-200 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 cursor-not-allowed select-none shadow-none">
                    <i class="fa-solid fa-floppy-disk text-slate-400 dark:text-slate-500"></i>
                    <span>Simpan Data Wajah</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL STEP 1: VERIFIKASI BIOMETRIK WAJAH & GPS PRESENSI (PUSAT VIEWPORT) --}}
    <div id="modalAbsensi" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-200">
        {{-- Area klik luar untuk menutup modal --}}
        <div class="fixed inset-0 -z-10 cursor-pointer" onclick="tutupModalAbsen()" aria-hidden="true"></div>

        <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[92vh] shadow-2xl overflow-hidden flex flex-col my-auto transform transition-all animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-slate-50 via-sky-50/40 to-indigo-50/30 dark:from-slate-800/90 dark:via-slate-800/80 dark:to-slate-800/90 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/60 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-camera-rotate"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 id="judulModalAbsen" class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base leading-tight truncate">Verifikasi Absensi</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Pemindaian biometrik wajah & deteksi radius GPS</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalAbsen()" class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer border border-slate-200/70 dark:border-slate-700 shadow-2xs shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Isian Content --}}
            <div class="p-5 sm:p-6 space-y-3.5 flex-1 overflow-y-auto">

                {{-- Status GPS --}}
                <div id="statusLokasiBox" class="p-3 sm:p-3.5 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl flex items-center justify-between transition-all shrink-0">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-slate-200/60 dark:bg-slate-700/60 flex items-center justify-center text-slate-500 dark:text-slate-300 shrink-0">
                            <i id="iconLokasi" class="fa-solid fa-location-dot text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi Terkini GPS:</span>
                            <span id="textNamaLokasi" class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate block">Mendeteksi lokasi GPS...</span>
                        </div>
                    </div>
                    <div id="reloadContainer" class="hidden text-right pl-2 border-l border-rose-200/60 dark:border-rose-800/60 shrink-0">
                        <span class="text-[10px] text-rose-600 dark:text-rose-400 block font-semibold leading-tight">Memuat Ulang</span>
                        <span id="reloadCountdown" class="text-xs font-mono font-bold text-rose-700 dark:text-rose-300 animate-pulse">5s</span>
                    </div>
                </div>

                {{-- Container Video Kamera Verifikasi Wajah Potret Canggih Standar Fintech KYC --}}
                <div class="relative bg-slate-950 rounded-3xl overflow-hidden w-full max-w-[320px] sm:max-w-[340px] mx-auto aspect-[3/4] flex items-center justify-center border-2 border-slate-700/80 shadow-2xl shrink-0 group select-none">
                    
                    {{-- 1. Video Stream --}}
                    <video id="webcamVideo"
                        autoplay
                        playsinline
                        muted
                        style="transform: scaleX(-1) !important; -webkit-transform: scaleX(-1) !important;"
                        class="w-full h-full object-cover"></video>

                    {{-- 2. Canvas Landmark AI Processing --}}
                    <canvas id="webcamCanvas" class="absolute inset-0 w-full h-full pointer-events-none object-cover" style="transform: scaleX(-1) !important;"></canvas>
                    
                    {{-- 3. SVG Mask Cutout: Meneduhkan area di luar lingkaran oval wajah --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" width="100%" height="100%">
                        <defs>
                            <mask id="faceMaskAbsen" maskContentUnits="objectBoundingBox">
                                <rect width="1" height="1" fill="white" />
                                <ellipse cx="0.5" cy="0.46" rx="0.33" ry="0.36" fill="black" />
                            </mask>
                        </defs>
                        <rect width="100%" height="100%" fill="rgba(2, 6, 23, 0.45)" mask="url(#faceMaskAbsen)" />
                    </svg>

                    {{-- 4. Cybernetic HUD Corner Brackets --}}
                    <div class="absolute top-3.5 left-3.5 w-5 h-5 border-t-2 border-l-2 border-sky-400/80 rounded-tl-lg pointer-events-none z-20"></div>
                    <div class="absolute top-3.5 right-3.5 w-5 h-5 border-t-2 border-r-2 border-sky-400/80 rounded-tr-lg pointer-events-none z-20"></div>
                    <div class="absolute bottom-16 left-3.5 w-5 h-5 border-b-2 border-l-2 border-sky-400/80 rounded-bl-lg pointer-events-none z-20"></div>
                    <div class="absolute bottom-16 right-3.5 w-5 h-5 border-b-2 border-r-2 border-sky-400/80 rounded-br-lg pointer-events-none z-20"></div>

                    {{-- 5. Top Bar HUD: Security Badge & Liveness Indicator --}}
                    <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-20 pointer-events-none">
                        <div class="bg-slate-900/85 backdrop-blur-md text-sky-400 text-[10px] font-mono font-bold px-2.5 py-1 rounded-full border border-sky-500/40 flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                            <span>KYC • BIOMETRIK AI</span>
                        </div>

                        <div id="livenessBadge" class="bg-slate-900/85 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full border border-slate-700/80 flex items-center gap-1.5 shadow-sm transition-all pointer-events-none">
                            <span id="livenessBadgeDot" class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            <span id="livenessBadgeText">Liveness: Menunggu Wajah</span>
                        </div>
                    </div>

                    {{-- 6. The Biometric Face Oval Guide (Reticle) --}}
                    <div id="ovalReticleAbsen" class="absolute top-[10%] left-[17%] right-[17%] bottom-[18%] rounded-[50%] oval-reticle-default pointer-events-none z-10 transition-all duration-300 flex items-center justify-center">
                        {{-- Reticle Tick Crosshairs --}}
                        <span class="absolute -top-1 w-3 h-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -bottom-1 w-3 h-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -left-1 h-3 w-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>
                        <span class="absolute -right-1 h-3 w-0.5 bg-sky-400 rounded-full shadow-[0_0_8px_#38bdf8]"></span>

                        {{-- Head Silhouette Wireframe Guide when idle --}}
                        <div id="faceSilhouetteAbsen" class="opacity-25 transition-opacity duration-300 text-sky-400 flex flex-col items-center select-none">
                            <i class="fa-solid fa-user text-6xl sm:text-7xl"></i>
                        </div>

                        {{-- Sweeping Laser Scan Line --}}
                        <div id="laserBeamAbsen" class="laser-scanner-beam pointer-events-none"></div>
                    </div>

                    {{-- 7. Floating Instruction HUD Pill --}}
                    <div id="cameraStatus" class="absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-slate-700/80 shadow-2xl flex items-center justify-center gap-1.5 transition-all">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mempersiapkan verifikasi biometrik...
                    </div>
                </div>

                {{-- Footnote Standar Keamanan Finansial --}}
                <div class="flex items-center justify-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium pt-0.5">
                    <i class="fa-solid fa-shield-halved text-sky-500 text-xs"></i>
                    <span>Sistem Verifikasi Biometrik Standar ISO/IEC 30107-3 Anti-Spoofing</span>
                </div>

                <div id="facePromptNotice" class="hidden p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-800 dark:text-amber-300 text-xs flex flex-col sm:flex-row items-center justify-between gap-2.5 shadow-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-base shrink-0"></i>
                        <span class="font-medium">Anda belum merekam biometrik wajah. Silakan lakukan pendaftaran wajah terlebih dahulu sebelum presensi!</span>
                    </div>
                    <button type="button" onclick="tutupModalAbsen(); bukaModalRekamWajah();" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shrink-0 cursor-pointer shadow-xs transition-all active:scale-95">
                        <i class="fa-solid fa-camera mr-1"></i> Rekam Sekarang
                    </button>
                </div>
            </div>

            {{-- Footer Tombol --}}
            <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800 flex justify-end items-center gap-3 shrink-0">
                <button type="button" onclick="tutupModalAbsen()" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-xl text-xs sm:text-sm font-semibold transition-all active:scale-95 cursor-pointer flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-xmark text-slate-400 dark:text-slate-400"></i>
                    <span>Batal</span>
                </button>
                <button type="button" onclick="verifikasiDanLanjut()" id="btnVerifikasiLanjut" disabled class="px-5 py-2.5 bg-slate-200 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 cursor-not-allowed select-none shadow-none">
                    <i class="fa-solid fa-circle-check text-slate-400 dark:text-slate-500"></i>
                    <span>Lanjutkan Presensi</span>
                </button>
            </div>

        </div>
    </div>

    {{-- MODAL STEP 2: KONFIRMASI DETAIL ABSENSI, ALASAN WAJIB & BUKTI WATERMARK (PUSAT VIEWPORT) --}}
    <div id="modalKonfirmasiAbsen" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-200">
        {{-- Area klik luar untuk menutup modal --}}
        <div class="fixed inset-0 -z-10 cursor-pointer" onclick="tutupModalAbsen()" aria-hidden="true"></div>

        <div class="relative bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[92vh] shadow-2xl overflow-hidden flex flex-col my-auto transform transition-all animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-slate-50 via-sky-50/40 to-indigo-50/30 dark:from-slate-800/90 dark:via-slate-800/80 dark:to-slate-800/90 p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <div class="flex items-center space-x-3.5 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/60 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base leading-tight truncate">Konfirmasi Detail Presensi</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">Tinjau ringkasan waktu, lokasi, dan alasan</p>
                    </div>
                </div>
                <button type="button" onclick="kembaliKeKamera()" class="w-8 h-8 rounded-full bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-all cursor-pointer border border-slate-200/70 dark:border-slate-700 shadow-2xs shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form & Isian Modal --}}
            <form id="formAbsensi" onsubmit="submitAbsensi(event)" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-y-auto">
                @csrf
                <input type="hidden" id="absen_type" name="type" value="in">
                <input type="hidden" id="absen_lat" name="latitude">
                <input type="hidden" id="absen_long" name="longitude">
                <input type="hidden" id="absen_is_face_verified" name="is_face_verified" value="0">

                <div class="p-5 sm:p-6 space-y-3.5 flex-1 overflow-y-auto">

                    {{-- Informasi Detail Rincian Absen --}}
                    <div class="space-y-2.5 bg-slate-50/90 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 text-xs">
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Status Lokasi:</span>
                            <span id="txtStatusRadius" class="font-bold text-emerald-600 dark:text-emerald-400">Di Dalam Area kerja</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Lokasi GPS:</span>
                            <span id="txtNamaLokasiConfirm" class="font-semibold text-slate-800 dark:text-slate-100 truncate max-w-[180px]">--</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Verifikasi Wajah:</span>
                            <span id="txtStatusBiometrik" class="font-bold text-sky-600 dark:text-sky-400 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-sky-500"></i> Terverifikasi
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 dark:border-slate-700/60 pb-2">
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
                    <div id="boxWarningStatus" class="hidden p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-800 dark:text-amber-300 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-base mt-0.5 shrink-0"></i>
                        <span id="txtWarningMessage" class="leading-relaxed">Harap lengkapi alasan di bawah ini sebelum mengirim absensi.</span>
                    </div>

                    {{-- Input Alasan Khusus --}}
                    <div id="wrapperAlasan" class="space-y-1.5">
                        <label id="labelAlasan" class="text-xs font-bold text-rose-700 dark:text-rose-400 block">Alasan Khusus / Keterangan:</label>
                        <textarea id="inputAlasan" name="reason" rows="2" class="w-full p-3 text-xs border border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20 text-slate-800 dark:text-slate-100 rounded-2xl focus:ring-2 focus:ring-rose-400 outline-none resize-none transition-all" placeholder="Tuliskan alasan lengkap Anda di sini..."></textarea>
                        <span id="errorAlasanMsg" class="text-[11px] text-rose-600 dark:text-rose-400 hidden font-semibold">* Alasan wajib diisi!</span>
                    </div>

                    {{-- Input Bukti Pendukung (Opsional dengan Watermark) --}}
                    <div id="wrapperBukti" class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-200 block flex items-center justify-between">
                            <span>Lampiran Bukti Pendukung <span class="text-slate-400 font-normal">(Opsional):</span></span>
                            <span id="txtNamaFileTerpilih" class="text-[10px] text-sky-600 dark:text-sky-400 font-semibold truncate max-w-[150px]"></span>
                        </label>

                        <input type="file" id="inputEvidenceFile" name="evidence" accept="image/*,.pdf" class="hidden" onchange="handleEvidenceFileChange(this)">

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="ambilFotoBuktiKamera()" class="flex-1 py-2.5 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-slate-200 dark:border-slate-700 shadow-2xs">
                                <i class="fa-solid fa-camera text-sky-600 dark:text-sky-400"></i>
                                <span>Kamera Langsung</span>
                            </button>
                            <button type="button" onclick="pilihDokumenBukti()" class="flex-1 py-2.5 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer border border-slate-200 dark:border-slate-700 shadow-2xs">
                                <i class="fa-solid fa-folder-open text-amber-600 dark:text-amber-400"></i>
                                <span>Galeri / File</span>
                            </button>
                        </div>

                        {{-- Preview Thumbnail Bukti Jika Ada --}}
                        <div id="previewBuktiContainer" class="hidden relative p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center space-x-2.5 overflow-hidden">
                                <img id="imgPreviewBukti" src="" class="w-10 h-10 object-cover rounded-xl border border-slate-200 dark:border-slate-700 hidden" alt="Preview Bukti">
                                <div id="iconPdfBukti" class="w-10 h-10 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center text-lg hidden">
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
                <div class="p-4 sm:p-5 bg-slate-50/80 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-slate-800 flex justify-end items-center gap-3 shrink-0">
                    <button type="button" onclick="kembaliKeKamera()" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-xl text-xs sm:text-sm font-semibold transition-all active:scale-95 cursor-pointer flex items-center gap-2 shadow-xs">
                        <i class="fa-solid fa-arrow-left text-slate-400 dark:text-slate-400"></i>
                        <span>Kembali</span>
                    </button>
                    <button type="submit" id="btnSubmitAbsen" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-lg shadow-emerald-600/30 dark:shadow-emerald-500/25 active:scale-95 cursor-pointer flex items-center gap-2 border border-emerald-500 dark:border-emerald-400/40">
                        <i class="fa-solid fa-paper-plane text-white"></i>
                        <span>Kirim Absensi Sekarang</span>
                    </button>
                </div>
            </form>
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
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 bg-purple-50/60 dark:bg-purple-950/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-600 dark:bg-purple-500 text-white flex items-center justify-center text-lg shadow-md shadow-purple-600/25 shrink-0">
                    <i class="fa-solid fa-money-bill-transfer text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight">Riwayat CAR Anda</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 border border-purple-200/60 dark:border-purple-500/30">
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
                <a href="{{ url('/car/ajukan') }}" class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-purple-600/20 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
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
                        <tr class="hover:bg-purple-50/30 dark:hover:bg-slate-800/40 transition-all">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/40 inline-block mb-1">
                                    {{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}
                                </span>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    <i class="fa-regular fa-clock text-[10px] text-purple-500"></i>
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
                                                <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold">({{ $detail->jumlah }} {{ $detail->satuan }})</span>
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
                                                class="px-2.5 py-1 bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white dark:bg-purple-500/15 dark:hover:bg-purple-600 dark:text-purple-300 dark:hover:text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 transition-all border border-purple-200/80 dark:border-purple-500/30 shadow-2xs cursor-pointer active:scale-95">
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
                                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-500/10 text-purple-500 flex items-center justify-center text-2xl mx-auto mb-3 border border-purple-100 dark:border-purple-500/20">
                                    <i class="fa-solid fa-money-bill-transfer"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan CAR</h4>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 mb-3">Anda belum pernah mengajukan permohonan CAR pada periode ini.</p>
                                <a href="{{ url('/car/ajukan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
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
{{-- Load Pustaka SweetAlert2 & Face-API Biometrik CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

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
            'detailCutiModal',
            'modalRekamWajah',
            'modalAbsensi',
            'modalKonfirmasiAbsen'
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
            const modalRekam = document.getElementById("modalRekamWajah");
            if (modalRekam && !modalRekam.classList.contains("hidden")) {
                tutupModalRekamWajah();
            }
            const modalAbsen = document.getElementById("modalAbsensi");
            const modalKonf = document.getElementById("modalKonfirmasiAbsen");
            if ((modalAbsen && !modalAbsen.classList.contains("hidden")) || (modalKonf && !modalKonf.classList.contains("hidden"))) {
                tutupModalAbsen();
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

    // 4. BIOMETRIC FACE RECOGNITION & GPS GEOFENCING ATTENDANCE
    let mediaStream = null;
    let rekamMediaStream = null;
    let faceApiModelsLoaded = false;
    let faceDetectionInterval = null;
    let rekamFaceDetectionInterval = null;
    let latestRecordedDescriptor = null;
    let isFaceVerified = false;

    // Liveness Detection (Anti-Spoofing) States
    let isLivenessPassed = false;
    let blinkCount = 0;
    const REQUIRED_BLINKS = 1;
    let isEyeClosedState = false;
    let eyeClosedConsecutiveFrames = 0;
    let livenessStartTime = null;
    let isSpoofingCooldown = false;
    const LIVENESS_TIMEOUT_MS = 8000; // 8 detik toleransi jika wajah cocok tanpa kedipan sama sekali (indikasi foto)
    const EAR_THRESHOLD_CLOSED = 0.21;
    const EAR_THRESHOLD_OPEN = 0.25;

    // Perhitungan Jarak Euclidean 2 Titik Koordinat Landmark
    function calculateLandmarkDistance(pt1, pt2) {
        if (!pt1 || !pt2) return 0;
        return Math.hypot(pt1.x - pt2.x, pt1.y - pt2.y);
    }

    // Perhitungan Eye Aspect Ratio (EAR) untuk Deteksi Kedipan Mata
    function calculateEAR(eyePoints) {
        if (!Array.isArray(eyePoints) || eyePoints.length < 6) return 0;
        // Jarak vertikal kelopak mata
        const v1 = calculateLandmarkDistance(eyePoints[1], eyePoints[5]);
        const v2 = calculateLandmarkDistance(eyePoints[2], eyePoints[4]);
        // Jarak horizontal sudut mata
        const h = calculateLandmarkDistance(eyePoints[0], eyePoints[3]);
        if (h === 0) return 0;
        return (v1 + v2) / (2.0 * h);
    }

    function updateLivenessBadge(status, text) {
        const badge = document.getElementById('livenessBadge');
        const badgeDot = document.getElementById('livenessBadgeDot');
        const badgeText = document.getElementById('livenessBadgeText');
        if (!badge || !badgeDot || !badgeText) return;

        badgeText.innerText = text;
        if (status === 'passed') {
            badge.className = "bg-emerald-950/85 backdrop-blur-md text-emerald-300 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-500/50 flex items-center gap-1.5 shadow-sm animate-pulse pointer-events-none";
            badgeDot.className = "w-1.5 h-1.5 rounded-full bg-emerald-400";
        } else if (status === 'active') {
            badge.className = "bg-amber-950/85 backdrop-blur-md text-amber-300 text-[10px] font-bold px-2.5 py-1 rounded-full border border-amber-500/50 flex items-center gap-1.5 shadow-sm pointer-events-none";
            badgeDot.className = "w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping";
        } else if (status === 'failed') {
            badge.className = "bg-rose-950/85 backdrop-blur-md text-rose-300 text-[10px] font-bold px-2.5 py-1 rounded-full border border-rose-500/50 flex items-center gap-1.5 shadow-sm pointer-events-none";
            badgeDot.className = "w-1.5 h-1.5 rounded-full bg-rose-500";
        } else {
            badge.className = "bg-slate-900/85 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full border border-slate-700/80 flex items-center gap-1.5 shadow-sm pointer-events-none";
            badgeDot.className = "w-1.5 h-1.5 rounded-full bg-slate-400";
        }

        updateOvalReticle(status);
    }

    function resetLivenessState() {
        isLivenessPassed = false;
        blinkCount = 0;
        isEyeClosedState = false;
        eyeClosedConsecutiveFrames = 0;
        livenessStartTime = null;
        updateLivenessBadge('idle', 'Liveness: Siaga');
    }

    let isSubmittingFaceRegister = false;
    let stableDetectionCount = 0;
    let isAutoSubmittingAttendance = false;
    let stableAttendanceFaceCount = 0;
    let latestCameraFaceDescriptor = null;

    let countdownInterval = null;
    let secondsLeft = 5;
    let isUserInRadius = false;
    let isLateOrEarly = false;

    const daftarStasiun = JSON.parse('{!! json_encode($daftarStasiun ?? []) !!}');
    const todaySchedule = JSON.parse('{!! json_encode($todaySchedule ?? []) !!}');
    let userFaceDescriptor = {!! json_encode(auth()->user()->face_descriptor) !!};

    function hasValidFaceDescriptor(descriptor) {
        return Array.isArray(descriptor) && descriptor.length === 128;
    }

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
            console.log("Model Biometrik Wajah berhasil dimuat.");
            return true;
        } catch (err) {
            console.error("Gagal memuat model face-api:", err);
            return false;
        }
    }

    // ==========================================
    // HELPER TAMPILAN TOMBOL AKSI PRESENSI & BIOMETRIK
    // ==========================================
    function setBtnLanjutState(isEnabled) {
        const btn = document.getElementById('btnVerifikasiLanjut');
        if (!btn) return;
        btn.disabled = !isEnabled;
        if (isEnabled) {
            btn.className = "px-5 py-2.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-600 hover:from-blue-500 hover:via-indigo-500 hover:to-blue-500 active:from-blue-700 active:to-indigo-700 text-white border border-blue-500/40 text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 dark:shadow-blue-500/25 transition-all flex items-center gap-2 cursor-pointer active:scale-95";
            btn.innerHTML = `<i class="fa-solid fa-circle-check text-white"></i> <span>Lanjutkan Presensi</span>`;
        } else {
            btn.className = "px-5 py-2.5 bg-slate-200 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 cursor-not-allowed select-none shadow-none";
            btn.innerHTML = `<i class="fa-solid fa-circle-check text-slate-400 dark:text-slate-500"></i> <span>Lanjutkan Presensi</span>`;
        }
    }

    function setBtnSimpanWajahState(isEnabled) {
        const btn = document.getElementById('btnSimpanWajah');
        if (!btn) return;
        btn.disabled = !isEnabled;
        if (isEnabled) {
            btn.className = "px-5 py-2.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-600 hover:from-blue-500 hover:via-indigo-500 hover:to-blue-500 active:from-blue-700 active:to-indigo-700 text-white border border-blue-500/40 text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 dark:shadow-blue-500/25 transition-all flex items-center gap-2 cursor-pointer active:scale-95";
            btn.innerHTML = `<i class="fa-solid fa-floppy-disk text-white"></i> <span>Simpan Data Wajah</span>`;
        } else {
            btn.className = "px-5 py-2.5 bg-slate-200 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-all flex items-center gap-2 cursor-not-allowed select-none shadow-none";
            btn.innerHTML = `<i class="fa-solid fa-floppy-disk text-slate-400 dark:text-slate-500"></i> <span>Simpan Data Wajah</span>`;
        }
    }

    function updateOvalReticle(status) {
        const oval = document.getElementById('ovalReticleAbsen');
        const laser = document.getElementById('laserBeamAbsen');
        const silhouette = document.getElementById('faceSilhouetteAbsen');
        if (!oval) return;

        oval.classList.remove('oval-reticle-default', 'oval-reticle-active', 'oval-reticle-success', 'oval-reticle-error');

        if (status === 'passed') {
            oval.classList.add('oval-reticle-success');
            if (laser) {
                laser.classList.add('laser-scanner-beam-success');
                laser.style.display = 'block';
            }
            if (silhouette) silhouette.classList.add('opacity-0');
        } else if (status === 'active') {
            oval.classList.add('oval-reticle-active');
            if (laser) {
                laser.classList.remove('laser-scanner-beam-success');
                laser.style.display = 'block';
            }
            if (silhouette) silhouette.classList.add('opacity-0');
        } else if (status === 'failed') {
            oval.classList.add('oval-reticle-error');
            if (laser) laser.style.display = 'none';
            if (silhouette) silhouette.classList.add('opacity-0');
        } else {
            oval.classList.add('oval-reticle-default');
            if (laser) {
                laser.classList.remove('laser-scanner-beam-success');
                laser.style.display = 'block';
            }
            if (silhouette) silhouette.classList.remove('opacity-0');
        }
    }

    function updateOvalReticleRekam(status) {
        const oval = document.getElementById('ovalReticleRekam');
        const laser = document.getElementById('laserBeamRekam');
        const silhouette = document.getElementById('faceSilhouetteRekam');
        if (!oval) return;

        oval.classList.remove('oval-reticle-default', 'oval-reticle-active', 'oval-reticle-success', 'oval-reticle-error');

        if (status === 'passed') {
            oval.classList.add('oval-reticle-success');
            if (laser) {
                laser.classList.add('laser-scanner-beam-success');
                laser.style.display = 'block';
            }
            if (silhouette) silhouette.classList.add('opacity-0');
        } else if (status === 'failed') {
            oval.classList.add('oval-reticle-error');
            if (laser) laser.style.display = 'none';
            if (silhouette) silhouette.classList.add('opacity-0');
        } else {
            oval.classList.add('oval-reticle-default');
            if (laser) {
                laser.classList.remove('laser-scanner-beam-success');
                laser.style.display = 'block';
            }
            if (silhouette) silhouette.classList.remove('opacity-0');
        }
    }

    // ==========================================
    // ALUR PEREKAMAN BIOMETRIK WAJAH (REGISTRATION)
    // ==========================================
    async function bukaModalRekamWajah() {
        @if(!empty($user->face_descriptor))
            Swal.fire({
                icon: 'info',
                title: 'Biometrik Terkunci',
                text: 'Data biometrik wajah Anda telah terverifikasi dan terkunci demi keamanan presensi. Hubungi Administrator / HRD jika memerlukan kalibrasi ulang.',
                confirmButtonColor: '#0284c7',
                customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold' }
            });
            return;
        @endif

        const modal = document.getElementById('modalRekamWajah');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        const statusBox = document.getElementById('statusRekamWajahBox');
        setBtnSimpanWajahState(false);
        latestRecordedDescriptor = null;
        isSubmittingFaceRegister = false;
        stableDetectionCount = 0;

        if (statusBox) {
            statusBox.className = "absolute bottom-2.5 left-2.5 right-2.5 bg-slate-900/85 backdrop-blur-md text-white text-[11px] px-3.5 py-2 rounded-xl z-10 text-center font-medium border border-slate-700/60 shadow-lg flex items-center justify-center gap-1.5";
            statusBox.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mempersiapkan model biometrik wajah...`;
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
                statusBox.className = "absolute bottom-2.5 left-2.5 right-2.5 bg-rose-600/95 backdrop-blur-md text-white text-[11px] px-3.5 py-2 rounded-xl z-10 text-center font-bold border border-rose-500/60 shadow-lg flex items-center justify-center gap-1.5";
                statusBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Izin kamera ditolak atau tidak tersedia.`;
            }
        }
    }

    function mulaiDeteksiWajahPerekaman() {
        const video = document.getElementById('videoRekamWajah');
        const canvas = document.getElementById('canvasRekamWajah');
        const statusBox = document.getElementById('statusRekamWajahBox');

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
                setBtnSimpanWajahState(true);

                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                stableDetectionCount++;
                updateOvalReticleRekam('passed');
                if (!isSubmittingFaceRegister && stableDetectionCount >= 2) {
                    isSubmittingFaceRegister = true;
                    if (rekamFaceDetectionInterval) {
                        clearInterval(rekamFaceDetectionInterval);
                        rekamFaceDetectionInterval = null;
                    }

                    if (statusBox) {
                        statusBox.className = "absolute bottom-3 left-3 right-3 bg-emerald-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-emerald-500/60 shadow-2xl flex items-center justify-center gap-2 animate-pulse";
                        statusBox.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> <span>Wajah Terdeteksi Jelas! Menyimpan biometrik otomatis...</span>`;
                    }

                    setTimeout(() => {
                        simpanBiometrikWajah();
                    }, 500);
                } else if (!isSubmittingFaceRegister) {
                    if (statusBox) {
                        statusBox.className = "absolute bottom-3 left-3 right-3 bg-sky-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-sky-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                        statusBox.innerHTML = `<i class="fa-solid fa-camera mr-1"></i> Wajah Terdeteksi! Tahan posisi...`;
                    }
                }
            } else {
                stableDetectionCount = 0;
                latestRecordedDescriptor = null;
                setBtnSimpanWajahState(false);
                updateOvalReticleRekam('idle');

                if (statusBox && !isSubmittingFaceRegister) {
                    statusBox.className = "absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-slate-700/80 shadow-2xl flex items-center justify-center gap-1.5";
                    statusBox.innerHTML = `<i class="fa-solid fa-arrows-to-eye text-sky-400 mr-1"></i> Posisikan wajah Anda di dalam lingkaran oval...`;
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

        setBtnSimpanWajahState(false);
        updateOvalReticleRekam('idle');

        const modal = document.getElementById('modalRekamWajah');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
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
            setBtnSimpanWajahState(true);
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
                    const gpsAccuracy = pos.coords.accuracy || 0;
                    window.currentGpsAccuracy = gpsAccuracy;

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
                            statusBox.className = "p-3 sm:p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center justify-between transition-all shrink-0";
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
                            statusBox.className = "p-3 sm:p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/80 rounded-2xl flex items-center justify-between transition-all shrink-0";
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
                        statusBox.className = "p-3 sm:p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl flex items-center justify-between transition-all shrink-0";
                        iconLokasi.className = "fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400 text-sm";
                        textLokasi.className = "text-xs font-bold text-amber-700 dark:text-amber-300";
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

        // Cegah bypass di awal: Jika belum merekam biometrik wajah, tolak presensi langsung
        if (!hasValidFaceDescriptor(userFaceDescriptor)) {
            Swal.fire({
                icon: 'warning',
                title: 'Biometrik Wajah Belum Terdaftar',
                text: 'Anda belum merekam biometrik wajah. Silakan lakukan pendaftaran wajah terlebih dahulu sebelum presensi!',
                confirmButtonText: '<i class="fa-solid fa-camera mr-1"></i> Rekam Wajah Sekarang',
                confirmButtonColor: '#0284c7',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                cancelButtonColor: '#64748b',
                customClass: { popup: 'rounded-2xl', confirmButton: 'px-5 py-2.5 rounded-xl font-bold', cancelButton: 'px-5 py-2.5 rounded-xl font-medium' }
            }).then((result) => {
                if (result.isConfirmed) {
                    bukaModalRekamWajah();
                }
            });
            return;
        }

        isFaceVerified = false;
        resetLivenessState();
        isSpoofingCooldown = false;
        isAutoSubmittingAttendance = false;
        stableAttendanceFaceCount = 0;

        // [SEC-01 FIX] Ambil time-based biometric challenge nonce dari server
        window.currentBiometricNonce = null;
        fetch('{{ route("attendance.biometric_challenge") }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.nonce) {
                    window.currentBiometricNonce = res.nonce;
                }
            })
            .catch(err => console.error("Gagal inisialisasi nonce biometrik:", err));
        setBtnLanjutState(false);

        const statusBox = document.getElementById('statusLokasiBox');
        const textLokasi = document.getElementById('textNamaLokasi');
        const iconLokasi = document.getElementById('iconLokasi');
        const reloadContainer = document.getElementById('reloadContainer');
        const faceNotice = document.getElementById('facePromptNotice');

        if (statusBox && textLokasi && iconLokasi) {
            statusBox.className = "p-3 sm:p-3.5 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl flex items-center justify-between transition-all shrink-0";
            iconLokasi.className = "fa-solid fa-spinner fa-spin text-slate-400 text-sm";
            textLokasi.innerText = "Mendeteksi posisi GPS Anda...";
            if (reloadContainer) reloadContainer.classList.add('hidden');
        }

        if (faceNotice) {
            if (!hasValidFaceDescriptor(userFaceDescriptor)) {
                faceNotice.classList.remove('hidden');
            } else {
                faceNotice.classList.add('hidden');
            }
        }

        const modal = document.getElementById('modalAbsensi');
        const modalKonf = document.getElementById('modalKonfirmasiAbsen');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        if (modalKonf && modalKonf.parentElement !== document.body) {
            document.body.appendChild(modalKonf);
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
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
                camStatus.className = "absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-slate-700/80 shadow-2xl flex items-center justify-center gap-1.5";
                camStatus.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat model biometrik...`;
            }

            await ensureFaceApiLoaded();
            mulaiVerifikasiWajahRealtime();
        } catch (err) {
            console.error("Gagal Akses Kamera:", err);
            if (camStatus) {
                camStatus.className = "absolute bottom-3 left-3 right-3 bg-rose-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-rose-500/60 shadow-2xl flex items-center justify-center gap-1.5";
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
            if (!faceApiModelsLoaded || video.paused || video.ended || !video.videoWidth || isSpoofingCooldown) return;

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
                let confidence = 0;

                // Hitung Euclidean Distance HANYA JIKA descriptor biometrik referensi valid (128 floats)
                if (hasValidFaceDescriptor(userFaceDescriptor)) {
                    const distance = faceapi.euclideanDistance(detection.descriptor, userFaceDescriptor);
                    confidence = Math.max(0, Math.min(100, Math.round((1 - distance) * 100)));

                    // Threshold Euclidean Distance: <= 0.55 adalah MATCH
                    if (distance <= 0.55) {
                        isMatch = true;
                        latestCameraFaceDescriptor = Array.from(detection.descriptor);
                    }
                } else {
                    // CEGAH BYPASS: Jangan pernah set isMatch = true jika descriptor kosong / tidak valid
                    isMatch = false;
                    confidence = 0;
                    isFaceVerified = false;
                    stableAttendanceFaceCount = 0;
                    resetLivenessState();
                    if (camStatus) {
                        camStatus.className = "absolute bottom-3 left-3 right-3 bg-amber-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-amber-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                        camStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Biometrik wajah belum terdaftar. Presensi dinonaktifkan!`;
                    }
                    setBtnLanjutState(false);
                    return;
                }

                if (isMatch) {
                    // 1. VALIDASI LIVENESS (ANTI-SPOOFING VIA EAR BLINK DETECTION)
                    if (!isLivenessPassed) {
                        if (livenessStartTime === null) {
                            livenessStartTime = Date.now();
                            updateLivenessBadge('active', 'Uji Keaktifan: Berkedip');
                        }

                        const elapsedMatch = Date.now() - livenessStartTime;

                        // Perhitungan Eye Aspect Ratio (EAR) dari Facial Landmarks
                        const landmarks = detection.landmarks;
                        const leftEye = landmarks.getLeftEye();
                        const rightEye = landmarks.getRightEye();
                        const leftEAR = calculateEAR(leftEye);
                        const rightEAR = calculateEAR(rightEye);
                        const avgEAR = (leftEAR + rightEAR) / 2.0;

                        // Evaluasi transisi mata terbuka -> tertutup -> terbuka kembali
                        if (avgEAR < EAR_THRESHOLD_CLOSED) {
                            eyeClosedConsecutiveFrames++;
                            isEyeClosedState = true;
                        } else if (avgEAR >= EAR_THRESHOLD_OPEN) {
                            if (isEyeClosedState && eyeClosedConsecutiveFrames >= 1) {
                                blinkCount++;
                                isEyeClosedState = false;
                                eyeClosedConsecutiveFrames = 0;
                                if (blinkCount >= REQUIRED_BLINKS) {
                                    isLivenessPassed = true;
                                }
                            } else {
                                isEyeClosedState = false;
                                eyeClosedConsecutiveFrames = 0;
                            }
                        }

                        // DETEKSI SPOOFING: Jika wajah cocok selama > 8 detik tanpa pernah berkedip (foto statis / foto layar HP)
                        if (elapsedMatch > LIVENESS_TIMEOUT_MS && !isLivenessPassed) {
                            isSpoofingCooldown = true;
                            resetLivenessState();
                            isFaceVerified = false;
                            stableAttendanceFaceCount = 0;
                            updateLivenessBadge('failed', 'Liveness: Gagal (Spoofing)');

                            setBtnLanjutState(false);

                            if (camStatus) {
                                camStatus.className = "absolute bottom-3 left-3 right-3 bg-rose-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-rose-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                                camStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Verifikasi gagal. Harap gunakan wajah asli dan pastikan Anda berkedip/bergerak.`;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Verifikasi Anti-Spoofing Gagal',
                                text: 'Verifikasi gagal. Harap gunakan wajah asli dan pastikan Anda berkedip/bergerak.',
                                confirmButtonText: 'Coba Lagi',
                                confirmButtonColor: '#e11d48'
                            }).then(() => {
                                setTimeout(() => {
                                    isSpoofingCooldown = false;
                                }, 1200);
                            });

                            return;
                        }

                        // Jika belum lulus liveness, tahan auto-submit dan pandu karyawan untuk berkedip
                        if (!isLivenessPassed) {
                            setBtnLanjutState(false);

                            if (camStatus && !isAutoSubmittingAttendance) {
                                camStatus.className = "absolute bottom-3 left-3 right-3 bg-amber-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-amber-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                                camStatus.innerHTML = `<i class="fa-solid fa-eye animate-pulse text-amber-200"></i> Wajah Cocok (${confidence}%)! Silakan BERKEDIP untuk verifikasi wajah asli (${blinkCount}/${REQUIRED_BLINKS})...`;
                            }
                            return;
                        }
                    }

                    // 2. KETIKA LIVENESS LOLOS & IDENTITAS COCOK
                    isFaceVerified = true;
                    stableAttendanceFaceCount++;
                    updateLivenessBadge('passed', 'Liveness: Wajah Asli ✓');

                    // Buka kunci tombol lanjut presensi saat verifikasi wajah berhasil cocok & live
                    setBtnLanjutState(true);

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
                                camStatus.className = "absolute bottom-3 left-3 right-3 bg-amber-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-amber-500/60 shadow-2xl flex items-center justify-center gap-2 animate-pulse";
                                camStatus.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Wajah Hidup Terverifikasi! Anda ${isLate ? 'Terlambat / Pulang Cepat' : 'Di Luar Radius'}. Membuka form konfirmasi alasan...`;
                            }
                            setTimeout(() => {
                                verifikasiDanLanjut();
                            }, 700);
                        } else {
                            // Auto-Submit Presensi Langsung (Tanpa harus klik submit manual)
                            if (camStatus) {
                                camStatus.className = "absolute bottom-3 left-3 right-3 bg-emerald-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-emerald-500/60 shadow-2xl flex items-center justify-center gap-2 animate-pulse";
                                camStatus.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i> Wajah Hidup Terverifikasi (${confidence}%)! Memproses presensi otomatis...`;
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
                            camStatus.className = "absolute bottom-3 left-3 right-3 bg-emerald-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-emerald-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                            camStatus.innerHTML = `<i class="fa-solid fa-circle-check mr-1"></i> Wajah Hidup Terverifikasi (${confidence}%)! Tahan posisi...`;
                        }
                    }
                } else {
                    resetLivenessState();
                    stableAttendanceFaceCount = 0;
                    isFaceVerified = false;
                    setBtnLanjutState(false);
                    updateOvalReticle('failed');
                    if (camStatus && !isAutoSubmittingAttendance) {
                        camStatus.className = "absolute bottom-3 left-3 right-3 bg-rose-600/95 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-rose-500/60 shadow-2xl flex items-center justify-center gap-1.5";
                        camStatus.innerHTML = `<i class="fa-solid fa-circle-xmark mr-1"></i> Wajah Tidak Cocok (${confidence}%). Harap hadap lurus ke kamera.`;
                    }
                }
            } else {
                resetLivenessState();
                stableAttendanceFaceCount = 0;
                isFaceVerified = false;
                setBtnLanjutState(false);
                updateOvalReticle('idle');
                if (camStatus && !isAutoSubmittingAttendance) {
                    camStatus.className = "absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md text-white text-[11px] sm:text-xs px-3.5 py-2.5 rounded-2xl z-20 text-center font-bold border border-slate-700/80 shadow-2xl flex items-center justify-center gap-1.5";
                    camStatus.innerHTML = `<i class="fa-solid fa-arrows-to-eye text-sky-400 mr-1"></i> Posisikan wajah Anda di dalam lingkaran oval...`;
                }
            }
        }, 150);
    }

    function verifikasiDanLanjut() {
        if (!hasValidFaceDescriptor(userFaceDescriptor)) {
            Swal.fire({
                icon: 'warning',
                title: 'Biometrik Wajah Belum Terdaftar',
                text: 'Anda belum merekam biometrik wajah. Silakan lakukan pendaftaran wajah terlebih dahulu sebelum presensi!',
                confirmButtonText: 'Rekam Sekarang',
                confirmButtonColor: '#0284c7'
            }).then((result) => {
                if (result.isConfirmed) {
                    tutupModalAbsen();
                    bukaModalRekamWajah();
                }
            });
            return;
        }

        if (!isFaceVerified || !isLivenessPassed) {
            Swal.fire({
                icon: 'warning',
                title: 'Verifikasi Liveness Belum Lengkap',
                text: 'Verifikasi biometrik wajah dan uji keaktifan (liveness detection dengan berkedip) wajib berhasil sebelum melanjutkan presensi.',
                confirmButtonColor: '#0284c7'
            });
            return;
        }
        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
            faceDetectionInterval = null;
        }

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }

        const modalAbsen = document.getElementById('modalAbsensi');
        const modalKonf = document.getElementById('modalKonfirmasiAbsen');

        if (modalKonf && modalKonf.parentElement !== document.body) {
            document.body.appendChild(modalKonf);
        }

        if (modalAbsen) {
            modalAbsen.classList.remove('flex');
            modalAbsen.classList.add('hidden');
        }
        if (modalKonf) {
            modalKonf.classList.remove('hidden');
            modalKonf.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        prosesEvaluasiKonfirmasi();
    }

    function prosesEvaluasiKonfirmasi() {
        const type = document.getElementById('absen_type').value;
        const now = new Date();
        const jamSekarangStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('txtWaktuSekarang').innerText = jamSekarangStr + ' WIB';

        const txtStatusRadius = document.getElementById('txtStatusRadius');
        if (isUserInRadius) {
            txtStatusRadius.className = "font-bold text-emerald-600 dark:text-emerald-400";
            txtStatusRadius.innerText = "Di Dalam Area Kerja";
        } else {
            txtStatusRadius.className = "font-bold text-rose-600 dark:text-rose-400";
            txtStatusRadius.innerText = "Di Luar Area Kerja";
        }

        const txtStatusBiometrik = document.getElementById('txtStatusBiometrik');
        if (isFaceVerified && isLivenessPassed) {
            txtStatusBiometrik.className = "font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1";
            txtStatusBiometrik.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500"></i> Terverifikasi & Live (Wajah Asli)`;
        } else {
            txtStatusBiometrik.className = "font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1";
            txtStatusBiometrik.innerHTML = `<i class="fa-solid fa-circle-exclamation text-amber-500"></i> Belum Terverifikasi`;
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
        const modalKonf = document.getElementById('modalKonfirmasiAbsen');
        if (modalKonf) {
            modalKonf.classList.remove('flex');
            modalKonf.classList.add('hidden');
        }
        bukaModalAbsen(document.getElementById('absen_type').value);
    }

    function tutupModalAbsen() {
        stopGpsTimer();
        resetLivenessState();
        isSpoofingCooldown = false;

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

        setBtnLanjutState(false);

        hapusBuktiTerpilih();

        const modalAbsen = document.getElementById('modalAbsensi');
        if (modalAbsen) {
            modalAbsen.classList.remove('flex');
            modalAbsen.classList.add('hidden');
        }
        const modalKonf = document.getElementById('modalKonfirmasiAbsen');
        if (modalKonf) {
            modalKonf.classList.remove('flex');
            modalKonf.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');
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
        if (!hasValidFaceDescriptor(userFaceDescriptor)) {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Ditolak',
                text: 'Data biometrik wajah belum terdaftar. Silakan rekam wajah di menu profil atau dashboard terlebih dahulu sebelum presensi!',
                confirmButtonColor: '#e11d48'
            });
            return;
        }

        if (!isFaceVerified || !isLivenessPassed) {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Ditolak',
                text: 'Verifikasi biometrik wajah dan uji keaktifan (liveness detection) wajib berhasil sebelum melakukan presensi.',
                confirmButtonColor: '#e11d48'
            });
            return;
        }

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
        formData.append('accuracy', window.currentGpsAccuracy || '10');
        if (window.currentBiometricNonce) {
            formData.append('biometric_nonce', window.currentBiometricNonce);
        }
        formData.append('is_face_verified', (isFaceVerified && isLivenessPassed) ? '1' : '0');
        formData.append('is_liveness_verified', isLivenessPassed ? '1' : '0');
        if (latestCameraFaceDescriptor && latestCameraFaceDescriptor.length === 128) {
            formData.append('face_descriptor', JSON.stringify(latestCameraFaceDescriptor));
        }
        formData.append('reason', reasonValue);
        formData.append('reason_out_of_radius', reasonValue);
        formData.append('reason_checkout', reasonValue);

        const inputHiddenFace = document.getElementById('absen_is_face_verified');
        if (inputHiddenFace) inputHiddenFace.value = (isFaceVerified && isLivenessPassed) ? '1' : '0';

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
                let errorMsg = res.body.message || 'Gagal mengirim presensi.';
                if (res.body.errors) {
                    if (res.body.errors.face_descriptor && res.body.errors.face_descriptor[0]) {
                        errorMsg = res.body.errors.face_descriptor[0];
                    } else if (res.body.errors.is_face_verified && res.body.errors.is_face_verified[0]) {
                        errorMsg = res.body.errors.is_face_verified[0];
                    }
                }
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#e11d48'
                });

                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i class="fa-solid fa-paper-plane text-white"></i> <span>Kirim Absensi Sekarang</span>`;
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
            btnSubmit.innerHTML = `<i class="fa-solid fa-paper-plane text-white"></i> <span>Kirim Absensi Sekarang</span>`;
        });
    }
</script>
@endpush

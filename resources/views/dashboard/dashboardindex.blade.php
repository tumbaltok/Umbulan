@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Banner Peringatan 1: Jadwal Kerja Belum Diatur (Khusus Akun Baru) --}}
    @if(auth()->check() && is_null(auth()->user()->schedule_type))
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-start space-x-3 shadow-sm">
            <div class="p-2 bg-amber-100 text-amber-700 rounded-xl mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-amber-800 text-sm">Jadwal Kerja Belum Diatur</h4>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                    Akun Anda saat ini belum memiliki jadwal kerja aktif. Silakan lakukan pengaturan jadwal kerja terlebih dahulu agar Anda dapat melakukan presensi/absensi harian.
                </p>
                <div class="mt-2">
                    <a href="{{ url('/profile?schedule_required=1#schedule_setting') }}" class="text-xs font-bold text-amber-800 underline hover:text-amber-900 transition-colors flex items-center space-x-1">
                        <span>Klik di sini untuk mengatur jadwal kerja Anda</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Banner Peringatan 2: Email / Phone Belum Diverifikasi --}}
    @if(auth()->check() && !auth()->user()->hasVerifiedEmail())
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex items-start space-x-3 shadow-sm">
            <div class="p-2 bg-amber-100 text-amber-700 rounded-xl mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-amber-800 text-sm">Akun Anda Belum Diverifikasi</h4>
                <p class="text-xs text-amber-600 mt-0.5 leading-relaxed">
                    Anda tetap dapat melihat riwayat dan menggunakan fitur profil. Namun, fitur <strong>Pengajuan Cuti baru akan terkunci</strong> sampai Anda memverifikasi alamat email Anda.
                </p>
                <div class="mt-2">
                    <a href="{{ route('verification.notice') }}" class="text-xs font-bold text-amber-800 underline hover:text-amber-900 transition-colors">
                        Klik di sini untuk mengirim ulang atau memverifikasi email &rarr;
                    </a>
                </div>
            </div>
        </div>
    @elseif(auth()->check() && !auth()->user()->phone_verified_at)
        <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-2xl flex items-start space-x-3 shadow-sm">
            <div class="p-2 bg-indigo-100 text-indigo-700 rounded-xl mt-0.5">
                <i class="fa-solid fa-phone-slash text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-indigo-800 text-sm">Nomor Telepon Belum Diverifikasi</h4>
                <p class="text-xs text-indigo-600 mt-0.5 leading-relaxed">
                    Email Anda berhasil diverifikasi! Satu langkah lagi, silakan <strong>verifikasi nomor telepon</strong> Anda untuk dapat menggunakan fitur Pengajuan Cuti.
                </p>
                <div class="mt-2">
                    <a href="{{ url('/profile?phone_required=1#phone_number') }}" class="text-xs font-bold text-indigo-800 underline hover:text-indigo-900 transition-colors flex items-center space-x-1">
                        <span>Klik di sini untuk mengisi dan memverifikasi nomor telepon sekarang</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Statistik Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-sky-50 text-sky-600 rounded-xl">
                <i class="fa-solid fa-calendar-days text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Hak Cuti Tahunan</p>
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">{{ $kuotaTahunan }} Hari</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i class="fa-solid fa-umbrella-beach text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Cuti Telah Diambil</p>
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">{{ $totalCutiDiambil }} Hari</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <i class="fa-solid fa-hourglass-half text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Menunggu Review</p>
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">
                    {{ $totalPending }} Pengajuan
                </h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <i class="fa-solid fa-circle-check text-xl w-6 text-center"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Sisa Kuota Cuti</p>
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">{{ $sisaKuota }} Hari</h3>
            </div>
        </div>
    </div>

    {{-- Widget Absensi & Jadwal Kerja --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Status Operasional Hari Ini</span>

                    @if($user->schedule_type === 'roster')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-100">
                            Sistem Roster
                        </span>
                    @endif
                </div>

                <div class="flex items-center space-x-3 mt-1">
                    @php
                        $isWorkingNow = app(App\Services\ScheduleService::class)->isUserWorkingNow($user);
                    @endphp

                    <h3 class="text-xl font-bold text-slate-800">
                        @if(is_null($user->schedule_type))
                            <span class="text-amber-600 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Jadwal Kerja Kosong
                            </span>
                        @elseif($user->schedule_type === 'roster')
                            @php
                                // Ambil jadwal aktual yang sudah memperhitungkan batas jam 07:00 WIB dini hari
                                $activeSchedule = app(App\Services\ScheduleService::class)->getTodaySchedule($user);
                            @endphp

                            @if(isset($activeSchedule['is_day_off']) && $activeSchedule['is_day_off'])
                                <span class="text-rose-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif(isset($activeSchedule['shift_type']) && $activeSchedule['shift_type'] === 'pagi')
                                <span class="text-emerald-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Pagi <span class="{{ $isWorkingNow ? 'text-emerald-600' : 'text-rose-600' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Sedang OFF - Di Luar Jam Kerja' }})</span>
                                </span>
                            @else
                                <span class="text-indigo-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $isWorkingNow ? 'bg-indigo-600 animate-pulse' : 'bg-rose-500' }}"></span>
                                    Shift Malam <span class="{{ $isWorkingNow ? 'text-indigo-600' : 'text-rose-600' }}">({{ $isWorkingNow ? 'Sedang Bekerja' : 'Sedang OFF - Di Luar Jam Kerja' }})</span>
                                </span>
                            @endif
                        @else
                            @if(isset($todaySchedule['is_day_off']) && $todaySchedule['is_day_off'])
                                <span class="text-rose-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif($isWorkingNow)
                                <span class="text-emerald-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> Sedang Bekerja
                                </span>
                            @else
                                <span class="text-rose-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Di Luar Jam Kerja)
                                </span>
                            @endif
                        @endif
                    </h3>
                </div>

                <p class="text-xs text-slate-500 mt-1.5">
                    @if(is_null($user->schedule_type))
                        Harap lakukan atur jam kerja pada menu profil Anda.
                    @elseif(isset($todaySchedule['is_day_off']) && !$todaySchedule['is_day_off'])
                        Ketentuan Jam Kerja: <strong class="text-slate-700">{{ $todaySchedule['scheduled_in'] ?? '--:--' }} - {{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB</strong>
                    @else
                        Hari ini Anda tidak memiliki jadwal shift aktif.
                    @endif
                </p>

                {{-- TANDA DANGER TERLAMBAT ATAU INFO WAKTUNYA PULANG --}}
                @if(isset($isLateNotCheckedIn) && $isLateNotCheckedIn)
                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-700">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 animate-pulse"></i>
                        <span><strong>Peringatan:</strong> Jam kerja Anda sudah dimulai ({{ $todaySchedule['scheduled_in'] ?? '--:--' }} WIB) dan Anda belum melakukan absen masuk (Terlambat).</span>
                    </div>
                @elseif(isset($canCheckOutNow) && $canCheckOutNow)
                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span><strong>Informasi:</strong> Jam kerja telah berakhir ({{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB). Anda sudah boleh melakukan absen pulang.</span>
                    </div>
                @endif
            </div>

            @if(!is_null($user->schedule_type) && isset($todaySchedule['is_day_off']) && !$todaySchedule['is_day_off'])
                <div class="flex items-center gap-2">
                    @if(!$todayAttendance || !$todayAttendance->check_in)
                        <button type="button" onclick="bukaModalAbsen('in')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition-colors flex items-center space-x-2 shadow-sm">
                            <i class="fa-solid fa-camera"></i>
                            <span>Absen Masuk</span>
                        </button>
                    @elseif(!$todayAttendance->check_out)
                        <button type="button" onclick="bukaModalAbsen('out')" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition-colors flex items-center space-x-2 shadow-sm">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Absen Pulang</span>
                        </button>
                    @else
                        <span class="bg-slate-100 text-slate-600 text-xs font-bold py-2 px-4 rounded-xl border border-slate-200">
                            <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Absensi Hari Ini Selesai
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Waktu Absen Masuk:</span>
                <span class="text-xs font-bold text-slate-800">{{ $todayAttendance->check_in ?? '--:--' }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Waktu Absen Pulang:</span>
                <span class="text-xs font-bold text-slate-800">{{ $todayAttendance->check_out ?? '--:--' }}</span>
            </div>
        </div>
    </div>

    {{-- Widget Kalender Aktivitas Jadwal --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Kalender Jadwal Kerja & Aktivitas</h3>
                <p class="text-xs text-slate-500 mt-0.5">Visualisasi jadwal shift, libur nasional, dan histori cuti Anda.</p>
            </div>

            <div class="flex items-center space-x-2">
                @php
                    $prevMonth = $currentCarbonDate->copy()->subMonth();
                    $nextMonth = $currentCarbonDate->copy()->addMonth();
                @endphp
                <a href="{{ route('dashboard', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-600 transition-colors">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <span class="text-xs font-bold text-slate-700 min-w-[120px] text-center">
                    {{ $currentCarbonDate->isoFormat('MMMM YYYY') }}
                </span>
                <a href="{{ route('dashboard', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-600 transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl">
            <span class="text-slate-400 font-bold">Keterangan:</span>

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
        <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                <h4 id="detailTanggal" class="font-bold text-slate-800 text-sm">--</h4>
                <button type="button" onclick="tutupDetailJadwal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div>
                <span id="detailTitle" class="font-bold text-sky-600 text-xs block">--</span>
                <p id="detailDesc" class="text-xs text-slate-600 mt-1.5 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">--</p>
            </div>
            <div class="text-right pt-2">
                <button type="button" onclick="tutupDetailJadwal()" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL STEP 1: KAMERA WEBCAM & GEOLOCATION GPS --}}
    <div id="modalAbsensi" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 id="judulModalAbsen" class="font-bold text-slate-800 text-sm">Verifikasi Absensi</h3>
                <button type="button" onclick="tutupModalAbsen()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Isian Content --}}
            <div class="p-5 space-y-3 flex-1 overflow-y-auto">

                {{-- Status GPS --}}
                <div id="statusLokasiBox" class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between transition-all shrink-0">
                    <div class="flex items-center space-x-2.5">
                        <i id="iconLokasi" class="fa-solid fa-location-dot text-slate-400 text-sm"></i>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi Terkini:</span>
                            <span id="textNamaLokasi" class="text-xs font-bold text-slate-600">Mendeteksi lokasi GPS...</span>
                        </div>
                    </div>
                    <div id="reloadContainer" class="hidden text-right pl-2 border-l border-rose-200/60">
                        <span class="text-[10px] text-rose-600 block font-semibold leading-tight">Memuat Ulang</span>
                        <span id="reloadCountdown" class="text-xs font-mono font-bold text-rose-700 animate-pulse">5s</span>
                    </div>
                </div>

                {{-- Container Video Kamera --}}
                <div class="relative bg-black rounded-xl overflow-hidden w-full aspect-[3/4] sm:aspect-video flex items-center justify-center border border-slate-200 shadow-inner shrink-0">
                    <video id="webcamVideo"
                        autoplay
                        playsinline
                        style="transform: scaleX(-1) !important; -webkit-transform: scaleX(-1) !important;"
                        class="w-full h-full object-cover"></video>

                    <canvas id="webcamCanvas" class="hidden"></canvas>
                    <div id="cameraStatus" class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded-md backdrop-blur-sm z-10">
                        Mempersiapkan kamera...
                    </div>
                </div>
            </div>

            {{-- Footer Tombol --}}
            <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="tutupModalAbsen()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="button" onclick="tangkapFotoDanLanjut()" id="btnTangkapFoto" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-camera"></i>
                    <span>Ambil Foto</span>
                </button>
            </div>

        </div>
    </div>

    {{-- MODAL STEP 2: PRATINJAU FOTO & KONFIRMASI DETAIL ABSENSI --}}
    <div id="modalKonfirmasiAbsen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">

            {{-- Header Modal --}}
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="font-bold text-slate-800 text-sm">Konfirmasi Detail Absensi</h3>
                <button type="button" onclick="kembaliKeKamera()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Form & Isian Modal --}}
            <form id="formAbsensi" onsubmit="submitAbsensi(event)" class="flex flex-col flex-1 overflow-y-auto">
                @csrf
                <input type="hidden" id="absen_type" name="type" value="in">
                <input type="hidden" id="absen_lat" name="latitude">
                <input type="hidden" id="absen_long" name="longitude">
                <input type="hidden" id="absen_face_image" name="face_image">

                <div class="p-5 space-y-3 flex-1 overflow-y-auto">
                    {{-- Container Foto Hasil Tangkapan --}}
                    <div class="relative w-full h-44 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shadow-sm shrink-0">
                        <img id="imgPratinjauFoto" src="" class="w-full h-full object-cover" alt="Foto Absensi">
                        <button type="button" onclick="kembaliKeKamera()" class="absolute top-2 right-2 px-2.5 py-1 bg-black/60 hover:bg-black/80 text-white text-[10px] font-semibold rounded-lg backdrop-blur-sm transition-all flex items-center space-x-1">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Foto Ulang</span>
                        </button>
                    </div>

                    {{-- Informasi Detail Rincian Absen --}}
                    <div class="space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-1.5">
                            <span class="text-slate-500 font-medium">Status Lokasi:</span>
                            <span id="txtStatusRadius" class="font-bold text-emerald-600">Di Dalam Area kerja</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-1.5">
                            <span class="text-slate-500 font-medium">Lokasi GPS:</span>
                            <span id="txtNamaLokasiConfirm" class="font-semibold text-slate-800 truncate max-w-[180px]">--</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-1.5">
                            <span class="text-slate-500 font-medium">Waktu Absen:</span>
                            <span id="txtWaktuSekarang" class="font-bold text-slate-800">--:-- WIB</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Ketentuan Jam Kerja:</span>
                            <span id="txtJamJadwal" class="font-bold text-slate-700">
                                {{ $todaySchedule['scheduled_in'] ?? '--:--' }} - {{ $todaySchedule['scheduled_out'] ?? '--:--' }} WIB
                            </span>
                        </div>
                    </div>

                    {{-- Status Peringatan Terlambat / Di Luar Radius --}}
                    <div id="boxWarningStatus" class="hidden p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <span id="txtWarningMessage">Harap lengkapi alasan di bawah ini sebelum mengirim absensi.</span>
                    </div>

                    {{-- Input Alasan Khusus --}}
                    <div id="wrapperAlasan" class="space-y-1">
                        <label id="labelAlasan" class="text-xs font-bold text-rose-700 block">Alasan Khusus / Keterangan:</label>
                        <textarea id="inputAlasan" name="reason" rows="2" class="w-full p-2 text-xs border border-rose-200 bg-rose-50/30 rounded-xl focus:ring-2 focus:ring-rose-400 outline-none resize-none" placeholder="Tuliskan alasan lengkap Anda di sini..."></textarea>
                        <span id="errorAlasanMsg" class="text-[11px] text-rose-600 hidden font-semibold">* Alasan wajib diisi!</span>
                    </div>
                </div>

                {{-- Footer Tombol Aksi --}}
                <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                    <button type="button" onclick="kembaliKeKamera()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" id="btnSubmitAbsen" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                        Kirim Absensi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat Cuti Anda --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat Cuti Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan izin cuti Anda pada periode tahun berjalan.</p>
            </div>
            <a href="{{ url('/cuti/create') }}" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan Cuti</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold text-xs border-b border-slate-100 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Jenis Cuti</th>
                        <th class="px-6 py-3.5">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-3.5">Durasi</th>
                        <th class="px-6 py-3.5">Keterangan / Alasan</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($riwayatCuti as $cuti)
                        <tr class="btn-detail-cuti hover:bg-slate-50/80 transition-colors cursor-pointer" data-id="{{ $cuti->id }}">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $cuti->name_cuti }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">{{ $cuti->total_hari }} Hari</td>
                            <td class="px-6 py-4 text-slate-500 text-xs max-w-xs truncate"
                                title="{{ $cuti->alasan_cuti ?? ($cuti->nama_sub_cuti ?? 'Tanpa Keterangan') }}">
                                @if(!empty($cuti->alasan_cuti))
                                    {{ $cuti->alasan_cuti }}
                                @else
                                    <span class="text-slate-400 italic font-medium">
                                        {{ isset($cuti->nama_sub_cuti) ? $cuti->nama_sub_cuti : 'Tanpa Keterangan' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4" onclick="event.stopPropagation();">
                                @if(trim(strtolower($cuti->status_akhir ?? '')) === 'approved')
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>

                                        <button type="button"
                                                data-url="{{ route('cuti.cetak', $cuti->id) }}"
                                                onclick="bukaPratinjauCetak(this.dataset.url)"
                                                class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-sm cursor-pointer">
                                            <i class="fa-solid fa-print text-[10px]"></i>
                                            <span>Cetak</span>
                                        </button>
                                    </div>
                                @elseif(trim(strtolower($cuti->status_akhir ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($cuti->status_tahap_2 ?? '')) === 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                        @if($cuti->catatan_penolakan)
                                            <div class="text-[11px] bg-rose-50/50 border border-rose-100 p-2 rounded-lg max-w-[200px] text-slate-600 leading-relaxed">
                                                <span class="font-bold text-rose-700 block mb-0.5">Alasan Penolakan:</span>
                                                "{{ $cuti->catatan_penolakan }}"
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block text-slate-200"></i>
                                Anda belum pernah mengajukan permohonan cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat MPR Anda --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-6">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat MPR Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan Material Purchase Request (MPR) Anda.</p>
            </div>
            <a href="{{ url('/mpr/create') }}" class="bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan MPR</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold text-xs border-b border-slate-100 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Nomor & Tanggal</th>
                        <th class="px-6 py-3.5">Keperluan / Urgensi</th>
                        <th class="px-6 py-3.5">Daftar Material</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($riwayatMpr as $mpr)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 block text-xs">{{ $mpr->nomor_mpr }}</span>
                                <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700 max-w-xs truncate" title="{{ $mpr->keperluan_urgensi }}">
                                {{ $mpr->keperluan_urgensi }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach($mpr->items->take(2) as $item)
                                        <li><span class="font-semibold text-slate-700">{{ $item->nama_barang }}</span> ({{ $item->jumlah }} {{ $item->satuan }})</li>
                                    @endforeach
                                    @if($mpr->items->count() > 2)
                                        <li class="text-slate-400 italic">+{{ $mpr->items->count() - 2 }} item lainnya</li>
                                    @endif
                                </ul>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    @if(trim(strtolower($mpr->status_akhir ?? '')) === 'approved')
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>
                                    @elseif(trim(strtolower($mpr->status_akhir ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($mpr->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($mpr->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/50 border border-rose-100 p-2 rounded-lg max-w-[200px] text-slate-600 leading-relaxed">
                                                    <span class="font-bold text-rose-700 block mb-0.5">Alasan Penolakan:</span>
                                                    "{{ $mpr->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(trim(strtolower($mpr->status_akhir ?? '')) === 'approved')
                                    <button type="button"
                                            data-url="{{ route('mpr.cetak', $mpr->id) }}"
                                            onclick="bukaPratinjauCetak(this.dataset.url)"
                                            class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold inline-flex items-center space-x-1 transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-print text-[10px]"></i>
                                        <span>Cetak PDF</span>
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-boxes-packing text-3xl mb-2 block text-slate-200"></i>
                                Anda belum pernah mengajukan permohonan MPR.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Riwayat CAR Anda --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-6">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat CAR Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan Cash Advance Request (CAR) Anda.</p>
            </div>
            <a href="{{ url('/car/create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Ajukan CAR</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold text-xs border-b border-slate-100 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Tanggal Pengajuan</th>
                        <th class="px-6 py-3.5">Alasan Pembelian</th>
                        <th class="px-6 py-3.5">Rekening Penerima</th>
                        <th class="px-6 py-3.5">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($riwayatCar as $car)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $car->created_at ? $car->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate" title="{{ $car->alasan_pembelian }}">
                                {{ $car->alasan_pembelian }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                {{ $car->receiving_account ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-start gap-1.5">
                                    @if(trim(strtolower($car->status_akhir ?? '')) === 'approved')
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Disetujui</span>
                                            </span>

                                            <button type="button"
                                                    data-url="{{ route('car.print', $car->id) }}"
                                                    onclick="bukaPratinjauCetak(this.dataset.url)"
                                                    class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-sm cursor-pointer">
                                                <i class="fa-solid fa-print text-[10px]"></i>
                                                <span>Cetak</span>
                                            </button>
                                        </div>
                                    @elseif(trim(strtolower($car->status_akhir ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_1 ?? '')) === 'rejected' || trim(strtolower($car->status_tahap_2 ?? '')) === 'rejected')
                                        <div class="space-y-1.5">
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                            @if($car->catatan_penolakan)
                                                <div class="text-[11px] bg-rose-50/50 border border-rose-100 p-2 rounded-lg max-w-[200px] text-slate-600 leading-relaxed">
                                                    <span class="font-bold text-rose-700 block mb-0.5">Alasan Penolakan:</span>
                                                    "{{ $car->catatan_penolakan }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Menunggu Review</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-200"></i>
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
    <div class="bg-white rounded-2xl max-w-2xl w-full h-[80vh] flex flex-col shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h3 id="judulModalLampiran" class="font-bold text-slate-800 text-sm">Pratinjau Dokumen</h3>
            <button onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div id="containerKontenLampiran" class="flex-1 bg-slate-50 p-4 flex items-center justify-center overflow-hidden"></div>
    </div>
</div>

{{-- Modal Detail Cuti --}}
<div id="detailCutiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="cutiModalBackdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
    <div class="bg-white rounded-2xl max-w-md w-full shadow-xl overflow-hidden z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Detail Pengajuan Cuti</h3>
            <button id="closeCutiModalBtn" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div id="cutiModalLoading" class="hidden text-center py-6 text-xs font-semibold text-slate-400 animate-pulse">Memuat data...</div>
            <div id="cutiModalContent" class="space-y-3">
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Jenis Cuti</span><p id="txt_jenis_cuti" class="text-sm font-semibold text-slate-800 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Sub Kategori Cuti</span><p id="txt_sub_cuti" class="text-sm font-medium text-slate-600 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Rentang Tanggal</span><p id="txt_rentang_tanggal" class="text-sm font-medium text-slate-600 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Durasi</span><p id="txt_total_hari" class="text-sm font-semibold text-slate-800 mt-0.5"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Alasan</span><p id="txt_alasan_cuti" class="text-xs text-slate-600 mt-0.5 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100"></p></div>
                <div><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block mb-1">Status</span><div id="wrapper_status"></div></div>
                <div class="pt-2"><span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block mb-1">Berkas Pendukung</span><div id="dokumen_render_area"></div></div>
            </div>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end"><button id="closeCutiModalBtn2" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition-colors">Tutup</button></div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Load Pustaka SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    // 4. WEBCAM & GPS ABSENSI (ALUR 2 LANGKAH)
    let mediaStream = null;
    let countdownInterval = null;
    let secondsLeft = 5;
    let isUserInRadius = false;
    let isLateOrEarly = false;

    const daftarStasiun = JSON.parse('{!! json_encode($daftarStasiun ?? []) !!}');
    const todaySchedule = JSON.parse('{!! json_encode($todaySchedule ?? []) !!}');

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

                                if (distance <= radiusLimit) {
                                    matchedStation = station;
                                    break;
                                }
                            }
                        }
                    }

                    if (statusBox && textLokasi && iconLokasi) {
                        if (matchedStation) {
                            isUserInRadius = true;
                            statusBox.className = "p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between transition-all";
                            iconLokasi.className = "fa-solid fa-circle-check text-emerald-600 text-sm";
                            textLokasi.className = "text-xs font-bold text-emerald-700";
                            textLokasi.innerText = "Lokasi: " + matchedStation.name;
                            document.getElementById('txtNamaLokasiConfirm').innerText = matchedStation.name;

                            if (reloadContainer) reloadContainer.classList.add('hidden');
                            stopGpsTimer();
                        } else {
                            isUserInRadius = false;
                            statusBox.className = "p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center justify-between transition-all";
                            iconLokasi.className = "fa-solid fa-triangle-exclamation text-rose-600 text-sm";
                            textLokasi.className = "text-xs font-bold text-rose-700";
                            textLokasi.innerText = "Lokasi berada di luar area Kerja";
                            document.getElementById('txtNamaLokasiConfirm').innerText = "Di Luar Radius Stasiun";

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

    function bukaModalAbsen(type) {
        document.getElementById('absen_type').value = type;
        document.getElementById('judulModalAbsen').innerText = type === 'in' ? 'Verifikasi Absen Masuk' : 'Verifikasi Absen Pulang';

        const statusBox = document.getElementById('statusLokasiBox');
        const textLokasi = document.getElementById('textNamaLokasi');
        const iconLokasi = document.getElementById('iconLokasi');
        const reloadContainer = document.getElementById('reloadContainer');

        if (statusBox && textLokasi && iconLokasi) {
            statusBox.className = "p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between transition-all";
            iconLokasi.className = "fa-solid fa-spinner fa-spin text-slate-400 text-sm";
            textLokasi.innerText = "Mendeteksi posisi GPS Anda...";
            if (reloadContainer) reloadContainer.classList.add('hidden');
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

        navigator.mediaDevices.getUserMedia(constraints)
            .then((stream) => {
                mediaStream = stream;
                const video = document.getElementById('webcamVideo');
                if (video) video.srcObject = stream;

                const camStatus = document.getElementById('cameraStatus');
                if (camStatus) camStatus.innerText = 'Kamera Aktif';
            })
            .catch((err) => {
                console.error("Gagal Akses Kamera:", err);
                const camStatus = document.getElementById('cameraStatus');
                if (camStatus) camStatus.innerText = 'Kamera tidak dapat diakses';
            });
    }

    function tangkapFotoDanLanjut() {
        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('webcamCanvas');
        if (!video || !canvas) return;

        const context = canvas.getContext('2d');

        const maxWidth = 640;
        const scale = Math.min(1, maxWidth / (video.videoWidth || 640));
        canvas.width = (video.videoWidth || 640) * scale;
        canvas.height = (video.videoHeight || 480) * scale;

        context.save();
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        context.restore();

        const base64Photo = canvas.toDataURL('image/jpeg', 0.6);

        document.getElementById('absen_face_image').value = base64Photo;
        document.getElementById('imgPratinjauFoto').src = base64Photo;

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

        isLateOrEarly = false;
        let warningText = "";

        if (todaySchedule && !todaySchedule.is_day_off) {
            const currentMinutes = now.getHours() * 60 + now.getMinutes();

            if (type === 'in' && todaySchedule.scheduled_in) {
                const [hIn, mIn] = todaySchedule.scheduled_in.split(':');
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);

                if (currentMinutes > schedInMinutes) {
                    isLateOrEarly = true;
                    warningText += "Anda melakukan absen masuk MELEBIHI jam kerja yang ditentukan (Terlambat). ";
                }
            } else if (type === 'out' && todaySchedule.scheduled_out) {
                const [hOut, mOut] = todaySchedule.scheduled_out.split(':');
                const [hIn, mIn] = (todaySchedule.scheduled_in || "00:00").split(':');

                const schedOutMinutes = parseInt(hOut) * 60 + parseInt(mOut);
                const schedInMinutes = parseInt(hIn) * 60 + parseInt(mIn);

                // Cek apakah Shift Malam / Lintas Hari (Contoh: Masuk 19:00, Pulang 07:00)
                const isCrossDayShift = schedOutMinutes < schedInMinutes;

                if (isCrossDayShift) {
                    // Untuk Shift Malam, jika absen dilakukan pada malam hari (setelah jam masuk)
                    // atau di pagi hari SEBELUM jam 07:00, maka terdeteksi Pulang Cepat.
                    if (currentMinutes >= schedInMinutes || currentMinutes < schedOutMinutes) {
                        isLateOrEarly = true;
                        warningText += "Anda melakukan absen pulang SEBELUM jam pulang selesai (Pulang Cepat). ";
                    }
                } else {
                    // Shift Normal (misal 08:00 - 17:00)
                    if (currentMinutes < schedOutMinutes) {
                        isLateOrEarly = true;
                        warningText += "Anda melakukan absen pulang SEBELUM jam pulang selesai (Pulang Cepat). ";
                    }
                }
            }
        }

        if (!isUserInRadius) {
            warningText += "Posisi GPS Anda berada di luar area kerja. ";
        }

        const boxWarning = document.getElementById('boxWarningStatus');
        const txtWarningMessage = document.getElementById('txtWarningMessage');
        const wrapperAlasan = document.getElementById('wrapperAlasan');
        const inputAlasan = document.getElementById('inputAlasan');

        if (!isUserInRadius || isLateOrEarly) {
            boxWarning.classList.remove('hidden');
            txtWarningMessage.innerText = warningText + "Harap isi alasan wajib di bawah ini.";
            wrapperAlasan.classList.remove('hidden');
            inputAlasan.required = true;
        } else {
            boxWarning.classList.add('hidden');
            wrapperAlasan.classList.remove('hidden');
            inputAlasan.required = false;
        }
    }

    function kembaliKeKamera() {
        document.getElementById('modalKonfirmasiAbsen').classList.add('hidden');
        bukaModalAbsen(document.getElementById('absen_type').value);
    }

    function tutupModalAbsen() {
        stopGpsTimer();

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }

        const video = document.getElementById('webcamVideo');
        if (video) {
            video.srcObject = null;
        }

        const inputAlasan = document.getElementById('inputAlasan');
        if (inputAlasan) inputAlasan.value = '';

        document.getElementById('modalAbsensi').classList.remove('flex');
        document.getElementById('modalAbsensi').classList.add('hidden');
        document.getElementById('modalKonfirmasiAbsen').classList.remove('flex');
        document.getElementById('modalKonfirmasiAbsen').classList.add('hidden');
    }

    function submitAbsensi(e) {
        e.preventDefault();

        const inputAlasan = document.getElementById('inputAlasan');
        const errorAlasanMsg = document.getElementById('errorAlasanMsg');

        if (inputAlasan.required && inputAlasan.value.trim() === '') {
            errorAlasanMsg.classList.remove('hidden');
            inputAlasan.focus();
            return;
        }
        errorAlasanMsg.classList.add('hidden');

        const btnSubmit = document.getElementById('btnSubmitAbsen');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengirim...`;

        const type = document.getElementById('absen_type').value;
        const url = type === 'in' ? '/attendance/check-in' : '/attendance/check-out';

        const payload = {
            _token: '{{ csrf_token() }}',
            latitude: document.getElementById('absen_lat').value,
            longitude: document.getElementById('absen_long').value,
            face_image: document.getElementById('absen_face_image').value,
            reason_out_of_radius: inputAlasan.value,
            reason_checkout: inputAlasan.value,
        };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            return { status: res.status, body: data };
        })
        .then(res => {
            if (res.status === 200) {
                tutupModalAbsen();

                // Pop-Up Sukses Menggunakan SweetAlert2
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.body.message || 'Absensi berhasil dikirim!',
                    icon: 'success',
                    confirmButtonText: 'Selesai',
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });

            } else {
                // Pop-Up Gagal Menggunakan SweetAlert2
                Swal.fire({
                    title: 'Gagal!',
                    text: res.body.message || 'Gagal mengirim absensi.',
                    icon: 'error',
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#e11d48'
                });

                btnSubmit.disabled = false;
                btnSubmit.innerText = 'Kirim Absensi Sekarang';
            }
        })
        .catch(err => {
            console.error("Detail Error:", err);

            Swal.fire({
                title: 'Kesalahan Sistem!',
                text: 'Gagal mengirim data. Silakan cek koneksi atau hubungi admin.',
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

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
                    <a href="{{ url('/profile') }}" class="text-xs font-bold text-amber-800 underline hover:text-amber-900 transition-colors">
                        Klik di sini untuk mengatur jadwal kerja Anda &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Banner Peringatan 2: Email Belum Diverifikasi --}}
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
                    <a href="{{ url('/profile') }}" class="text-xs font-bold text-indigo-800 underline hover:text-indigo-900 transition-colors">
                        Klik di sini untuk memverifikasi nomor telepon &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Widget Absensi & Jadwal Kerja --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Status Operasional Hari Ini</span>
                    
                    {{-- Badge hanya muncul jika tipe jadwal adalah ROSTER --}}
                    @if($user->schedule_type === 'roster')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-100">
                            Sistem Roster
                        </span>
                    @endif
                </div>

                {{-- Status Real-Time Berdasarkan Jam Saat Ini --}}
                <div class="flex items-center space-x-3 mt-1">
                    @php
                        $isWorkingNow = app(App\Services\ScheduleService::class)->isUserWorkingNow($user);
                    @endphp

                    <h3 class="text-xl font-bold text-slate-800">
                        @if(is_null($user->schedule_type))
                            {{-- ================= JADWAL BELUM DIATUR ================= --}}
                            <span class="text-amber-600 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Jadwal Kerja Kosong
                            </span>
                        @elseif($user->schedule_type === 'roster')
                            {{-- ================= TAMPILAN KHUSUS JADWAL ROSTER ================= --}}
                            @if(isset($todaySchedule['is_day_off']) && $todaySchedule['is_day_off'])
                                <span class="text-rose-600 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Sedang OFF (Libur)
                                </span>
                            @elseif(isset($todaySchedule['shift_name']) && str_contains(strtolower($todaySchedule['shift_name']), 'pagi'))
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
                            {{-- ================= TAMPILAN KHUSUS JADWAL NORMAL ================= --}}
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
            </div>

            {{-- Tombol Tindakan Absensi --}}
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

        {{-- Info Waktu Masuk & Pulang Terdaftar --}}
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

    {{-- Widget Kalender Aktivitas Jadwal (GitHub Activity Style) --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Kalender Jadwal Kerja & Aktivitas</h3>
                <p class="text-xs text-slate-500 mt-0.5">Visualisasi jadwal shift, libur nasional, dan histori cuti Anda.</p>
            </div>

            {{-- Navigasi Bulan / Tahun --}}
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

        {{-- Legenda Warna --}}
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

        {{-- Grid Kotak-Kotak Activity GitHub --}}
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

    {{-- Modal Popup Verifikasi Wajah & Geolocation GPS --}}
    <div id="modalAbsensi" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 id="judulModalAbsen" class="font-bold text-slate-800 text-sm">Verifikasi Absensi</h3>
                <button type="button" onclick="tutupModalAbsen()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form id="formAbsensi" onsubmit="submitAbsensi(event)" class="p-5 space-y-4">
                @csrf
                <input type="hidden" id="absen_type" name="type" value="in">
                <input type="hidden" id="absen_lat" name="latitude">
                <input type="hidden" id="absen_long" name="longitude">
                <input type="hidden" id="absen_face_image" name="face_image">

                {{-- TAMPILAN KAMERA LANDSCAPE --}}
                <div class="relative bg-black rounded-xl overflow-hidden aspect-video flex items-center justify-center border border-slate-200">
                    {{-- Style scaleX(1) mematikan efek mirror secara paksa --}}
                    <video id="webcamVideo" autoplay playsinline style="transform: scaleX(1) !important; -webkit-transform: scaleX(1) !important;" class="w-full h-full object-cover"></video>
                    <canvas id="webcamCanvas" class="hidden"></canvas>
                    <div id="cameraStatus" class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded-md backdrop-blur-sm">
                        Mempersiapkan kamera...
                    </div>
                </div>

                <div id="wrapperAlasan" class="hidden space-y-1">
                    <label id="labelAlasan" class="text-xs font-bold text-rose-700 block">Alasan Khusus:</label>
                    <textarea id="inputAlasan" name="reason" rows="2" class="w-full p-2.5 text-xs border border-rose-200 bg-rose-50/30 rounded-xl focus:ring-2 focus:ring-rose-400 outline-none" placeholder="Tuliskan alasan lengkap Anda di sini..."></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="tutupModalAbsen()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" id="btnSubmitAbsen" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-colors">Kirim Absensi</button>
                </div>
            </form>
        </div>
    </div>

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
                <h3 class="text-xl font-bold text-slate-800 mt-0.5">{{ $totalPending }} Pengajuan</h3>
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

    {{-- Tabel Riwayat Cuti Anda --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat Cuti Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan izin cuti Anda pada periode tahun berjalan.</p>
            </div>
            <a href="{{ url('/cuti/ajukan') }}" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
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
                                @if(trim(strtolower($cuti->status_supervisor)) === 'approved' && trim(strtolower($cuti->status_manager)) === 'approved' && trim(strtolower($cuti->status_akhir)) === 'approved')
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
                                @elseif(trim(strtolower($cuti->status_supervisor)) === 'rejected' || trim(strtolower($cuti->status_manager)) === 'rejected' || trim(strtolower($cuti->status_akhir)) === 'rejected')
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

    {{-- Tabel Riwayat CAR Anda --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat CAR Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan Cash Advance Request (CAR) Anda.</p>
            </div>
            <a href="{{ url('/car/ajukan') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
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
                                    @if(trim(strtolower($car->status_manager)) === 'approved' && trim(strtolower($car->status_supervisor)) === 'approved' && trim(strtolower($car->status_akhir)) === 'approved')
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
                                    @elseif(trim(strtolower($car->status_supervisor)) === 'rejected' || trim(strtolower($car->status_manager)) === 'rejected' || trim(strtolower($car->status_akhir)) === 'rejected')
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
                        alert("Gagal memuat data detail pengajuan cuti.");
                        closeModal();
                    });
            });
        });
    });

    // 4. WEBCAM & GPS ABSENSI (UPDATED LANDSCAPE & NON-MIRROR)
    let mediaStream = null;

    function bukaModalAbsen(type) {
        document.getElementById('absen_type').value = type;
        document.getElementById('judulModalAbsen').innerText = type === 'in' ? 'Verifikasi Absen Masuk' : 'Verifikasi Absen Pulang';
        
        const modal = document.getElementById('modalAbsensi');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Minta Lokasi GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('absen_lat').value = pos.coords.latitude;
                    document.getElementById('absen_long').value = pos.coords.longitude;
                },
                (err) => {
                    alert('Gagal mendapatkan lokasi GPS. Harap izinkan akses lokasi pada browser Anda.');
                },
                { enableHighAccuracy: true }
            );
        }

        // Minta Video Kamera dengan format Landscape
        const constraints = {
            audio: false,
            video: {
                facingMode: "user",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then((stream) => {
                mediaStream = stream;
                const video = document.getElementById('webcamVideo');
                video.srcObject = stream;
                document.getElementById('cameraStatus').innerText = 'Kamera Aktif';
            })
            .catch((err) => {
                console.error("Gagal Akses Kamera:", err);
                document.getElementById('cameraStatus').innerText = 'Kamera tidak dapat diakses';
            });
    }

    function tutupModalAbsen() {
        const modal = document.getElementById('modalAbsensi');
        modal.classList.remove('flex');
        modal.classList.add('hidden');

        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
    }

    function submitAbsensi(e) {
        e.preventDefault();

        const video = document.getElementById('webcamVideo');
        const canvas = document.getElementById('webcamCanvas');
        const context = canvas.getContext('2d');

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 360;
        
        // Ambil gambar secara normal tanpa mirror
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const base64Photo = canvas.toDataURL('image/png');
        document.getElementById('absen_face_image').value = base64Photo;

        const type = document.getElementById('absen_type').value;
        const url = type === 'in' ? '{{ route("attendance.checkin") }}' : '{{ route("attendance.checkout") }}';
        
        const payload = {
            _token: '{{ csrf_token() }}',
            latitude: document.getElementById('absen_lat').value,
            longitude: document.getElementById('absen_long').value,
            face_image: base64Photo,
            reason_out_of_radius: document.getElementById('inputAlasan').value,
            reason_checkout: document.getElementById('inputAlasan').value,
        };

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                alert(res.body.message);
                window.location.reload();
            } else if (res.status === 422) {
                document.getElementById('wrapperAlasan').classList.remove('hidden');
                document.getElementById('labelAlasan').innerText = res.body.message;
                alert(res.body.message);
            } else {
                alert(res.body.message || 'Terjadi kesalahan sistem.');
            }
        })
        .catch(err => alert('Gagal mengirim absensi. Periksa koneksi internet Anda.'));
    }
</script>
@endpush
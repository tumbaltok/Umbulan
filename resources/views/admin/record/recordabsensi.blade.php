@extends('layouts.app')

@section('title', 'Record Absensi Karyawan')

@section('content')
<div class="space-y-6">

    <!-- LEAFLET MAP STYLING -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        html.dark .leaflet-tile-pane {
            filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
        }
        .leaflet-container {
            font-family: inherit;
            border-radius: 0.75rem;
        }
    </style>

    <!-- HEADER PAGE -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-fingerprint"></i>
                <span>Human Resource Management System</span>
            </div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-user text-sky-600 dark:text-sky-400"></i>
                Record & Audit Absensi Karyawan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pemantauan presensi multi-stasiun transmisi, validasi geofencing GPS Haversine, biometrik wajah, dan rekaman jam kerja.
            </p>
        </div>

        <!-- PERIODE STATUS BADGE & QUICK INFO -->
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Rentang Periode Aktif</span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    @if($filters['start_date'] === $filters['end_date'])
                        {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('l, d F Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}
                    @endif
                </span>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-base border border-sky-100 dark:border-sky-800 shrink-0">
                <i class="fa-regular fa-calendar-check"></i>
            </span>
        </div>
    </div>

    <!-- FILTER BAR PANEL COMPREHENSIVE -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm transition-colors">
        <form method="GET" action="{{ route('admin.absensi.index') }}" id="filterForm" class="space-y-4">
            
            <!-- Quick Chips Periode -->
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
                    <button type="button" onclick="openCustomDateModal()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $filters['periode'] === 'custom' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        <i class="fa-regular fa-calendar-days text-[11px]"></i>
                        <span>Custom</span>
                        @if($filters['periode'] === 'custom')
                            <span class="text-[10px] font-mono opacity-90 pl-1 border-l border-white/30">
                                {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d/m') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d/m/Y') }}
                            </span>
                            <i class="fa-solid fa-pen text-[9px] ml-0.5 opacity-80"></i>
                        @endif
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.absensi.index') }}" 
                       class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700/70 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-all cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Reset Filter
                    </a>
                </div>
            </div>

            <!-- State Hidden Inputs (Periode, Tanggal Mulai & Selesai) -->
            <input type="hidden" name="periode" id="periodeInput" value="{{ $filters['periode'] }}">
            <input type="hidden" name="start_date" id="startDateInput" value="{{ $filters['start_date'] }}">
            <input type="hidden" name="end_date" id="endDateInput" value="{{ $filters['end_date'] }}">

            <!-- Input Grid Filter (4 Kolom Bersih) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- 1. Filter Karyawan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-user mr-1"></i> Karyawan
                    </label>
                    <select name="user_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawanList as $k)
                            <option value="{{ $k->id }}" {{ $filters['user_id'] == $k->id ? 'selected' : '' }}>
                                {{ $k->name }} {{ $k->nip ? '(' . $k->nip . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Filter Stasiun Penugasan (22 Stasiun) -->
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

                <!-- 3. Filter Role / Divisi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                        <i class="fa-solid fa-briefcase mr-1"></i> Role / Divisi
                    </label>
                    <select name="role_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ $filters['role_id'] == $r->id ? 'selected' : '' }}>
                                {{ $r->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Filter Status & Tombol Terapkan -->
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">
                            <i class="fa-solid fa-tag mr-1"></i> Status Kehadiran
                        </label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="on_time" {{ $filters['status'] === 'on_time' ? 'selected' : '' }}>Tepat Waktu</option>
                            <option value="late" {{ $filters['status'] === 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="outside_radius" {{ $filters['status'] === 'outside_radius' ? 'selected' : '' }}>Luar Radius</option>
                            <option value="early_out" {{ $filters['status'] === 'early_out' ? 'selected' : '' }}>Pulang Cepat</option>
                            <option value="cuti" {{ $filters['status'] === 'cuti' ? 'selected' : '' }}>Cuti / Izin</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer h-[38px] flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-filter mr-1.5"></i> Filter
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- CARDS STATISTIK SUMMARY HRMS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Presensi Tercatat -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Presensi Tercatat</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">
                    {{ number_format($metrics['total_presensi']) }}
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Sesi Check-In Aktif</p>
            </div>
            <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center text-xl border border-sky-100 dark:border-sky-800 shrink-0">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
        </div>

        <!-- Card 2: Tingkat Ketepatan Waktu -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Tingkat Ketepatan (On-Time)</p>
                <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1">
                    {{ $metrics['on_time_rate'] }}%
                </h3>
                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">
                    {{ $metrics['total_on_time'] }} Tepat · {{ $metrics['total_late'] }} Telat
                </p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl border border-emerald-100 dark:border-emerald-800 shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <!-- Card 3: Presensi Luar Radius (Perlu Tinjauan) -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-amber-100 dark:border-amber-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Luar Radius (Perlu Tinjauan)</p>
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    {{ number_format($metrics['total_luar_radius']) }}
                </h3>
                <p class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-0.5">
                    Pelanggaran Geofencing Pos
                </p>
            </div>
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center text-xl border border-amber-100 dark:border-amber-800 shrink-0">
                <i class="fa-solid fa-location-crosshairs"></i>
            </div>
        </div>

        <!-- Card 4: Karyawan Tidak Hadir / Sedang Cuti -->
        <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-rose-100 dark:border-rose-900/50 shadow-sm flex items-center justify-between transition-colors">
            <div>
                <p class="text-[11px] font-bold text-rose-500 dark:text-rose-400 uppercase tracking-wider">Tidak Hadir / Sedang Cuti</p>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ number_format($metrics['total_tidak_hadir']) }}
                </h3>
                <p class="text-[11px] text-rose-500/80 dark:text-rose-400/80 mt-0.5">
                    @if($isSingleDay)
                        {{ count($belumAbsen) }} Belum Hadir · {{ count($sedangCuti) }} Cuti
                    @else
                        Total Izin / Cuti / Alpha
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/60 text-rose-500 dark:text-rose-400 rounded-2xl flex items-center justify-center text-xl border border-rose-100 dark:border-rose-800 shrink-0">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
    </div>

    <!-- TABS NAVIGASI (Jika Evaluasi Single Day / Hari Ini) -->
    @if($isSingleDay)
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700">
        <button type="button" onclick="switchMainTab('tab-presensi')" id="btnTabPresensi"
                class="px-4 py-2.5 text-xs font-bold border-b-2 border-sky-600 text-sky-600 dark:text-sky-400 flex items-center gap-2 transition-all">
            <i class="fa-solid fa-list-check"></i>
            <span>Daftar Presensi Tercatat</span>
            <span class="px-2 py-0.5 bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 rounded-full text-[10px]">
                {{ $attendances->total() }}
            </span>
        </button>

        <button type="button" onclick="switchMainTab('tab-tidakhadir')" id="btnTabTidakHadir"
                class="px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-2 transition-all">
            <i class="fa-solid fa-user-clock"></i>
            <span>Monitoring Ketidakhadiran (Belum Absen / Cuti)</span>
            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 rounded-full text-[10px]">
                {{ $metrics['total_tidak_hadir'] }}
            </span>
        </button>
    </div>
    @endif

    <!-- SECTION 1: TABEL UTAMA RECORD PRESENSI KARYAWAN -->
    <div id="sectionPresensi" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 inline-block"></span>
                    Log Kehadiran Relasional Database
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Menampilkan seluruh riwayat absensi terverifikasi sesuai filter aktif.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-full text-[11px] font-bold">
                    {{ $attendances->total() }} Record Ditemukan
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/70 dark:bg-slate-900/60">
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4 min-w-[200px]">Karyawan</th>
                        <th class="p-4 min-w-[150px]">Jadwal & Shift</th>
                        <th class="p-4 min-w-[180px]">Jam Presensi & Durasi</th>
                        <th class="p-4 min-w-[180px]">Lokasi & Geofencing</th>
                        <th class="p-4 min-w-[150px] text-center">Verifikasi & Bukti</th>
                        <th class="p-4 min-w-[180px]">Keterangan / Alasan</th>
                        <th class="p-4 w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                    @forelse($attendances as $absen)
                        @php
                            $user = $absen->user;
                            $station = $user?->station;
                            $roleName = $user?->roles?->pluck('role_name')->implode(' / ') ?: ($user?->role?->role_name ?? 'STAFF');

                            // Status Radius
                            $isInRadiusIn = (bool) ($absen->is_in_radius_check_in ?? true);
                            $isInRadiusOut = (bool) ($absen->is_in_radius_check_out ?? true);
                            $isOutside = !$isInRadiusIn || !$isInRadiusOut;

                            // Status Ketepatan
                            $isLate = (bool) $absen->is_late;
                            $isEarlyOut = (bool) $absen->is_early_checkout;

                            // Durasi Kerja
                            $workDuration = $absen->work_duration_formatted;

                            // URL Bukti
                            $evidenceInUrl = $absen->evidence_in ? asset('storage/' . $absen->evidence_in) : ($absen->face_photo_in ? asset('storage/' . $absen->face_photo_in) : null);
                            $evidenceOutUrl = $absen->evidence_out ? asset('storage/' . $absen->evidence_out) : ($absen->face_photo_out ? asset('storage/' . $absen->face_photo_out) : null);

                            // Alasan
                            $reasonIn = $absen->effective_reason_in;
                            $reasonOut = $absen->effective_reason_out;

                            // Shift Label & Class
                            $shiftTypeLower = strtolower($absen->shift_type ?? 'normal');
                            $shiftBadge = 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600';
                            if (str_contains($shiftTypeLower, 'pagi')) {
                                $shiftBadge = 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                            } elseif (str_contains($shiftTypeLower, 'malam')) {
                                $shiftBadge = 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800';
                            } elseif (str_contains($shiftTypeLower, 'cuti')) {
                                $shiftBadge = 'bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800';
                            }

                            // Payload Detail Modal
                            $detailPayload = [
                                'id' => $absen->id,
                                'user_name' => $user?->name ?? 'Karyawan',
                                'user_nip' => $user?->nip ?? '-',
                                'user_role' => $roleName,
                                'user_avatar' => $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                                'user_initial' => strtoupper(substr($user?->name ?? 'U', 0, 1)),
                                'date_formatted' => \Carbon\Carbon::parse($absen->date)->translatedFormat('l, d F Y'),
                                'shift_type' => $absen->shift_type ?? 'Normal',
                                'scheduled_in' => $absen->scheduled_in ? substr($absen->scheduled_in, 0, 5) : '--:--',
                                'scheduled_out' => $absen->scheduled_out ? substr($absen->scheduled_out, 0, 5) : '--:--',
                                'check_in' => $absen->check_in ? substr($absen->check_in, 0, 5) : null,
                                'check_out' => $absen->check_out ? substr($absen->check_out, 0, 5) : null,
                                'work_duration' => $workDuration,
                                'is_late' => $isLate,
                                'is_early_checkout' => $isEarlyOut,
                                'is_in_radius_in' => $isInRadiusIn,
                                'is_in_radius_out' => $isInRadiusOut,
                                'check_in_distance' => $absen->check_in_distance,
                                'check_out_distance' => $absen->check_out_distance,
                                'check_in_lat' => $absen->check_in_lat ? (float) $absen->check_in_lat : null,
                                'check_in_long' => $absen->check_in_long ? (float) $absen->check_in_long : null,
                                'check_out_lat' => $absen->check_out_lat ? (float) $absen->check_out_lat : null,
                                'check_out_long' => $absen->check_out_long ? (float) $absen->check_out_long : null,
                                'station_name' => $station?->name ?? 'Stasiun Umbulan',
                                'station_lat' => $station?->latitude ? (float) $station->latitude : null,
                                'station_long' => $station?->longitude ? (float) $station->longitude : null,
                                'station_radius' => $station?->radius_meters ? (float) $station->radius_meters : 100,
                                'is_face_verified_in' => (bool) $absen->is_face_verified_in,
                                'is_face_verified_out' => (bool) $absen->is_face_verified_out,
                                'reason_in' => $reasonIn,
                                'reason_out' => $reasonOut,
                                'evidence_in_url' => $evidenceInUrl,
                                'evidence_out_url' => $evidenceOutUrl,
                                'created_at' => $absen->created_at ? $absen->created_at->format('d/m/Y H:i:s') : '-',
                                'updated_at' => $absen->updated_at ? $absen->updated_at->format('d/m/Y H:i:s') : '-',
                            ];
                        @endphp

                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            
                            <!-- 1. NO & TANGGAL -->
                            <td class="p-4 text-center">
                                <span class="font-mono text-slate-400 font-bold text-[11px] block">
                                    {{ $attendances->firstItem() ? $attendances->firstItem() + $loop->index : $loop->iteration }}
                                </span>
                                <span class="text-[10px] text-slate-400 block whitespace-nowrap mt-0.5">
                                    {{ \Carbon\Carbon::parse($absen->date)->translatedFormat('d M') }}
                                </span>
                            </td>

                            <!-- 2. KARYAWAN -->
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    @if(!empty($user?->profile_photo))
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user?->name }}" 
                                             class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center shrink-0 shadow-xs text-xs">
                                            {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-slate-100 leading-tight">
                                            {{ $user?->name ?? 'Karyawan Dihapus' }}
                                        </p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            @if(!empty($user?->nip))
                                                <span class="text-[10px] font-mono text-slate-400">{{ $user->nip }}</span>
                                                <span class="text-slate-300 dark:text-slate-600 text-[9px]">•</span>
                                            @endif
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase">
                                                {{ $roleName }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. JADWAL & SHIFT -->
                            <td class="p-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold border {{ $shiftBadge }}">
                                        {{ $absen->shift_type ?? 'Normal' }}
                                    </span>
                                    <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400">
                                        {{ $absen->scheduled_in ? substr($absen->scheduled_in, 0, 5) : '--:--' }} - 
                                        {{ $absen->scheduled_out ? substr($absen->scheduled_out, 0, 5) : '--:--' }}
                                    </p>
                                </div>
                            </td>

                            <!-- 4. JAM PRESENSI & DURASI KERJA -->
                            <td class="p-4">
                                <div class="space-y-1.5">
                                    <!-- In / Out Badges -->
                                    <div class="flex items-center gap-1.5">
                                        <!-- Check-In -->
                                        @if(!empty($absen->check_in))
                                            <span class="px-2 py-0.5 {{ $isLate ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' }} rounded-lg font-mono font-bold text-[11px] border">
                                                <i class="fa-solid fa-arrow-right-to-bracket text-[9px] mr-1"></i>{{ substr($absen->check_in, 0, 5) }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 font-mono text-[11px]">--:--</span>
                                        @endif

                                        <span class="text-slate-300 dark:text-slate-600 font-bold">→</span>

                                        <!-- Check-Out -->
                                        @if(!empty($absen->check_out))
                                            <span class="px-2 py-0.5 {{ $isEarlyOut ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800' : 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800' }} rounded-lg font-mono font-bold text-[11px] border">
                                                <i class="fa-solid fa-arrow-right-from-bracket text-[9px] mr-1"></i>{{ substr($absen->check_out, 0, 5) }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Belum Pulang</span>
                                        @endif
                                    </div>

                                    <!-- Status Ketepatan & Durasi Jam Kerja -->
                                    <div class="flex items-center gap-2">
                                        @if($isLate)
                                            <span class="inline-flex items-center text-[10px] font-bold text-rose-600 dark:text-rose-400">
                                                <i class="fa-solid fa-clock-exclamation mr-1"></i> Terlambat
                                            </span>
                                        @elseif(!empty($absen->check_in))
                                            <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <i class="fa-solid fa-circle-check mr-1"></i> Tepat Waktu
                                            </span>
                                        @endif

                                        @if($isEarlyOut)
                                            <span class="inline-flex items-center text-[10px] font-bold text-purple-600 dark:text-purple-400">
                                                <i class="fa-solid fa-person-walking-arrow-right mr-1"></i> Pulang Cepat
                                            </span>
                                        @endif

                                        <!-- Total Jam Kerja -->
                                        @if(!empty($absen->check_in) && !empty($absen->check_out))
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-1.5 py-0.5 rounded">
                                                <i class="fa-solid fa-business-time mr-1"></i>{{ $workDuration }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 5. LOKASI & GEOFENCING -->
                            <td class="p-4">
                                <div class="space-y-1">
                                    <p class="font-bold text-slate-700 dark:text-slate-200 leading-tight">
                                        {{ $station?->name ?? 'Stasiun Umbulan' }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <!-- Badge Radius -->
                                        @if(!$isOutside)
                                            <span class="inline-flex items-center text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                                                <i class="fa-solid fa-location-dot mr-1"></i> Dalam Radius
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Luar Radius
                                            </span>
                                        @endif

                                        <!-- Jarak Haversine -->
                                        @if(!empty($absen->check_in_distance))
                                            <span class="text-[10px] font-mono text-slate-400" title="Jarak saat Check-In">
                                                {{ number_format($absen->check_in_distance, 0, ',', '.') }} m
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 6. VERIFIKASI & BUKTI WATERMARK -->
                            <td class="p-4 text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <!-- Badge Face Biometric Verified -->
                                    @if($absen->is_face_verified_in || $absen->is_face_verified_out)
                                        <span class="inline-flex items-center text-[10px] font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/60 px-2 py-0.5 rounded-full border border-sky-200 dark:border-sky-800">
                                            <i class="fa-solid fa-shield-check text-sky-500 mr-1"></i> Biometrik Valid
                                        </span>
                                    @endif

                                    <!-- Tombol Bukti Masuk / Pulang -->
                                    <div class="flex items-center gap-1">
                                        @if($evidenceInUrl)
                                            <button type="button" 
                                                    onclick="openLightboxModal('{{ $evidenceInUrl }}', 'Bukti Masuk (Watermark) - {{ addslashes($user?->name) }}')"
                                                    class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-md font-bold text-[10px] transition-all cursor-pointer flex items-center gap-1"
                                                    title="Lihat Foto Bukti Masuk">
                                                <i class="fa-solid fa-image"></i> Masuk
                                            </button>
                                        @endif

                                        @if($evidenceOutUrl)
                                            <button type="button" 
                                                    onclick="openLightboxModal('{{ $evidenceOutUrl }}', 'Bukti Pulang (Watermark) - {{ addslashes($user?->name) }}')"
                                                    class="px-2 py-0.5 bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 rounded-md font-bold text-[10px] transition-all cursor-pointer flex items-center gap-1"
                                                    title="Lihat Foto Bukti Pulang">
                                                <i class="fa-solid fa-image"></i> Pulang
                                            </button>
                                        @endif

                                        @if(!$evidenceInUrl && !$evidenceOutUrl && !$absen->is_face_verified_in && !$absen->is_face_verified_out)
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 7. KETERANGAN / ALASAN -->
                            <td class="p-4">
                                <div class="space-y-1 max-w-xs">
                                    @if(!empty($reasonIn))
                                        <div class="p-1.5 bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 rounded-lg text-[11px] text-slate-700 dark:text-slate-300 leading-snug">
                                            <span class="font-bold text-[10px] uppercase tracking-wider text-rose-700 dark:text-rose-400 flex items-center gap-1">
                                                <i class="fa-solid fa-right-to-bracket text-[9px]"></i> Alasan Masuk:
                                            </span>
                                            "{{ $reasonIn }}"
                                        </div>
                                    @endif

                                    @if(!empty($reasonOut))
                                        <div class="p-1.5 bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50 rounded-lg text-[11px] text-slate-700 dark:text-slate-300 leading-snug">
                                            <span class="font-bold text-[10px] uppercase tracking-wider text-purple-700 dark:text-purple-400 flex items-center gap-1">
                                                <i class="fa-solid fa-right-from-bracket text-[9px]"></i> Alasan Pulang:
                                            </span>
                                            "{{ $reasonOut }}"
                                        </div>
                                    @endif

                                    @if(empty($reasonIn) && empty($reasonOut))
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 8. AKSI (DETAIL MODAL) -->
                            <td class="p-4 text-center">
                                <button type="button" 
                                        onclick='openDetailModal(@json($detailPayload))'
                                        class="p-2 bg-sky-50 dark:bg-sky-950/60 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-600 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-xl font-bold transition-all cursor-pointer shadow-2xs inline-flex items-center gap-1 text-[11px]"
                                        title="Tinjau Detail Metadata & Peta GPS">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-xl">
                                        <i class="fa-solid fa-inbox"></i>
                                    </div>
                                    <p class="font-semibold text-xs">Belum ada rekaman presensi pada filter periode ini.</p>
                                    <p class="text-[11px]">Silakan ubah rentang tanggal atau reset filter untuk menampilkan data lainnya.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION BAR -->
        @if($attendances->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>

    <!-- SECTION 2: MONITORING KETIDAKHADIRAN (BELUM ABSEN / SEDANG CUTI) -->
    @if($isSingleDay)
    <div id="sectionTidakHadir" class="hidden space-y-6">

        <!-- TABEL 2A: BELUM HADIR / TANPA KETERANGAN -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-rose-50/50 dark:bg-rose-950/20 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-rose-900 dark:text-rose-300 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                        Karyawan Wajib Hadir Namun Belum Absen Hari Ini
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Karyawan terjadwal masuk kerja yang belum melakukan check-in sistem.</p>
                </div>
                <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-full text-[10px] font-bold">
                    {{ count($belumAbsen) }} Karyawan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-900/60">
                            <th class="p-4">Karyawan</th>
                            <th class="p-4">Stasiun Penugasan</th>
                            <th class="p-4">Role / Jabatan</th>
                            <th class="p-4">Jadwal Shift</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                        @forelse($belumAbsen as $item)
                            @php
                                $u = $item['user'];
                                $sched = $item['schedule'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $u->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-semibold text-slate-600 dark:text-slate-300">
                                    {{ $u->station->name ?? 'Stasiun Umbulan' }}
                                </td>
                                <td class="p-4 font-semibold text-slate-500 dark:text-slate-400 uppercase">
                                    {{ $u->role->role_name ?? 'STAFF' }}
                                </td>
                                <td class="p-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $sched['shift_name'] ?? 'Shift Normal' }}
                                    <span class="block text-[10px] text-slate-400">
                                        {{ $sched['scheduled_in'] ?? '--:--' }} - {{ $sched['scheduled_out'] ?? '--:--' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 font-bold rounded-lg border border-rose-100 dark:border-rose-900/50 text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Belum Check-In
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-emerald-600 dark:text-emerald-400 font-bold">
                                    <i class="fa-solid fa-check-circle mr-1"></i> Seluruh karyawan yang wajib hadir telah melakukan presensi!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL 2B: KARYAWAN SEDANG CUTI -->
        @if(count($sedangCuti) > 0)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-teal-50/50 dark:bg-teal-950/20 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-teal-900 dark:text-teal-300 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"></span>
                        Karyawan Sedang Menjalani Cuti Resmi Hari Ini
                    </h2>
                </div>
                <span class="px-2.5 py-1 bg-teal-100 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 rounded-full text-[10px] font-bold">
                    {{ count($sedangCuti) }} Karyawan Cuti
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-900/60">
                            <th class="p-4">Karyawan</th>
                            <th class="p-4">Stasiun Penugasan</th>
                            <th class="p-4">Role / Jabatan</th>
                            <th class="p-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                        @foreach($sedangCuti as $c)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ $c['user']->name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $c['user']->nip ?? '-' }}</p>
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-300 font-medium">
                                    {{ $c['user']->station->name ?? 'Stasiun Umbulan' }}
                                </td>
                                <td class="p-4 uppercase text-slate-500 dark:text-slate-400 font-semibold">
                                    {{ $c['user']->role->role_name ?? 'STAFF' }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 font-bold rounded-lg border border-teal-200 dark:border-teal-800 text-[10px] inline-flex items-center gap-1">
                                        <i class="fa-solid fa-umbrella-beach"></i> {{ $c['reason'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
    @endif

</div>

<!-- MODAL REVIEW DETAIL RECORD PRESENSI & PETA AUDIT GEOFENCING -->
<div id="detailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
    <div id="detailModalCard" class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl max-w-3xl w-full overflow-hidden border border-slate-100 dark:border-slate-700 transform transition-all duration-300 scale-95 opacity-0 flex flex-col max-h-[90vh]">
        
        <!-- HEADER MODAL -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/60">
            <div class="flex items-center gap-3">
                <div id="modalUserAvatarBox" class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center shrink-0 shadow-sm text-sm">
                    <span id="modalUserInitial">U</span>
                </div>
                <div>
                    <h3 id="modalUserName" class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight">Nama Karyawan</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="modalUserNip" class="text-[10px] font-mono text-slate-400">-</span>
                        <span class="text-slate-300 dark:text-slate-600 text-[9px]">•</span>
                        <span id="modalUserRole" class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase">-</span>
                    </div>
                </div>
            </div>

            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-slate-200/60 dark:bg-slate-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 hover:text-rose-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- BODY MODAL (SCROLLABLE) -->
        <div class="p-6 overflow-y-auto space-y-6 text-xs">
            
            <!-- Grid Metadata Waktu & Verifikasi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Box 1: Shift & Waktu Kehadiran -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700/60 space-y-3">
                    <h4 class="font-bold text-[11px] text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-regular fa-clock text-sky-500"></i> Informasi Waktu & Shift
                    </h4>

                    <div class="grid grid-cols-2 gap-2 text-slate-700 dark:text-slate-200">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Tanggal Presensi:</span>
                            <span id="modalDateFormatted" class="font-bold">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Tipe Shift:</span>
                            <span id="modalShiftType" class="font-bold text-sky-600 dark:text-sky-400">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Jadwal Masuk / Pulang:</span>
                            <span id="modalScheduledTime" class="font-mono font-bold">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Total Durasi Kerja:</span>
                            <span id="modalWorkDuration" class="font-bold text-emerald-600 dark:text-emerald-400">-</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Waktu Check-In:</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span id="modalCheckInTime" class="font-mono font-bold text-slate-800 dark:text-slate-100">-</span>
                                <span id="modalLateBadge" class="text-[9px] font-bold px-1.5 py-0.2 rounded">-</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Waktu Check-Out:</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span id="modalCheckOutTime" class="font-mono font-bold text-slate-800 dark:text-slate-100">-</span>
                                <span id="modalEarlyBadge" class="text-[9px] font-bold px-1.5 py-0.2 rounded">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box 2: Geofencing & Biometrik Wajah -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700/60 space-y-3">
                    <h4 class="font-bold text-[11px] text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i> Audit Lokasi & Biometrik
                    </h4>

                    <div class="space-y-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Stasiun Penugasan Resmi:</span>
                            <span id="modalStationName" class="font-bold text-slate-800 dark:text-slate-100">-</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-slate-700 dark:text-slate-200">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Jarak Check-In:</span>
                                <span id="modalCheckInDist" class="font-bold">-</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Jarak Check-Out:</span>
                                <span id="modalCheckOutDist" class="font-bold">-</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center gap-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Biometrik Wajah:</span>
                                <span id="modalFaceVerifiedBadge" class="font-bold text-[10px]">-</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Geofence Status:</span>
                                <span id="modalRadiusBadge" class="font-bold text-[10px]">-</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Box Peta Interaktif Leaflet -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-[11px] text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-map-location-dot text-sky-500"></i> Peta Koordinat GPS Absensi vs Radius Stasiun
                    </h4>
                    <span id="modalCoordsSummary" class="text-[10px] font-mono text-slate-400">Lat/Long: -</span>
                </div>
                <div id="attendanceDetailMap" class="w-full h-56 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-900 z-0"></div>
            </div>

            <!-- Box Alasan & Berkas Bukti (Jika Ada) -->
            <div id="modalEvidenceContainer" class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-700">
                <h4 class="font-bold text-[11px] text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-file-invoice text-amber-500"></i> Alasan & Berkas Bukti (Watermark)
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bukti Masuk -->
                    <div id="modalBoxEvidenceIn" class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Bukti Presensi Masuk</span>
                        <p id="modalTextReasonIn" class="italic text-slate-600 dark:text-slate-300 text-[11px] mb-2">Tidak ada alasan.</p>
                        <div id="modalThumbInWrapper" class="hidden">
                            <img id="modalThumbIn" src="" alt="Bukti Masuk" 
                                 onclick="openLightboxModal(this.src, 'Bukti Watermark Masuk')"
                                 class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:opacity-90 transition-opacity">
                        </div>
                    </div>

                    <!-- Bukti Pulang -->
                    <div id="modalBoxEvidenceOut" class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Bukti Presensi Pulang</span>
                        <p id="modalTextReasonOut" class="italic text-slate-600 dark:text-slate-300 text-[11px] mb-2">Tidak ada alasan.</p>
                        <div id="modalThumbOutWrapper" class="hidden">
                            <img id="modalThumbOut" src="" alt="Bukti Pulang" 
                                 onclick="openLightboxModal(this.src, 'Bukti Watermark Pulang')"
                                 class="w-full h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:opacity-90 transition-opacity">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Timestamps -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between text-[10px] text-slate-400">
                <span>Dibuat di Sistem: <strong id="modalCreatedAt" class="text-slate-600 dark:text-slate-300">-</strong></span>
                <span>Terakhir Diperbarui: <strong id="modalUpdatedAt" class="text-slate-600 dark:text-slate-300">-</strong></span>
            </div>

        </div>

        <!-- FOOTER MODAL -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 flex justify-end">
            <button type="button" onclick="closeDetailModal()" 
                    class="px-5 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                Tutup Review
            </button>
        </div>
    </div>
</div>

<!-- MODAL POPUP LIGHTBOX FOTO WATERMARK -->
<div id="lightboxModal" class="fixed inset-0 z-60 hidden flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-opacity duration-300">
    <div id="lightboxModalCard" class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 dark:border-slate-800 transform transition-all duration-300 scale-95 opacity-0">
        <!-- HEADER LIGHTBOX -->
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
            <h3 id="lightboxTitle" class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-file-shield text-sky-500"></i>
                <span>Berkas Bukti Watermark</span>
            </h3>
            <button type="button" onclick="closeLightboxModal()" class="w-8 h-8 rounded-full bg-slate-200/60 dark:bg-slate-800 hover:bg-rose-100 dark:hover:bg-rose-900/50 hover:text-rose-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- GAMBAR FULL SIZE -->
        <div class="p-4 flex items-center justify-center bg-slate-950/40 min-h-[300px]">
            <img id="lightboxImage" src="" alt="Bukti Presensi" class="max-h-[75vh] w-auto rounded-xl object-contain shadow-xl">
        </div>

        <!-- FOOTER -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex justify-end">
            <button type="button" onclick="closeLightboxModal()" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL POPUP PILIH RENTANG TANGGAL KUSTOM -->
<div id="customDateModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
    <div id="customDateModalCard" class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 dark:border-slate-700 transform transition-all duration-300 scale-95 opacity-0">
        
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/60">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm border border-sky-100 dark:border-sky-800">
                    <i class="fa-regular fa-calendar-days"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pilih Rentang Tanggal Kustom</h3>
                    <p class="text-[11px] text-slate-400">Tentukan periode awal dan akhir presensi.</p>
                </div>
            </div>
            <button type="button" onclick="closeCustomDateModal()" class="w-8 h-8 rounded-full bg-slate-200/60 dark:bg-slate-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 hover:text-rose-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5">
                    <i class="fa-regular fa-calendar text-sky-500 mr-1"></i> Tanggal Mulai
                </label>
                <input type="date" id="modalCustomStartDate" value="{{ $filters['start_date'] }}"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5">
                    <i class="fa-regular fa-calendar-check text-sky-500 mr-1"></i> Tanggal Selesai
                </label>
                <input type="date" id="modalCustomEndDate" value="{{ $filters['end_date'] }}"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div id="customDateError" class="hidden p-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-[11px] text-rose-600 dark:text-rose-300 font-medium">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> Tanggal mulai tidak boleh melebihi tanggal selesai.
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 flex items-center justify-end gap-2">
            <button type="button" onclick="closeCustomDateModal()" 
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer">
                Batal
            </button>
            <button type="button" onclick="applyCustomDatePreset()" 
                    class="px-5 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-check text-[11px]"></i> Terapkan Kustom
            </button>
        </div>
    </div>
</div>

<script>
    // -------------------------------------------------------------
    // PRESET FILTER PERIODE QUICK-CHIPS & POPUP CUSTOM
    // -------------------------------------------------------------
    function setPeriodePreset(type) {
        const input = document.getElementById('periodeInput');
        const start = document.getElementById('startDateInput');
        const end = document.getElementById('endDateInput');
        if (!input || !start || !end) return;

        if (type === 'custom') {
            openCustomDateModal();
            return;
        }

        input.value = type;

        const now = new Date();
        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (type === 'today') {
            const todayStr = formatDate(now);
            start.value = todayStr;
            end.value = todayStr;
        } else if (type === 'week') {
            // Awal minggu (Senin)
            const d = new Date(now);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            const monday = new Date(d.setDate(diff));
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            start.value = formatDate(monday);
            end.value = formatDate(sunday);
        } else if (type === 'month') {
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            start.value = formatDate(firstDay);
            end.value = formatDate(lastDay);
        }

        document.getElementById('filterForm')?.submit();
    }

    function openCustomDateModal() {
        const modal = document.getElementById('customDateModal');
        const card = document.getElementById('customDateModalCard');
        const err = document.getElementById('customDateError');
        if (!modal || !card) return;

        if (err) err.classList.add('hidden');

        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCustomDateModal() {
        const modal = document.getElementById('customDateModal');
        const card = document.getElementById('customDateModalCard');
        if (!modal || !card) return;

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    function applyCustomDatePreset() {
        const modalStart = document.getElementById('modalCustomStartDate');
        const modalEnd = document.getElementById('modalCustomEndDate');
        const formStart = document.getElementById('startDateInput');
        const formEnd = document.getElementById('endDateInput');
        const formPeriode = document.getElementById('periodeInput');
        const err = document.getElementById('customDateError');

        if (!modalStart || !modalEnd || !formStart || !formEnd || !formPeriode) return;

        let startVal = modalStart.value;
        let endVal = modalEnd.value;

        if (!startVal && !endVal) {
            return;
        }
        if (!startVal) startVal = endVal;
        if (!endVal) endVal = startVal;

        if (startVal > endVal) {
            if (err) err.classList.remove('hidden');
            return;
        }

        formStart.value = startVal;
        formEnd.value = endVal;
        formPeriode.value = 'custom';

        closeCustomDateModal();
        document.getElementById('filterForm')?.submit();
    }


    // -------------------------------------------------------------
    // MAIN TAB SWITCHER (Jika Single Day / Hari Ini)
    // -------------------------------------------------------------
    function switchMainTab(tabId) {
        const secPresensi = document.getElementById('sectionPresensi');
        const secTidakHadir = document.getElementById('sectionTidakHadir');
        const btnTabPresensi = document.getElementById('btnTabPresensi');
        const btnTabTidakHadir = document.getElementById('btnTabTidakHadir');

        if (!secPresensi || !secTidakHadir) return;

        if (tabId === 'tab-presensi') {
            secPresensi.classList.remove('hidden');
            secTidakHadir.classList.add('hidden');

            btnTabPresensi?.classList.add('border-sky-600', 'text-sky-600', 'dark:text-sky-400');
            btnTabPresensi?.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');

            btnTabTidakHadir?.classList.remove('border-sky-600', 'text-sky-600', 'dark:text-sky-400');
            btnTabTidakHadir?.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        } else {
            secPresensi.classList.add('hidden');
            secTidakHadir.classList.remove('hidden');

            btnTabTidakHadir?.classList.add('border-sky-600', 'text-sky-600', 'dark:text-sky-400');
            btnTabTidakHadir?.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');

            btnTabPresensi?.classList.remove('border-sky-600', 'text-sky-600', 'dark:text-sky-400');
            btnTabPresensi?.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        }
    }

    // -------------------------------------------------------------
    // DETAIL MODAL & LEAFLET MAP AUDIT GEOFENCING
    // -------------------------------------------------------------
    let detailMapInstance = null;
    let mapMarkerCheckIn = null;
    let mapMarkerCheckOut = null;
    let mapCircleStation = null;

    function openDetailModal(data) {
        const modal = document.getElementById('detailModal');
        const card = document.getElementById('detailModalCard');
        if (!modal || !card) return;

        // 1. Set Profil User
        document.getElementById('modalUserName').innerText = data.user_name;
        document.getElementById('modalUserNip').innerText = data.user_nip ? 'NIP: ' + data.user_nip : '-';
        document.getElementById('modalUserRole').innerText = data.user_role;
        document.getElementById('modalUserInitial').innerText = data.user_initial;

        // 2. Set Metadata Waktu & Shift
        document.getElementById('modalDateFormatted').innerText = data.date_formatted;
        document.getElementById('modalShiftType').innerText = data.shift_type;
        document.getElementById('modalScheduledTime').innerText = data.scheduled_in + ' - ' + data.scheduled_out;
        document.getElementById('modalWorkDuration').innerText = data.work_duration;

        // Check-In
        document.getElementById('modalCheckInTime').innerText = data.check_in ? data.check_in + ' WIB' : '--:--';
        const lateBadge = document.getElementById('modalLateBadge');
        if (data.is_late) {
            lateBadge.innerText = 'Terlambat';
            lateBadge.className = 'text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300';
        } else if (data.check_in) {
            lateBadge.innerText = 'Tepat Waktu';
            lateBadge.className = 'text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300';
        } else {
            lateBadge.innerText = '-';
            lateBadge.className = 'hidden';
        }

        // Check-Out
        document.getElementById('modalCheckOutTime').innerText = data.check_out ? data.check_out + ' WIB' : 'Belum Pulang';
        const earlyBadge = document.getElementById('modalEarlyBadge');
        if (data.is_early_checkout) {
            earlyBadge.innerText = 'Pulang Cepat';
            earlyBadge.className = 'text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300';
        } else {
            earlyBadge.innerText = '';
            earlyBadge.className = 'hidden';
        }

        // 3. Stasiun & Geofence
        document.getElementById('modalStationName').innerText = data.station_name;
        document.getElementById('modalCheckInDist').innerText = data.check_in_distance ? data.check_in_distance + ' m' : '-';
        document.getElementById('modalCheckOutDist').innerText = data.check_out_distance ? data.check_out_distance + ' m' : '-';

        const faceBadge = document.getElementById('modalFaceVerifiedBadge');
        if (data.is_face_verified_in || data.is_face_verified_out) {
            faceBadge.innerText = 'Terverifikasi';
            faceBadge.className = 'font-bold text-[10px] text-sky-600 dark:text-sky-400';
        } else {
            faceBadge.innerText = 'Standar';
            faceBadge.className = 'font-bold text-[10px] text-slate-400';
        }

        const radBadge = document.getElementById('modalRadiusBadge');
        if (data.is_in_radius_in && data.is_in_radius_out) {
            radBadge.innerText = 'Di Dalam Radius';
            radBadge.className = 'font-bold text-[10px] text-emerald-600 dark:text-emerald-400';
        } else {
            radBadge.innerText = 'Di Luar Radius';
            radBadge.className = 'font-bold text-[10px] text-amber-600 dark:text-amber-400';
        }

        // 4. Berkas Bukti & Alasan
        const reasonInEl = document.getElementById('modalTextReasonIn');
        const thumbInWrapper = document.getElementById('modalThumbInWrapper');
        const thumbIn = document.getElementById('modalThumbIn');

        if (data.reason_in) {
            reasonInEl.innerText = '"' + data.reason_in + '"';
        } else {
            reasonInEl.innerText = 'Tidak ada alasan khusus masuk.';
        }

        if (data.evidence_in_url) {
            thumbIn.src = data.evidence_in_url;
            thumbInWrapper.classList.remove('hidden');
        } else {
            thumbInWrapper.classList.add('hidden');
        }

        const reasonOutEl = document.getElementById('modalTextReasonOut');
        const thumbOutWrapper = document.getElementById('modalThumbOutWrapper');
        const thumbOut = document.getElementById('modalThumbOut');

        if (data.reason_out) {
            reasonOutEl.innerText = '"' + data.reason_out + '"';
        } else {
            reasonOutEl.innerText = 'Tidak ada alasan khusus pulang.';
        }

        if (data.evidence_out_url) {
            thumbOut.src = data.evidence_out_url;
            thumbOutWrapper.classList.remove('hidden');
        } else {
            thumbOutWrapper.classList.add('hidden');
        }

        // Audit Timestamps
        document.getElementById('modalCreatedAt').innerText = data.created_at;
        document.getElementById('modalUpdatedAt').innerText = data.updated_at;

        // 5. Render Leaflet Map
        const coordsSummary = document.getElementById('modalCoordsSummary');
        if (data.check_in_lat && data.check_in_long) {
            coordsSummary.innerText = data.check_in_lat.toFixed(5) + ', ' + data.check_in_long.toFixed(5);
        } else {
            coordsSummary.innerText = 'Koordinat GPS Tidak Tersedia';
        }

        // Tampilkan Modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');

            renderDetailLeafletMap(data);
        }, 50);
    }

    function renderDetailLeafletMap(data) {
        const mapContainer = document.getElementById('attendanceDetailMap');
        if (!mapContainer || typeof L === 'undefined') return;

        // Tentukan titik pusat default (Stasiun atau Check-In atau Umbulan Default)
        let centerLat = data.station_lat || data.check_in_lat || -7.6322;
        let centerLng = data.station_long || data.check_in_long || 112.9056;

        if (!detailMapInstance) {
            detailMapInstance = L.map('attendanceDetailMap').setView([centerLat, centerLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(detailMapInstance);
        } else {
            detailMapInstance.setView([centerLat, centerLng], 15);
        }

        // Bersihkan marker lama
        if (mapMarkerCheckIn) detailMapInstance.removeLayer(mapMarkerCheckIn);
        if (mapMarkerCheckOut) detailMapInstance.removeLayer(mapMarkerCheckOut);
        if (mapCircleStation) detailMapInstance.removeLayer(mapCircleStation);

        const bounds = [];

        // 1. Lingkaran Radius Geofencing Stasiun
        if (data.station_lat && data.station_long) {
            const radMeters = data.station_radius || 100;
            mapCircleStation = L.circle([data.station_lat, data.station_long], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.15,
                radius: radMeters
            }).addTo(detailMapInstance).bindPopup(`<b>${data.station_name}</b><br>Radius Resmi: ${radMeters}m`);

            bounds.push([data.station_lat, data.station_long]);
        }

        // 2. Marker Check-In
        if (data.check_in_lat && data.check_in_long) {
            const inIcon = L.divIcon({
                className: 'custom-pin-in',
                html: '<div style="background-color:#10b981; width:28px; height:28px; border-radius:50%; border:3px solid white; box-shadow:0 4px 6px -1px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; color:white; font-size:12px;"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            mapMarkerCheckIn = L.marker([data.check_in_lat, data.check_in_long], { icon: inIcon })
                .addTo(detailMapInstance)
                .bindPopup(`<b>Presensi Masuk</b><br>Jam: ${data.check_in || '--:--'}<br>Jarak: ${data.check_in_distance || 0}m`);

            bounds.push([data.check_in_lat, data.check_in_long]);
        }

        // 3. Marker Check-Out (Jika ada)
        if (data.check_out_lat && data.check_out_long) {
            const outIcon = L.divIcon({
                className: 'custom-pin-out',
                html: '<div style="background-color:#0284c7; width:28px; height:28px; border-radius:50%; border:3px solid white; box-shadow:0 4px 6px -1px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; color:white; font-size:12px;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            mapMarkerCheckOut = L.marker([data.check_out_lat, data.check_out_long], { icon: outIcon })
                .addTo(detailMapInstance)
                .bindPopup(`<b>Presensi Pulang</b><br>Jam: ${data.check_out || '--:--'}<br>Jarak: ${data.check_out_distance || 0}m`);

            bounds.push([data.check_out_lat, data.check_out_long]);
        }

        if (bounds.length > 1) {
            detailMapInstance.fitBounds(bounds, { padding: [30, 30] });
        }

        setTimeout(() => {
            detailMapInstance.invalidateSize();
        }, 200);
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        const card = document.getElementById('detailModalCard');
        if (!modal || !card) return;

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // -------------------------------------------------------------
    // LIGHTBOX PREVIEW FOTO WATERMARK
    // -------------------------------------------------------------
    function openLightboxModal(imageUrl, titleText) {
        const modal = document.getElementById('lightboxModal');
        const card = document.getElementById('lightboxModalCard');
        const img = document.getElementById('lightboxImage');
        const title = document.getElementById('lightboxTitle');

        if (!modal || !card || !img || !title) return;

        img.src = imageUrl;
        title.querySelector('span').innerText = titleText;

        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeLightboxModal() {
        const modal = document.getElementById('lightboxModal');
        const card = document.getElementById('lightboxModalCard');
        if (!modal || !card) return;

        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('lightboxImage').src = '';
        }, 200);
    }

    // Event Listener Tutup Modal Luar & Tombol ESC
    document.getElementById('customDateModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCustomDateModal();
    });

    document.getElementById('detailModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });

    document.getElementById('lightboxModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeLightboxModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCustomDateModal();
            closeDetailModal();
            closeLightboxModal();
        }
    });
</script>
@endsection
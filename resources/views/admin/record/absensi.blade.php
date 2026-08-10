@extends('layouts.app')

@section('title', 'Rekap Absensi Harian')

@section('content')
<div class="space-y-6">

    <!-- HEADER PAGE -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-user-check text-sky-600"></i>
                Rekap Absensi Harian Karyawan
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pantau karyawan yang sudah dan belum melakukan absensi secara real-time.</p>
        </div>

        <!-- FILTER TANGGAL -->
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="flex items-center gap-2">
            <input type="date" name="tanggal" value="{{ $tanggal }}" 
                   class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500">
            <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    <!-- CARDS STATISTIK SUMMARY -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Karyawan</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-1">{{ count($karyawan) }}</h3>
            </div>
            <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="p-5 bg-white rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Sudah Absen</p>
                <h3 class="text-2xl font-extrabold text-emerald-700 mt-1">{{ count($sudahAbsen) }}</h3>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <div class="p-5 bg-white rounded-2xl border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">Belum Absen</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ count($belumAbsen) }}</h3>
            </div>
            <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-lg">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
    </div>

    <!-- TABEL 1: DAFTAR KARYAWAN SUDAH ABSEN -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-emerald-50/50 flex items-center justify-between">
            <h2 class="font-bold text-sm text-emerald-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                Sudah Absen Hari Ini ({{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }})
            </h2>
            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold">
                {{ count($sudahAbsen) }} Karyawan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="p-4">Karyawan</th>
                        <th class="p-4">Stasiun / Sektor</th>
                        <th class="p-4">Jam Masuk</th>
                        <th class="p-4">Jam Pulang</th>
                        <th class="p-4">Status & Lokasi</th>
                        <th class="p-4">Foto Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sudahAbsen as $item)
                        @php
                            $absen = $item['absen'];
                            
                            // 1. Cek Lokasi Radius GPS
                            $isOutsideIn = isset($absen->is_in_radius_check_in) && !$absen->is_in_radius_check_in;
                            $isOutsideOut = isset($absen->is_in_radius_check_out) && !$absen->is_in_radius_check_out;
                            $isOutside = $isOutsideIn || $isOutsideOut;

                            // 2. Cek Keterlambatan (Absen Masuk)
                            $isLate = false;
                            if (!empty($absen->check_in) && !empty($absen->scheduled_in)) {
                                $isLate = \Carbon\Carbon::parse($absen->check_in)->gt(\Carbon\Carbon::parse($absen->scheduled_in));
                            }

                            // 3. Cek Pulang Cepat (Absen Pulang)
                            $isEarlyOut = false;
                            if (!empty($absen->check_out) && !empty($absen->scheduled_out)) {
                                $cOut = \Carbon\Carbon::parse($absen->check_out);
                                $sOut = \Carbon\Carbon::parse($absen->scheduled_out);
                                $sIn = \Carbon\Carbon::parse($absen->scheduled_in ?? '00:00');

                                // Jika Shift Malam (misal 19:00 - 07:00)
                                if ($sOut->lt($sIn)) {
                                    if ($cOut->gte($sIn) || $cOut->lt($sOut)) {
                                        $isEarlyOut = true;
                                    }
                                } else {
                                    // Shift Normal
                                    if ($cOut->lt($sOut)) {
                                        $isEarlyOut = true;
                                    }
                                }
                            }

                            // Ambil alasan terpisah
                            $reasonIn = $absen->reason_out_of_radius_in ?? null;
                            $reasonOut = $absen->reason_checkout ?? null;
                        @endphp

                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($item['user']->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 leading-tight">{{ $item['user']->name }}</p>
                                        <p class="text-[10px] text-slate-400 uppercase mt-0.5">{{ $item['user']->role->role_name ?? 'STAFF' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-semibold text-slate-600">
                                {{ $item['user']->station->name ?? 'Stasiun Umbulan' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-mono font-bold border border-emerald-100">
                                    {{ $absen->check_in ?? '--:--' }} WIB
                                </span>
                            </td>
                            <td class="p-4">
                                @if(!empty($absen->check_out))
                                    <span class="px-2 py-1 bg-sky-50 text-sky-700 rounded-lg font-mono font-bold border border-sky-100">
                                        {{ $absen->check_out }} WIB
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium italic">Belum Pulang</span>
                                @endif
                            </td>
                            
                            {{-- KOLOM STATUS & LOKASI PERBAIKAN --}}
                            <td class="p-4">
                                <div class="space-y-2 max-w-xs">
                                    {{-- BUBBLE / BADGE STATUS --}}
                                    <div class="flex flex-wrap items-center gap-1">
                                        @if(!$isOutside && !$isLate && !$isEarlyOut)
                                            <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                <i class="fa-solid fa-circle-check mr-1"></i> Tepat Waktu & Dalam Radius
                                            </span>
                                        @else
                                            @if($isOutside)
                                                <span class="inline-flex items-center text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100">
                                                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Luar Radius
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                    <i class="fa-solid fa-location-dot mr-1"></i> Dalam Radius
                                                </span>
                                            @endif

                                            @if($isLate)
                                                <span class="inline-flex items-center text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                                    <i class="fa-solid fa-clock mr-1"></i> Terlambat
                                                </span>
                                            @endif

                                            @if($isEarlyOut)
                                                <span class="inline-flex items-center text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                                                    <i class="fa-solid fa-person-walking-arrow-right mr-1"></i> Pulang Cepat
                                                </span>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- BLOK DAFTAR ALASAN SPESIFIK BERLABEL --}}
                                    @if(!empty($reasonIn) || !empty($reasonOut))
                                        <div class="space-y-1">
                                            {{-- Alasan Absen Datang / Masuk --}}
                                            @if(!empty($reasonIn))
                                                <div class="p-2 bg-rose-50/60 border border-rose-100 rounded-lg text-[11px] text-slate-700 leading-snug">
                                                    <span class="font-bold block text-[10px] uppercase tracking-wider text-rose-700 flex items-center gap-1">
                                                        <i class="fa-solid fa-right-to-bracket text-[9px]"></i> Alasan Masuk:
                                                    </span>
                                                    "{{ $reasonIn }}"
                                                </div>
                                            @endif

                                            {{-- Alasan Absen Pulang --}}
                                            @if(!empty($reasonOut))
                                                <div class="p-2 bg-purple-50/60 border border-purple-100 rounded-lg text-[11px] text-slate-700 leading-snug">
                                                    <span class="font-bold block text-[10px] uppercase tracking-wider text-purple-700 flex items-center gap-1">
                                                        <i class="fa-solid fa-right-from-bracket text-[9px]"></i> Alasan Pulang:
                                                    </span>
                                                    "{{ $reasonOut }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    @if(!empty($absen->face_photo_in))
                                        <a href="{{ asset('storage/' . $absen->face_photo_in) }}" target="_blank" class="text-sky-600 hover:underline font-semibold flex items-center gap-1 text-[11px]">
                                            <i class="fa-solid fa-image"></i> Masuk
                                        </a>
                                    @endif

                                    @if(!empty($absen->face_photo_out))
                                        <a href="{{ asset('storage/' . $absen->face_photo_out) }}" target="_blank" class="text-purple-600 hover:underline font-semibold flex items-center gap-1 text-[11px]">
                                            <i class="fa-solid fa-image"></i> Pulang
                                        </a>
                                    @endif

                                    @if(empty($absen->face_photo_in) && empty($absen->face_photo_out))
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 italic">Belum ada karyawan yang melakukan absen pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABEL 2: DAFTAR KARYAWAN BELUM ABSEN -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
            <h2 class="font-bold text-sm text-rose-900 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                Belum Absen Hari Ini
            </h2>
            <span class="px-2.5 py-1 bg-rose-100 text-rose-700 rounded-full text-[10px] font-bold">
                {{ count($belumAbsen) }} Karyawan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="p-4">Karyawan</th>
                        <th class="p-4">Stasiun Kerja</th>
                        <th class="p-4">Role / Jabatan</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($belumAbsen as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-semibold text-slate-600">
                                {{ $user->station->name ?? 'Stasiun Umbulan' }}
                            </td>
                            <td class="p-4 font-semibold text-slate-500 uppercase">
                                {{ $user->role->role_name ?? 'STAFF' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-600 font-bold rounded-lg border border-rose-100 text-[10px]">
                                    Belum Hadir / Tanpa Keterangan
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-emerald-600 font-bold">Luar biasa! Semua karyawan sudah melakukan absen hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
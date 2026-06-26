@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Banner Peringatan jika Email Belum Diverifikasi --}}
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
                    <a href="profile" class="text-xs font-bold text-indigo-800 underline hover:text-indigo-900 transition-colors">
                        Klik di sini untuk memverifikasi nomor telepon &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

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

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800">Riwayat Cuti Anda</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan izin cuti Anda pada periode tahun berjalan.</p>
            </div>
            <a href="/cuti/ajukan" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition-colors flex items-center space-x-1">
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
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $cuti->name_cuti }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">{{ $cuti->total_hari }} Hari</td>
                            <td class="px-6 py-4 text-slate-500 text-xs max-w-xs truncate" title="{{ $cuti->alasan_cuti }}">
                                {{ $cuti->alasan_cuti }}
                            </td>
                            <td class="px-6 py-4">
                                @if($cuti->status_manager == 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($cuti->status_supervisor == 'rejected' || $cuti->status_manager == 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>

                                        {{-- 🌟 PERBAIKAN: Menampilkan Catatan Penolakan di bawah Status Ditolak --}}
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
                            {{-- 🌟 PERBAIKAN: Mengubah colspan menjadi 5 agar lurus sempurna mengikuti jumlah kolom <th> --}}
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
</div>
@endsection

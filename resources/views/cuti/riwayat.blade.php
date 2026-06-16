@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Riwayat Pengajuan Cuti Anda</h2>
                <p class="text-sm text-slate-500 mt-0.5">Daftar seluruh permohonan cuti yang pernah Anda ajukan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Jenis Cuti</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4">Durasi</th>
                        <th class="px-6 py-4">Status Persetujuan</th>
                        <th class="px-6 py-4">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($riwayatCuti as $cuti)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $cuti->name_cuti ?? 'Cuti Umum' }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">{{ $cuti->total_hari }} Hari</td>
                            <td class="px-6 py-4">
                                @if($cuti->status_manager == 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($cuti->status_supervisor == 'rejected' || $cuti->status_manager == 'rejected')
                                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-sm max-w-xs truncate" title="{{ $cuti->alasan_cuti }}">
                                {{ $cuti->alasan_cuti }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block text-slate-200"></i>
                                Belum ada riwayat pengajuan cuti yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

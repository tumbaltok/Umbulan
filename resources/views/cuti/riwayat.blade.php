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
                        <th class="px-6 py-4">Jenis Cuti / Detail</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4">Durasi</th>
                        <th class="px-6 py-4">Catatan Alasan</th>
                        <th class="px-6 py-4">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($pengajuanCuti as $cuti)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- JENIS CUTI & DETAIL SUB-CUTI --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $cuti->name_cuti }}</div>

                                {{-- Jauh lebih simpel, langsung panggil kolom nama_sub_cuti dari tabel --}}
                                @if(!empty($cuti->nama_sub_cuti))
                                    <div class="text-xs text-sky-600 font-medium mt-0.5 bg-sky-50 px-2 py-0.5 rounded w-fit border border-sky-100">
                                        {{ $cuti->nama_sub_cuti }}
                                    </div>
                                @endif
                            </td>

                            {{-- TANGGAL --}}
                            <td class="px-6 py-4 text-slate-500 text-sm">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>

                            {{-- DURASI --}}
                            <td class="px-6 py-4 font-medium text-sm">{{ $cuti->total_hari }} Hari</td>

                            {{-- CATATAN TAMBAHAN --}}
                            <td class="px-6 py-4 text-slate-500 text-sm max-w-xs truncate" title="{{ $cuti->alasan_cuti }}">
                                {{ $cuti->alasan_cuti ?? '-' }}
                            </td>

                            {{-- STATUS MANAGEMENT --}}
                            <td class="px-6 py-4">
                                @if($cuti->status_manager == 'approved')
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>
                                        <a href="{{ route('cuti.cetak', $cuti->id) }}" target="_blank" class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-sm w-fit">
                                            <span>Cetak</span>
                                        </a>
                                    </div>
                                @elseif($cuti->status_supervisor == 'rejected' || $cuti->status_manager == 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
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
                            {{-- BENAR: Diubah ke colspan="5" karena total kolom ada 5 buah --}}
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
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

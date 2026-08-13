@extends('layouts.app')
@section('title', 'Riwayat Pengajuan MPR')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-6xl mx-auto m-2 sm:m-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
                <i class="fa-solid fa-boxes-packing text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Riwayat Pengajuan MPR</h2>
                <p class="text-xs text-slate-400">Daftar riwayat permohonan Material Purchase Request Anda</p>
            </div>
        </div>
        <a href="{{ route('mpr.create') }}" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-md shadow-sky-600/10">
            <i class="fa-solid fa-plus"></i> Ajukan MPR Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-wider border-y border-slate-100">
                <tr>
                    <th class="py-3 px-4">No. MPR / Tanggal</th>
                    <th class="py-3 px-4">Keperluan / Urgensi</th>
                    <th class="py-3 px-4">Rincian Material</th>
                    <th class="py-3 px-4">Status Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayatMpr as $mpr)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-800 block text-xs">{{ $mpr->nomor_mpr }}</span>
                            <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-medium text-slate-700 max-w-xs">
                            {{ $mpr->keperluan_urgensi }}
                        </td>
                        <td class="py-3.5 px-4 text-xs">
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach($mpr->items as $item)
                                    <li><span class="font-semibold text-slate-700">{{ $item->nama_barang }}</span> ({{ $item->jumlah }} {{ $item->satuan }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($mpr->status_akhir === 'approved')
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-[10px] font-bold uppercase">Disetujui</span>
                            @elseif($mpr->status_akhir === 'rejected')
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[10px] font-bold uppercase">Ditolak</span>
                            @else
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg text-[10px] font-bold uppercase">Menunggu</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($mpr->status_akhir === 'approved')
                                <a href="{{ route('mpr.cetak', $mpr->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold transition-colors">
                                    <i class="fa-solid fa-print"></i> Cetak PDF
                                </a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400 text-xs">
                            Belum ada riwayat pengajuan MPR.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
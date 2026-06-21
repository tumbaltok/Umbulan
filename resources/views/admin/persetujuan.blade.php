@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Daftar Pengajuan Cuti Karyawan</h2>
            <p class="text-sm text-slate-500 mt-0.5">Halaman khusus Atasan untuk meninjau dan memproses permohonan cuti staf</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Karyawan</th>
                        <th class="px-6 py-4">Jenis Cuti</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4">Total Hari</th>
                        <th class="px-6 py-4">Status SPV</th>
                        <th class="px-6 py-4">Status Manager</th>
                        <th class="px-6 py-4 text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarPengajuan as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $item->user_name }}</td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $item->nama_sub_cuti }}</div>
                                @if(!empty($item->dokumen_pendukung))
                                    <div class="mt-1">
                                        <a href="{{ asset('uploads/dokumen_cuti/' . $item->dokumen_pendukung) }}" target="_blank" class="inline-flex items-center text-xs font-semibold text-sky-600 hover:text-sky-700 underline">
                                            <i class="fa-solid fa-paperclip mr-1"></i> Lihat Berkas Fisik
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-normal italic">Tidak ada berkas</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4 font-mono font-bold">{{ $item->total_hari }} Hari</td>

                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $item->status_supervisor == 'approved' ? 'bg-emerald-50 text-emerald-700' : ($item->status_supervisor == 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ ucfirst($item->status_supervisor) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $item->status_manager == 'approved' ? 'bg-emerald-50 text-emerald-700' : ($item->status_manager == 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ ucfirst($item->status_manager) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <form action="{{ route('cuti.proses-persetujuan', $item->id) }}" method="POST" class="inline-flex space-x-2">
                                    @csrf
                                    <input type="text" name="catatan_penolakan" placeholder="Alasan jika menolak..." class="px-2 py-1 text-xs border border-slate-200 rounded focus:outline-none focus:border-sky-500">

                                    <button type="submit" name="tindakan" value="approved" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition-colors">
                                        Approve
                                    </button>
                                    <button type="submit" name="tindakan" value="rejected" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-bold transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-check text-3xl mb-2 block text-slate-200"></i>
                                Tidak ada pengajuan cuti masuk yang perlu ditinjau.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

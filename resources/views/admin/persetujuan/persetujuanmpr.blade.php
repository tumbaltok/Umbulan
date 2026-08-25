@extends('layouts.app')
@section('title', 'Persetujuan MPR')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-6xl mx-auto m-2 sm:m-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
            <i class="fa-solid fa-boxes-packing text-xl"></i>
        </div>
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800">Daftar Persetujuan MPR</h2>
            <p class="text-xs text-slate-400">Persetujuan dokumen Material Purchase Request bawahan</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($daftarPengajuan as $mpr)
            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-200/60 pb-3">
                    <div>
                        <span class="text-xs font-bold text-sky-600 block">{{ $mpr->nomor_mpr }}</span>
                        <h3 class="text-sm font-bold text-slate-800">{{ $mpr->user->name }} <span class="text-xs font-normal text-slate-500">({{ $mpr->user->station->name ?? 'Stasiun Umbulan' }})</span></h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $mpr->status_tahap_1 === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($mpr->status_tahap_1 === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                Step 1: {{ ucfirst($mpr->status_tahap_1) }}
                            </span>
                            @if($mpr->status_tahap_2 !== 'not_required')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $mpr->status_tahap_2 === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($mpr->status_tahap_2 === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    Step 2: {{ ucfirst($mpr->status_tahap_2) }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">
                                    -
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <p class="font-semibold text-slate-700">Keperluan / Urgensi:</p>
                    <p class="bg-white p-2.5 rounded-xl border border-slate-200 text-slate-700">{{ $mpr->keperluan_urgensi }}</p>
                </div>

                <div class="text-xs space-y-1">
                    <p class="font-semibold text-slate-700">Detail Barang yang Diminta:</p>
                    <div class="bg-white p-3 rounded-xl border border-slate-200 overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-[10px] text-slate-400 uppercase border-b border-slate-100">
                                <tr>
                                    <th class="py-1 px-2">Nama Barang</th>
                                    <th class="py-1 px-2">Qty</th>
                                    <th class="py-1 px-2">Est. Harga</th>
                                    <th class="py-1 px-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($mpr->items as $item)
                                    <tr>
                                        <td class="py-1.5 px-2 font-medium text-slate-800">{{ $item->nama_barang }}</td>
                                        <td class="py-1.5 px-2">{{ $item->jumlah }} {{ $item->satuan }}</td>
                                        <td class="py-1.5 px-2">Rp {{ number_format($item->estimasi_harga, 0, ',', '.') }}</td>
                                        <td class="py-1.5 px-2 text-slate-400">{{ $item->keterangan_item ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2 border-t border-slate-200/60">
                    <form id="form-reject-mpr-{{ $mpr->id }}" action="{{ route('admin.persetujuan.mpr.process', $mpr->id) }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="tindakan" value="rejected">
                        <button type="button" onclick="konfirmasiAksi('form-reject-mpr-{{ $mpr->id }}', 'Tolak Pengajuan MPR?', '#dc2626', 'Ya, Tolak')" class="w-1/2 sm:w-auto bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs px-4 py-2 rounded-xl transition-colors">
                            Tolak
                        </button>
                    </form>

                    <form id="form-approve-mpr-{{ $mpr->id }}" action="{{ route('admin.persetujuan.mpr.process', $mpr->id) }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="tindakan" value="approved">
                        <button type="button" onclick="konfirmasiAksi('form-approve-mpr-{{ $mpr->id }}', 'Setujui Pengajuan MPR?', '#059669', 'Ya, Setujui')" class="w-1/2 sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-5 py-2 rounded-xl shadow-sm transition-colors">
                            Setujui
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-slate-50 rounded-2xl border border-slate-200">
                <i class="fa-solid fa-clipboard-check text-slate-300 text-3xl mb-2 block"></i>
                <p class="text-xs text-slate-400 font-medium">Tidak ada pengajuan MPR yang membutuhkan persetujuan Anda saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0284c7',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold'
            }
        });
    });
</script>
@endif

<script>
    function konfirmasiAksi(formId, pesan, warnaTombol, teksTombol) {
        Swal.fire({
            title: 'Konfirmasi Tindakan',
            text: pesan,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: warnaTombol,
            cancelButtonColor: '#64748b',
            confirmButtonText: teksTombol,
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'px-4 py-2 rounded-xl font-semibold',
                cancelButton: 'px-4 py-2 rounded-xl font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endpush

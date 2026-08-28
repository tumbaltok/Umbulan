@extends('layouts.app')
@section('title', 'Riwayat Pengajuan MPR')

@section('content')
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-4 sm:p-6 max-w-6xl mx-auto m-2 sm:m-6 transition-colors">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="bg-sky-50 dark:bg-sky-950/50 p-3 rounded-xl text-sky-600 dark:text-sky-400">
                <i class="fa-solid fa-boxes-packing text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">Riwayat Pengajuan MPR</h2>
                <p class="text-xs text-slate-400">Daftar riwayat permohonan Material Purchase Request Anda</p>
            </div>
        </div>
        <a href="{{ route('mpr.create') }}" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-md shadow-sky-600/10">
            <i class="fa-solid fa-plus"></i> Ajukan MPR Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-y border-slate-100 dark:border-slate-700">
                <tr>
                    <th class="py-3 px-4">No. MPR / Tanggal</th>
                    <th class="py-3 px-4">Priority & Lokasi</th>
                    <th class="py-3 px-4">Keperluan / Urgensi</th>
                    <th class="py-3 px-4">Rincian Material</th>
                    <th class="py-3 px-4">Status Akhir</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($riwayatMpr as $mpr)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">{{ $mpr->nomor_mpr }}</span>
                            <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($mpr->priority === 'Emergency')
                                <span class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-md text-[10px] font-bold uppercase">Emergency</span>
                            @elseif($mpr->priority === 'Urgent')
                                <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-md text-[10px] font-bold uppercase">Urgent</span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-md text-[10px] font-semibold uppercase">Normal</span>
                            @endif
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $mpr->delivery_point ?? 'Site Umbulan' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-medium text-slate-700 dark:text-slate-300 max-w-xs whitespace-pre-line">
                            {{ \Illuminate\Support\Str::limit($mpr->keperluan_urgensi, 80) }}
                        </td>
                        <td class="py-3.5 px-4 text-xs">
                            <ul class="list-disc pl-4 space-y-0.5 text-slate-600 dark:text-slate-300">
                                @foreach($mpr->items as $item)
                                    <li><span class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->nama_barang }}</span> ({{ $item->jumlah }} {{ $item->satuan }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($mpr->status_akhir === 'approved')
                                <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-lg text-[10px] font-bold uppercase">Disetujui</span>
                            @elseif($mpr->status_akhir === 'rejected')
                                <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-lg text-[10px] font-bold uppercase">Ditolak</span>
                            @else
                                <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-[10px] font-bold uppercase">Menunggu</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($mpr->status_akhir === 'approved')
                                <button type="button"
                                        onclick="bukaPratinjauCetak('{{ route('mpr.cetak', $mpr->id) }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                    <i class="fa-solid fa-print"></i> Cetak PDF
                                </button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400 text-xs">
                            Belum ada riwayat pengajuan MPR.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL POPUP PRATINJAU DOKUMEN CETAK MPR --}}
<div id="modalPreviewLampiran" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-3xl h-[85vh] flex flex-col shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-sky-600 dark:text-sky-400 text-base"></i>
                <h3 id="judulModalLampiran" class="text-sm font-bold text-slate-800 dark:text-slate-100">Pratinjau Dokumen Cetak MPR</h3>
            </div>
            <button type="button" onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Konten Utama / Tempat Render File PDF --}}
        <div id="containerKontenLampiran" class="flex-1 bg-slate-100 dark:bg-slate-900 flex items-center justify-center p-2 sm:p-4 overflow-auto">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bukaPratinjauCetak(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Dokumen Cetak MPR';
        tampilkanModal(urlFile, true);
    }

    function tampilkanModal(urlFile, isPdfFormated = false) {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');

        container.innerHTML = '<div class="text-xs text-slate-400 font-medium animate-pulse">Memuat dokumen...</div>';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const ekstensi = urlFile.split('.').pop().toLowerCase();

        if (isPdfFormated || ekstensi === 'pdf') {
            container.innerHTML = `<iframe src="${urlFile}" class="w-full h-full rounded-xl border-0 shadow-inner" allow="autoplay"></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ekstensi)) {
            container.innerHTML = `<img src="${urlFile}" class="max-w-full max-h-full rounded-xl shadow-md object-contain" alt="Pratinjau Dokumen">`;
        } else {
            container.innerHTML = `
                <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-slate-200 max-w-xs">
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

        modal.classList.remove('flex');
        modal.classList.add('hidden');
        container.innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    document.getElementById('modalPreviewLampiran').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupPratinjauLampiran();
        }
    });
</script>
@endpush

@extends('layouts.app')
@section('title', 'Riwayat Pengajuan CAR')
@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Riwayat Pengajuan Uang Material (CAR)</h2>
        <p class="text-xs text-slate-400">Daftar pemantauan status persetujuan berkas pembelian barang Anda</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <th class="p-4" style="width: 15%;">Tanggal</th>
                    <th class="p-4" style="width: 50%;">Daftar Barang & Nota</th>
                    <th class="p-4" style="width: 15%;">Total Biaya</th>
                    <th class="p-4 text-center" style="width: 20%;">Status Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($riwayatCar as $car)
                <tr>
                    <td class="p-4 whitespace-nowrap align-middle">{{ $car->created_at->format('d M Y') }}</td>

                    {{-- Loop data barang dari relasi details --}}
                    <td class="p-4 align-top">
                        <ul class="space-y-2">
                            @foreach($car->details as $detail)
                                <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                                    <div class="space-y-0.5">
                                        <span class="font-medium text-slate-900 block">{{ $detail->nama_barang }}</span>
                                        <div class="text-xs text-slate-500 flex flex-wrap items-center gap-1.5">
                                            <span class="font-semibold text-slate-700">Rp {{ number_format($detail->estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-slate-400">x {{ $detail->jumlah }} Qty</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-slate-400">Total:</span>
                                            <span class="font-bold text-slate-700">Rp {{ number_format(($detail->total_harga ?? ($detail->estimasi_harga * $detail->jumlah)), 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @if($detail->dokumen_nota_or_proposal)
                                        <button type="button"
                                                onclick="bukaPratinjauLampiran('{{ asset('storage/' . $detail->dokumen_nota_or_proposal) }}')"
                                                class="inline-flex items-center gap-1 text-xs text-sky-600 hover:text-sky-700 font-semibold bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-100 w-fit cursor-pointer self-start sm:self-center shrink-0 transition-colors">
                                            <i class="fa-solid fa-eye"></i> Lampiran Car
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-1 rounded-lg w-fit self-start sm:self-center shrink-0">Tanpa Nota</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>

                    {{-- Menghitung akumulasi grand total biaya dari semua detail barang --}}
                    <td class="p-4 font-bold text-emerald-600 align-middle whitespace-nowrap">
                        Rp {{ number_format($car->details->sum('total_harga'), 0, ',', '.') }}
                    </td>

                    {{-- Menampilkan Status Akhir beserta tombol cetak popup --}}
                    <td class="p-4 text-center align-middle whitespace-nowrap">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <span class="px-3 py-1 text-xs font-extrabold tracking-wide uppercase rounded-full
                                @if(trim(strtolower($car->status_akhir)) === 'approved') bg-emerald-500 text-white
                                @elseif(trim(strtolower($car->status_akhir)) === 'rejected') bg-rose-500 text-white
                                @else bg-amber-500 text-white @endif">
                                {{ $car->status_akhir }}
                            </span>

                            @if(trim(strtolower($car->status_akhir)) === 'rejected' && !empty($car->catatan_penolakan))
                                <span class="text-[11px] text-rose-600 font-medium italic max-w-[200px] whitespace-normal text-center bg-rose-5 px-2 py-0.5 rounded border border-rose-100">
                                    Keterangan : {{ $car->catatan_penolakan }}
                                </span>
                            @endif

                            @if(trim(strtolower($car->status_supervisor)) === 'approved')
                                @if(trim(strtolower($car->status_manager)) === 'approved')
                                    @if(trim(strtolower($car->status_akhir)) === 'approved')
                                        <button type="button"
                                                onclick="bukaPratinjauCetak('{{ route('car.print', $car->id) }}')"
                                                class="inline-flex items-center gap-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-print"></i> Cetak CAR
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400">Belum ada pengajuan CAR terkini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL POPUP PRATINJAU LAMPIRAN & CETAK --}}
<div id="modalPreviewLampiran" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-3xl h-[85vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-sky-600 text-base"></i>
                <h3 id="judulModalLampiran" class="text-sm font-bold text-slate-800">Pratinjau Lampiran Dokumen</h3>
            </div>
            <button type="button" onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Konten Utama / Tempat Render File --}}
        <div id="containerKontenLampiran" class="flex-1 bg-slate-100 flex items-center justify-center p-2 sm:p-4 overflow-auto">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bukaPratinjauLampiran(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Lampiran Dokumen';
        tampilkanModal(urlFile);
    }

    // Fungsi Baru khusus untuk menampilkan hasil Cetak CAR di dalam Modal
    function bukaPratinjauCetak(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Dokumen Cetak CAR';
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

        // Jika dipanggil oleh cetak CAR atau file ber-ekstensi PDF
        if (isPdfFormated || ekstensi === 'pdf') {
            container.innerHTML = `<iframe src="${urlFile}" class="w-full h-full rounded-xl border-0 shadow-inner" allow="autoplay"></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ekstensi)) {
            container.innerHTML = `<img src="${urlFile}" class="max-w-full max-h-full rounded-xl shadow-md object-contain" alt="Pratinjau Nota">`;
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

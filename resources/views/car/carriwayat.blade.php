@extends('layouts.app')
@section('title', 'Riwayat Pengajuan CAR')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-6xl mx-auto m-2 sm:m-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
                <i class="fa-solid fa-receipt text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Riwayat Pengajuan Uang Material (CAR)</h2>
                <p class="text-xs text-slate-400">Daftar pemantauan status persetujuan berkas pembelian barang Anda</p>
            </div>
        </div>
        <a href="{{ route('car.create') }}" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-md shadow-sky-600/10">
            <i class="fa-solid fa-plus"></i> Ajukan CAR Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-wider border-y border-slate-100">
                <tr>
                    <th class="py-3 px-4" style="width: 18%;">No. CAR / Tanggal</th>
                    <th class="py-3 px-4" style="width: 42%;">Rincian Barang & Nota</th>
                    <th class="py-3 px-4" style="width: 18%;">Total Biaya</th>
                    <th class="py-3 px-4 text-center" style="width: 12%;">Status Akhir</th>
                    <th class="py-3 px-4 text-center" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($riwayatCar as $car)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3.5 px-4 align-top">
                        <span class="font-bold text-slate-800 block text-xs">{{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">{{ $car->tanggal_pengajuan ? \Carbon\Carbon::parse($car->tanggal_pengajuan)->format('d M Y') : $car->created_at->format('d M Y') }}</span>
                    </td>

                    {{-- Loop data barang dari relasi details --}}
                    <td class="py-3.5 px-4 align-top">
                        <ul class="space-y-2">
                            @foreach($car->details as $detail)
                                <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50 p-2.5 rounded-xl border border-slate-100">
                                    <div class="space-y-0.5">
                                        <span class="font-medium text-slate-900 block text-xs">{{ $detail->nama_barang }}</span>
                                        <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-1.5">
                                            <span class="font-semibold text-slate-700">Rp {{ number_format($detail->estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-slate-400">x {{ $detail->jumlah }} {{ $detail->satuan }}</span>
                                            @if(($detail->ongkir ?? 0) > 0)
                                                <span class="text-slate-300">|</span>
                                                <span class="text-amber-600 font-medium">+ Ongkir: Rp {{ number_format($detail->ongkir, 0, ',', '.') }}</span>
                                            @endif
                                            <span class="text-slate-300">|</span>
                                            <span class="text-slate-400">Total:</span>
                                            <span class="font-bold text-slate-700">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @if($detail->dokumen_nota_or_proposal)
                                        <button type="button"
                                                data-url="{{ asset('storage/' . $detail->dokumen_nota_or_proposal) }}"
                                                onclick="bukaPratinjauLampiran(this.dataset.url)"
                                                class="self-start sm:self-auto inline-flex items-center gap-1 text-[11px] bg-white hover:bg-slate-100 text-sky-600 font-semibold px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                                            <i class="fa-solid fa-file-invoice"></i> Nota
                                        </button>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>

                    {{-- Menghitung akumulasi grand total biaya dari semua detail barang --}}
                    <td class="py-3.5 px-4 align-top whitespace-nowrap">
                        <div class="font-bold text-emerald-600 text-xs">
                            Rp {{ number_format($car->details->sum('total_harga'), 0, ',', '.') }}
                        </div>
                        @php $totalOngkirCar = $car->details->sum('ongkir'); @endphp
                        @if($totalOngkirCar > 0)
                            <span class="block text-[10px] text-amber-600 font-medium mt-0.5">
                                Termasuk Ongkir: Rp {{ number_format($totalOngkirCar, 0, ',', '.') }}
                            </span>
                        @endif
                    </td>

                    {{-- Status Persetujuan --}}
                    <td class="py-3.5 px-4 text-center align-top whitespace-nowrap">
                        @if(trim(strtolower($car->status_akhir)) === 'approved')
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-[10px] font-bold uppercase">Disetujui</span>
                        @elseif(trim(strtolower($car->status_akhir)) === 'rejected')
                            <span class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[10px] font-bold uppercase">Ditolak</span>
                            @if(!empty($car->catatan_penolakan))
                                <span class="block text-[11px] text-rose-600 font-medium italic max-w-[180px] whitespace-normal mx-auto mt-1.5 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                                    {{ $car->catatan_penolakan }}
                                </span>
                            @endif
                        @else
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg text-[10px] font-bold uppercase">Menunggu</span>
                        @endif
                    </td>

                    {{-- Aksi (Cetak PDF jika approved, - jika belum) --}}
                    <td class="py-3.5 px-4 text-center align-top whitespace-nowrap">
                        @if(trim(strtolower($car->status_akhir)) === 'approved')
                            <button type="button"
                                    onclick="bukaPratinjauCetak('{{ route('car.print', $car->id) }}')"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                                <i class="fa-solid fa-print"></i> Cetak PDF
                            </button>
                        @else
                            <span class="text-xs text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 text-xs">
                        Belum ada riwayat pengajuan CAR terkini.
                    </td>
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

    // Fungsi khusus untuk menampilkan hasil Cetak CAR di dalam Modal
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

@extends('layouts.app')
@section('title', 'Ajukan MPR Baru')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-5xl mx-auto m-2 sm:m-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
            <i class="fa-solid fa-boxes-packing text-xl"></i>
        </div>
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800">Form Pengajuan MPR</h2>
            <p class="text-xs text-slate-400">Pengajuan Material Purchase Request (Pembelian Material / Barang)</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mpr.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Informasi Umum Pengajuan --}}
        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sky-500"></i> Informasi Umum Pengajuan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Keperluan / Urgensi Pemakaian</label>
                    <textarea name="keperluan_urgensi" rows="2" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm" placeholder="Tuliskan detail urgensi pemakaian material/barang ini..."></textarea>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Dokumen Pendukung Utama (Opsional)</label>
                    <input type="file" name="dokumen_pendukung" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
            </div>
        </div>

        {{-- Section 2: Rincian Material / Barang yang Diminta --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-2">
                <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Rincian Material / Barang
                </h3>
                <button type="button" id="btn-tambah-item" class="w-full sm:w-auto bg-sky-50 hover:bg-sky-100 text-sky-600 font-semibold text-xs px-3 py-2 rounded-xl transition-colors flex items-center justify-center gap-1">
                    <i class="fa-solid fa-plus"></i> Tambah Item
                </button>
            </div>

            {{-- Container Baris Item --}}
            <div id="container-item" class="space-y-4">
                {{-- Baris Pertama (Default Row) --}}
                <div class="baris-item bg-white p-4 rounded-xl border border-slate-200 relative shadow-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                        <div class="sm:col-span-2 md:col-span-4">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Barang / Material</label>
                            <input type="text" name="items[0][nama_barang]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Contoh: Valve Gate 2 Inch">
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 sm:col-span-2 md:col-span-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Qty</label>
                                <input type="number" name="items[0][jumlah]" required min="1" class="input-jumlah w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Qty">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Satuan</label>
                                <input type="text" name="items[0][satuan]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Pcs/Unit/Box">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Est. Harga (Rp)</label>
                                <input type="number" name="items[0][estimasi_harga]" min="0" class="input-harga w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Harga">
                            </div>
                        </div>

                        <div class="md:col-span-2 flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t border-slate-50 md:border-none">
                            <div class="text-left md:text-right">
                                <span class="block text-[10px] text-slate-400 uppercase font-bold">Subtotal</span>
                                <span class="text-sm font-bold text-slate-700 label-subtotal">Rp 0</span>
                            </div>
                            <button type="button" class="btn-hapus-item text-slate-300 cursor-not-allowed p-2 rounded-lg md:mt-4" disabled>
                                <i class="fa-solid fa-trash-can text-base"></i>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan Spesifikasi Barang (Opsional)</label>
                        <input type="text" name="items[0][keterangan_item]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:border-sky-500" placeholder="Spesifikasi / Merek / Tipe barang...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Total Akumulasi --}}
        <div class="p-4 bg-slate-900 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-white shadow-lg shadow-slate-900/10">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Estimasi Total MPR:</span>
                <span class="text-xs text-slate-500 font-medium" id="label-jumlah-item">1 Macam Item</span>
            </div>
            <span id="grand_total" class="text-lg sm:text-xl font-black text-emerald-400">Rp 0</span>
        </div>

        <div class="pt-2 flex">
            <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm px-8 py-3 rounded-xl shadow-md shadow-sky-600/10 transition-colors ml-auto">
                Kirim Pengajuan MPR
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const containerItem = document.getElementById('container-item');
    const btnTambahItem = document.getElementById('btn-tambah-item');
    const grandTotalOutput = document.getElementById('grand_total');
    const labelJumlahItem = document.getElementById('label-jumlah-item');

    let itemIndex = 1;

    function hitungAkumulasi() {
        let akumulasiGrandTotal = 0;
        const semuaBaris = containerItem.querySelectorAll('.baris-item');

        semuaBaris.forEach(baris => {
            const inputJumlah = baris.querySelector('.input-jumlah');
            const inputHarga = baris.querySelector('.input-harga');
            const labelSubtotal = baris.querySelector('.label-subtotal');

            const qty = parseFloat(inputJumlah.value) || 0;
            const harga = parseFloat(inputHarga.value) || 0;
            const subtotal = qty * harga;

            akumulasiGrandTotal += subtotal;
            labelSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        });

        grandTotalOutput.textContent = 'Rp ' + akumulasiGrandTotal.toLocaleString('id-ID');
        labelJumlahItem.textContent = `${semuaBaris.length} Macam Item`;
    }

    containerItem.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-jumlah') || e.target.classList.contains('input-harga')) {
            hitungAkumulasi();
        }
    });

    btnTambahItem.addEventListener('click', function() {
        const barisBaru = document.createElement('div');
        barisBaru.className = "baris-item bg-white p-4 rounded-xl border border-slate-200 relative shadow-sm space-y-4 transition-all";

        barisBaru.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                <div class="sm:col-span-2 md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Barang / Material</label>
                    <input type="text" name="items[${itemIndex}][nama_barang]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Nama barang">
                </div>
                
                <div class="grid grid-cols-3 gap-2 sm:col-span-2 md:col-span-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qty</label>
                        <input type="number" name="items[${itemIndex}][jumlah]" required min="1" class="input-jumlah w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Qty">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Satuan</label>
                        <input type="text" name="items[${itemIndex}][satuan]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Satuan">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Est. Harga (Rp)</label>
                        <input type="number" name="items[${itemIndex}][estimasi_harga]" min="0" class="input-harga w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Harga">
                    </div>
                </div>

                <div class="md:col-span-2 flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t border-slate-50 md:border-none">
                    <div class="text-left md:text-right">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Subtotal</span>
                        <span class="text-sm font-bold text-slate-700 label-subtotal">Rp 0</span>
                    </div>
                    <button type="button" class="btn-hapus-item text-rose-500 hover:text-rose-700 transition-colors p-2 rounded-lg md:mt-4">
                        <i class="fa-solid fa-trash-can text-base"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan Spesifikasi Barang (Opsional)</label>
                <input type="text" name="items[${itemIndex}][keterangan_item]" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:border-sky-500" placeholder="Spesifikasi / Merek / Tipe barang...">
            </div>
        `;

        containerItem.appendChild(barisBaru);
        itemIndex++;
        hitungAkumulasi();
    });

    containerItem.addEventListener('click', function(e) {
        const tombolHapus = e.target.closest('.btn-hapus-item');
        if (tombolHapus && !tombolHapus.disabled) {
            const baris = tombolHapus.closest('.baris-item');
            baris.remove();
            hitungAkumulasi();
        }
    });
</script>
@endpush
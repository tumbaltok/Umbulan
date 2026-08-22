@extends('layouts.app')
@section('title', 'Ajukan CAR Baru')
@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-5xl mx-auto m-2 sm:m-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
            <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
        </div>
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800">Form Pengajuan CAR</h2>
            <p class="text-xs text-slate-400">Pengajuan anggaran dengan nota/dokumen pendukung yang terpisah pada setiap barang</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('car.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Informasi Umum --}}
        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sky-500"></i> Informasi Umum Pengajuan
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Alasan Pembelian / Urgensi Keperluan</label>
                    <textarea name="alasan_pembelian" rows="2" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm" placeholder="Tuliskan detail keseluruhan urgensi pemakaian barang-barang ini..."></textarea>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Receiving Account</label>
                    <select name="receiving_account" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm cursor-pointer">
                        <option value="" disabled selected>--- Pilih Penerima ---</option>
                        @foreach($daftarStasiun as $stasiun)
                            <option value="{{ $stasiun->name }}">{{ $stasiun->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2: Daftar Item / Produk beserta Nota --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-2">
                <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Rincian Material & Nota Pendukung
                </h3>
                <button type="button" id="btn-tambah-item" class="w-full sm:w-auto bg-sky-50 hover:bg-sky-100 text-sky-600 font-semibold text-xs px-3 py-2 rounded-xl transition-colors flex items-center justify-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Tambah Item (Add Product)
                </button>
            </div>

            {{-- Container Baris Item --}}
            <div id="container-item" class="space-y-4">
                {{-- Baris pertama bawaan (Default Row) --}}
                <div class="baris-item bg-white p-4 rounded-xl border border-slate-200 relative shadow-sm space-y-4">
                    {{-- Grid Atas: Input Data Nominal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                        <div class="sm:col-span-2 md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Barang / Material</label>
                            <input type="text" name="items[0][nama_barang]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Contoh: Pipa PVC 2 Inch">
                        </div>

                        <div class="grid grid-cols-12 gap-2 sm:col-span-2 md:col-span-7">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Qty</label>
                                <input type="number" name="items[0][jumlah]" required min="1" class="input-jumlah w-full px-2 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500 text-center" placeholder="Qty">
                            </div>

                            <div class="col-span-3">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Satuan</label>
                                <select name="items[0][satuan]" required class="w-full px-2 py-2 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:border-sky-500 cursor-pointer">
                                    <option value="" disabled selected hidden>Pilih</option>
                                    <option value="PCS">Pcs</option>
                                    <option value="Unit">Unit</option>
                                    <option value="BOX">Box</option>
                                    <option value="Meter">Meter</option>
                                    <option value="Batang">Batang</option>
                                    <option value="Set">Set</option>
                                </select>
                            </div>

                            <div class="col-span-4">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Harga Satuan (Rp)</label>
                                <input type="number" name="items[0][estimasi_harga]" required min="0" class="input-harga w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Harga">
                            </div>

                            {{-- Container Dynamic Ongkir --}}
                            <div class="col-span-3 flex flex-col justify-end container-ongkir">
                                <button type="button" class="btn-tambah-ongkir h-[38px] px-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs rounded-lg border border-amber-200 transition-colors flex items-center justify-center gap-1 cursor-pointer w-full">
                                    + <i class="fa-solid fa-truck-fast"></i> Ongkir
                                </button>
                                <div class="wrapper-input-ongkir hidden">
                                    <label class="block text-[10px] font-semibold text-amber-600 mb-1">Ongkir (Rp)</label>
                                    <input type="number" name="items[0][ongkir]" min="0" value="" class="input-ongkir w-full px-2 py-1.5 rounded-lg border border-amber-300 text-sm focus:outline-none focus:border-amber-500 bg-amber-50/30" placeholder="Ongkir">
                                </div>
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

                    {{-- Grid Bawah: Input Dokumen Khusus Item Ini --}}
                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <label class="block text-xs font-semibold text-slate-600">Upload Nota / Foto Barang</label>

                        <input type="file"
                            name="items[0][dokumen_pendukung]"
                            required
                            class="input-file-dokumen w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-600 text-xs focus:outline-none focus:border-sky-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">

                        <p class="text-[10px] text-slate-400 mt-1">* Format: PDF, JPG, JPEG, PNG</p>

                        {{-- Container Preview untuk Dokumen --}}
                        <div class="preview-container hidden p-3 bg-slate-50 border border-dashed border-slate-200 rounded-xl max-w-md">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="p-1.5 bg-sky-50 text-sky-600 rounded-lg text-xs font-semibold uppercase tracking-wider label-tipe-file">File</span>
                                <span class="text-xs text-slate-600 truncate font-medium nama-file-preview">nama_file.jpg</span>
                            </div>
                            <div class="area-preview-visual flex justify-start items-center"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Total Akumulasi Keseluruhan --}}
        <div class="p-4 bg-slate-900 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-white shadow-lg shadow-slate-900/10">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pengajuan CAR:</span>
                <span class="text-xs text-slate-500 font-medium" id="label-jumlah-item">1 Macam Item</span>
            </div>
            <span id="grand_total" class="text-lg sm:text-xl font-black text-emerald-400">Rp 0</span>
        </div>

        <div class="pt-2 flex">
            <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm px-8 py-3 rounded-xl shadow-md shadow-sky-600/10 transition-colors ml-auto cursor-pointer">
                Kirim Pengajuan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- POPUP BERHASIL MENGIRIM PENGAJUAN --}}
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
    const containerItem = document.getElementById('container-item');
    const btnTambahItem = document.getElementById('btn-tambah-item');
    const grandTotalOutput = document.getElementById('grand_total');
    const labelJumlahItem = document.getElementById('label-jumlah-item');

    let itemIndex = 1;

    // POPUP KONFIRMASI SEBELUM MENGIRIM FORM
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector('form[action="{{ route("car.store") }}"]');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Pengajuan',
                    text: 'Apakah Anda yakin ingin mengirim pengajuan CAR ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'px-4 py-2 rounded-xl font-semibold',
                        cancelButton: 'px-4 py-2 rounded-xl font-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });

    // kalkulasi: (Qty * Harga Satuan) + Ongkir
    function hitungAkumulasi() {
        let akumulasiGrandTotal = 0;
        const semuaBaris = containerItem.querySelectorAll('.baris-item');

        semuaBaris.forEach(baris => {
            const inputJumlah = baris.querySelector('.input-jumlah');
            const inputHarga = baris.querySelector('.input-harga');
            const inputOngkir = baris.querySelector('.input-ongkir');
            const labelSubtotal = baris.querySelector('.label-subtotal');

            const qty = parseFloat(inputJumlah.value) || 0;
            const harga = parseFloat(inputHarga.value) || 0;
            const ongkir = parseFloat(inputOngkir ? inputOngkir.value : 0) || 0;

            const subtotal = (qty * harga) + ongkir;

            akumulasiGrandTotal += subtotal;
            labelSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        });

        grandTotalOutput.textContent = 'Rp ' + akumulasiGrandTotal.toLocaleString('id-ID');
        if (labelJumlahItem) labelJumlahItem.textContent = `${semuaBaris.length} Macam Item`;
    }

    function tanganiPreviewDokumen(inputElement) {
        const barisItem = inputElement.closest('.baris-item');
        const previewContainer = barisItem.querySelector('.preview-container');
        const labelTipeFile = barisItem.querySelector('.label-tipe-file');
        const namaFilePreview = barisItem.querySelector('.nama-file-preview');
        const areaPreviewVisual = barisItem.querySelector('.area-preview-visual');

        const file = inputElement.files[0];

        if (file) {
            namaFilePreview.textContent = file.name;
            previewContainer.classList.remove('hidden');

            if (file.type.startsWith('image/')) {
                labelTipeFile.textContent = 'Gambar';
                labelTipeFile.className = 'p-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-semibold uppercase tracking-wider label-tipe-file';
                areaPreviewVisual.innerHTML = `<img src="${URL.createObjectURL(file)}" class="max-h-40 rounded-lg border border-slate-200 shadow-inner object-contain" alt="Pratinjau Nota">`;
            } else if (file.type === 'application/pdf') {
                labelTipeFile.textContent = 'PDF';
                labelTipeFile.className = 'p-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-semibold uppercase tracking-wider label-tipe-file';
                areaPreviewVisual.innerHTML = `
                    <div class="flex items-center space-x-2 text-slate-600 bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm shadow-sm">
                        <i class="fa-solid fa-file-pdf text-rose-500 text-lg"></i>
                        <span>Dokumen PDF Siap Diunggah</span>
                    </div>
                `;
            } else {
                labelTipeFile.textContent = 'File';
                labelTipeFile.className = 'p-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold uppercase tracking-wider label-tipe-file';
                areaPreviewVisual.innerHTML = `<span class="text-xs text-slate-400">Format file tidak mendukung pratinjau visual</span>`;
            }
        } else {
            previewContainer.classList.add('hidden');
            areaPreviewVisual.innerHTML = '';
        }
    }

    // Event Listener Input Angka & Ongkir
    containerItem.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-jumlah') ||
            e.target.classList.contains('input-harga') ||
            e.target.classList.contains('input-ongkir')) {
            hitungAkumulasi();
        }
    });

    // Event Listener Upload File
    containerItem.addEventListener('change', function(e) {
        if (e.target.classList.contains('input-file-dokumen')) {
            tanganiPreviewDokumen(e.target);
        }
    });

    // Event Listener Klik Tombol + Ongkir & Hapus Baris
    containerItem.addEventListener('click', function(e) {
        const btnOngkir = e.target.closest('.btn-tambah-ongkir');
        if (btnOngkir) {
            const container = btnOngkir.closest('.container-ongkir');
            const wrapperInput = container.querySelector('.wrapper-input-ongkir');

            btnOngkir.classList.add('hidden');
            wrapperInput.classList.remove('hidden');

            const inputOngkir = wrapperInput.querySelector('.input-ongkir');
            if (inputOngkir) {
                inputOngkir.value = '';
                inputOngkir.focus();
            }
        }

        const tombolHapus = e.target.closest('.btn-hapus-item');
        if (tombolHapus && !tombolHapus.disabled) {
            const baris = tombolHapus.closest('.baris-item');
            baris.remove();
            hitungAkumulasi();
        }
    });

    // Listener Tambah Item Baru Dinamis
    btnTambahItem.addEventListener('click', function() {
        const barisBaru = document.createElement('div');
        barisBaru.className = "baris-item bg-white p-4 rounded-xl border border-slate-200 relative shadow-sm space-y-4 transition-all animate-fadeIn";

        barisBaru.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                <div class="sm:col-span-2 md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Barang / Material</label>
                    <input type="text" name="items[${itemIndex}][nama_barang]" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Contoh: Pipa PVC 2 Inch">
                </div>

                <div class="grid grid-cols-12 gap-2 sm:col-span-2 md:col-span-7">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qty</label>
                        <input type="number" name="items[${itemIndex}][jumlah]" required min="1" class="input-jumlah w-full px-2 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500 text-center" placeholder="Qty">
                    </div>

                    <div class="col-span-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Satuan</label>
                        <select name="items[${itemIndex}][satuan]" required class="w-full px-2 py-2 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:border-sky-500 cursor-pointer">
                            <option value="" disabled selected hidden>Pilih</option>
                            <option value="PCS">PCS</option>
                            <option value="Unit">Unit</option>
                            <option value="BOX">BOX</option>
                            <option value="Meter">Meter</option>
                            <option value="Batang">Batang</option>
                            <option value="Set">Set</option>
                        </select>
                    </div>

                    <div class="col-span-4">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Harga Satuan (Rp)</label>
                        <input type="number" name="items[${itemIndex}][estimasi_harga]" required min="0" class="input-harga w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Harga">
                    </div>

                    <div class="col-span-3 flex flex-col justify-end container-ongkir">
                        <button type="button" class="btn-tambah-ongkir h-[38px] px-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs rounded-lg border border-amber-200 transition-colors flex items-center justify-center gap-1 cursor-pointer w-full">
                            + <i class="fa-solid fa-truck-fast"></i> Ongkir
                        </button>
                        <div class="wrapper-input-ongkir hidden">
                            <label class="block text-[10px] font-semibold text-amber-600 mb-1">Ongkir (Rp)</label>
                            <input type="number" name="items[${itemIndex}][ongkir]" min="0" value="" class="input-ongkir w-full px-2 py-1.5 rounded-lg border border-amber-300 text-sm focus:outline-none focus:border-amber-500 bg-amber-50/30" placeholder="Ongkir">
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t border-slate-50 md:border-none">
                    <div class="text-left md:text-right">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Subtotal</span>
                        <span class="text-sm font-bold text-slate-700 label-subtotal">Rp 0</span>
                    </div>
                    <button type="button" class="btn-hapus-item text-rose-500 hover:text-rose-700 transition-colors p-2 rounded-lg md:mt-4 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-base"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-2">
                <label class="block text-xs font-semibold text-slate-600">Upload Nota / Foto Barang</label>

                <input type="file"
                    name="items[${itemIndex}][dokumen_pendukung]"
                    required
                    class="input-file-dokumen w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-600 text-xs focus:outline-none focus:border-sky-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">

                <p class="text-[10px] text-slate-400 mt-1">* Format: PDF, JPG, JPEG, PNG</p>

                <div class="preview-container hidden p-3 bg-slate-50 border border-dashed border-slate-200 rounded-xl max-w-md">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="p-1.5 bg-sky-50 text-sky-600 rounded-lg text-xs font-semibold uppercase tracking-wider label-tipe-file">File</span>
                        <span class="text-xs text-slate-600 truncate font-medium nama-file-preview">nama_file.jpg</span>
                    </div>
                    <div class="area-preview-visual flex justify-start items-center"></div>
                </div>
            </div>
        `;

        containerItem.appendChild(barisBaru);
        itemIndex++;
        hitungAkumulasi();
    });
</script>
@endpush

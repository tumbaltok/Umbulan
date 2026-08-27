@extends('layouts.app')
@section('title', 'Edit Pengajuan CAR')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 max-w-5xl mx-auto m-2 sm:m-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-sky-50 p-3 rounded-xl text-sky-600">
            <i class="fa-solid fa-pen-to-square text-xl"></i>
        </div>
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800">Edit Pengajuan CAR</h2>
            <p class="text-xs text-slate-400">Ubah formulir pengajuan dana muka operasional: {{ $car->nomor_car ?? sprintf('%03d', $car->id) }}</p>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formCar" action="{{ route('car.update', $car->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: Header Dokumen & Akun Pencairan --}}
        <div class="bg-slate-50/50 p-4 sm:p-5 rounded-2xl border border-slate-100 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-file-contract text-sky-500"></i> Metadata Dokumen & Akun Pencairan
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {{-- Nomor CAR (Readonly) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor CAR (Readonly)</label>
                    <input type="text" name="nomor_car" value="{{ $car->nomor_car }}" readonly
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/70 text-slate-600 text-sm font-semibold cursor-not-allowed focus:outline-none">
                </div>

                {{-- Tanggal Pengajuan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Pengajuan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pengajuan" value="{{ old('tanggal_pengajuan', $car->tanggal_pengajuan ? $car->tanggal_pengajuan->format('Y-m-d') : date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer">
                </div>

                {{-- Rekening Penerima Dana (Receiving Account) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Rekening Penerima Dana <span class="text-rose-500">*</span></label>
                    <input type="text" name="receiving_account" value="{{ old('receiving_account', $car->receiving_account) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
                           placeholder="Contoh: BCA 1234567890 a.n. Nama Karyawan">
                    <p class="text-[10px] text-slate-400 mt-1">Format: Nama Bank - No Rekening - Atas Nama Penerima</p>
                </div>
            </div>

            {{-- Alasan Pembelian & Catatan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Pembelian / Urgensi Operasional <span class="text-rose-500">*</span></label>
                    <textarea name="alasan_pembelian" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm leading-relaxed" placeholder="Jelaskan kebutuhan pengajuan dana muka operasional secara lengkap...">{{ old('alasan_pembelian', $car->alasan_pembelian) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Note & Explanation (Opsional)</label>
                    <textarea name="note_explanation" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm leading-relaxed" placeholder="Catatan tambahan teknis atau keterangan pelaporan...">{{ old('note_explanation', $car->note_explanation) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: Rincian Barang / Multi-Item --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Requested Items & Ongkos Kirim per Vendor
                    </h3>
                    <p class="text-xs text-slate-400">Rincian barang, harga satuan, ongkir spesifik toko/vendor, dan upload nota/proposal</p>
                </div>
                <button type="button" id="btn-tambah-item" class="w-full sm:w-auto bg-sky-50 hover:bg-sky-100 text-sky-600 font-semibold text-xs px-3.5 py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-plus"></i> Tambah Item
                </button>
            </div>

            {{-- Container Baris Item --}}
            <div id="container-item" class="space-y-4">
                @foreach($car->details as $index => $detail)
                <div class="baris-item bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 relative shadow-xs space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5">
                        {{-- Nama Barang --}}
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Description / Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[{{ $index }}][nama_barang]" value="{{ $detail->nama_barang }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Nama barang">
                        </div>

                        {{-- Qty, Satuan, Harga, Ongkir --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:col-span-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Qty <span class="text-rose-500">*</span></label>
                                <input type="number" name="items[{{ $index }}][jumlah]" value="{{ $detail->jumlah }}" required min="1" class="input-jumlah w-full px-2 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center font-bold" placeholder="1">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="items[{{ $index }}][satuan]" value="{{ $detail->satuan }}" required list="satuan-list" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center" placeholder="Pcs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="items[{{ $index }}][estimasi_harga]" value="{{ (int) $detail->estimasi_harga }}" required min="0" class="input-harga w-full px-2 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-amber-700 mb-1"><i class="fa-solid fa-truck-fast"></i> Ongkir (Rp)</label>
                                <input type="number" name="items[{{ $index }}][ongkir]" value="{{ (int) ($detail->ongkir ?? 0) }}" min="0" class="input-ongkir w-full px-2 py-2 rounded-xl border border-amber-300 bg-amber-50/20 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="0">
                            </div>
                        </div>

                        {{-- Subtotal & Tombol Hapus --}}
                        <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-2.5 pt-2 md:pt-0 border-t border-slate-50 md:border-none">
                            <div class="text-left md:text-right">
                                <span class="block text-[10px] text-slate-400 uppercase font-bold">Subtotal Baris</span>
                                <span class="text-sm font-bold text-slate-700 label-subtotal">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</span>
                            </div>
                            <button type="button" class="btn-hapus-item {{ $car->details->count() <= 1 ? 'text-slate-300 cursor-not-allowed' : 'text-rose-500 hover:text-rose-700 cursor-pointer' }} p-2 rounded-lg md:mt-4 transition-colors" {{ $car->details->count() <= 1 ? 'disabled' : '' }}>
                                <i class="fa-solid fa-trash-can text-base"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Upload Dokumen Pendukung Khusus Item Ini --}}
                    <div class="border-t border-slate-100 pt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="w-full sm:w-2/3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Unggah Nota Baru / Pengganti (Opsional)</label>
                            <input type="file" name="items[{{ $index }}][dokumen_pendukung]"
                                   class="input-file-dokumen w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                            @if($detail->dokumen_nota_or_proposal)
                                <input type="hidden" name="items[{{ $index }}][existing_dokumen]" value="{{ $detail->dokumen_nota_or_proposal }}">
                                <p class="text-[11px] text-emerald-600 mt-1 font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-check"></i> File lama tersimpan: {{ basename($detail->dokumen_nota_or_proposal) }}
                                </p>
                            @endif
                        </div>

                        {{-- Container Preview --}}
                        <div class="preview-container hidden p-2.5 bg-slate-50 border border-dashed border-slate-200 rounded-xl w-full sm:w-1/3">
                            <div class="flex items-center space-x-2 mb-1.5">
                                <span class="p-1 bg-sky-50 text-sky-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file">File</span>
                                <span class="text-[11px] text-slate-600 truncate font-medium nama-file-preview">nama_file.jpg</span>
                            </div>
                            <div class="area-preview-visual flex justify-start items-center"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Datalist Satuan Standar --}}
            <datalist id="satuan-list">
                <option value="PCS">
                <option value="Unit">
                <option value="Box">
                <option value="Lot">
                <option value="Meter">
                <option value="Batang">
                <option value="Set">
                <option value="Roll">
                <option value="Kg">
                <option value="Liter">
                <option value="Tabung">
            </datalist>
        </div>

        {{-- Section 3: Ringkasan Total Akumulasi (Grand Total) --}}
        <div class="p-5 bg-slate-900 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-white shadow-lg shadow-slate-900/10">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Estimasi Grand Total CAR:</span>
                <span class="text-xs text-slate-400 font-medium" id="label-jumlah-item">{{ $car->details->count() }} Macam Item</span>
            </div>
            <div class="text-left sm:text-right">
                <span id="grand_total" class="text-xl sm:text-2xl font-black text-emerald-400">Rp 0</span>
                <span class="block text-[11px] text-slate-400 font-medium" id="label-rincian-total">Barang: Rp 0 + Total Ongkir: Rp 0</span>
            </div>
        </div>

        {{-- Tombol Batal & Simpan --}}
        <div class="pt-2 flex justify-between items-center">
            <a href="{{ route('car.riwayat') }}" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-100 transition-colors">
                Batal
            </a>
            <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm px-8 py-3 rounded-xl shadow-md shadow-sky-600/10 transition-colors cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan CAR
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const containerItem = document.getElementById('container-item');
    const btnTambahItem = document.getElementById('btn-tambah-item');
    const grandTotalOutput = document.getElementById('grand_total');
    const labelJumlahItem = document.getElementById('label-jumlah-item');
    const labelRincianTotal = document.getElementById('label-rincian-total');

    let itemIndex = {{ $car->details->count() }};
    let isConfirmed = false;

    document.addEventListener("DOMContentLoaded", function () {
        hitungAkumulasi();

        const form = document.getElementById('formCar');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!isConfirmed) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Simpan Perubahan CAR',
                        text: 'Apakah Anda yakin ingin memperbarui data pengajuan CAR ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Simpan',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'px-4 py-2 rounded-xl font-semibold',
                            cancelButton: 'px-4 py-2 rounded-xl font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            isConfirmed = true;
                            form.submit();
                        }
                    });
                }
            });
        }

        containerItem.querySelectorAll('.input-file-dokumen').forEach(setupPreviewListener);
    });

    function setupPreviewListener(inputElement) {
        inputElement.addEventListener('change', function () {
            const baris = this.closest('.baris-item');
            const previewContainer = baris.querySelector('.preview-container');
            const labelTipe = baris.querySelector('.label-tipe-file');
            const namaFile = baris.querySelector('.nama-file-preview');
            const areaVisual = baris.querySelector('.area-preview-visual');

            const file = this.files[0];
            if (file) {
                namaFile.textContent = file.name;
                previewContainer.classList.remove('hidden');

                if (file.type.startsWith('image/')) {
                    labelTipe.textContent = 'Gambar';
                    labelTipe.className = 'p-1 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                    areaVisual.innerHTML = `<img src="${URL.createObjectURL(file)}" class="max-h-24 rounded-lg border border-slate-200 object-contain" alt="Pratinjau Nota">`;
                } else if (file.type === 'application/pdf') {
                    labelTipe.textContent = 'PDF';
                    labelTipe.className = 'p-1 bg-rose-50 text-rose-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                    areaVisual.innerHTML = `<div class="flex items-center gap-1.5 text-xs text-slate-600"><i class="fa-solid fa-file-pdf text-rose-500"></i> PDF Siap Diunggah</div>`;
                } else {
                    labelTipe.textContent = 'File';
                    labelTipe.className = 'p-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file';
                    areaVisual.innerHTML = `<span class="text-[10px] text-slate-400">Berkas Terlampir</span>`;
                }
            } else {
                previewContainer.classList.add('hidden');
                areaVisual.innerHTML = '';
            }
        });
    }

    function hitungAkumulasi() {
        let totalHargaBarang = 0;
        let totalOngkir = 0;
        let grandTotal = 0;
        const semuaBaris = containerItem.querySelectorAll('.baris-item');

        semuaBaris.forEach(baris => {
            const inputJumlah = baris.querySelector('.input-jumlah');
            const inputHarga = baris.querySelector('.input-harga');
            const inputOngkir = baris.querySelector('.input-ongkir');
            const labelSubtotal = baris.querySelector('.label-subtotal');

            const qty = parseFloat(inputJumlah.value) || 0;
            const harga = parseFloat(inputHarga.value) || 0;
            const ongkir = parseFloat(inputOngkir ? inputOngkir.value : 0) || 0;

            const hargaBarang = qty * harga;
            const subtotal = hargaBarang + ongkir;

            totalHargaBarang += hargaBarang;
            totalOngkir += ongkir;
            grandTotal += subtotal;

            labelSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        });

        grandTotalOutput.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        if (labelJumlahItem) labelJumlahItem.textContent = `${semuaBaris.length} Macam Item`;
        if (labelRincianTotal) {
            labelRincianTotal.textContent = `Barang: Rp ${totalHargaBarang.toLocaleString('id-ID')} + Total Ongkir: Rp ${totalOngkir.toLocaleString('id-ID')}`;
        }

        // Atur status tombol hapus
        semuaBaris.forEach(baris => {
            const btn = baris.querySelector('.btn-hapus-item');
            if (semuaBaris.length === 1) {
                btn.disabled = true;
                btn.className = "btn-hapus-item text-slate-300 cursor-not-allowed p-2 rounded-lg md:mt-4";
            } else {
                btn.disabled = false;
                btn.className = "btn-hapus-item text-rose-500 hover:text-rose-700 transition-colors p-2 rounded-lg md:mt-4 cursor-pointer";
            }
        });
    }

    containerItem.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-jumlah') || e.target.classList.contains('input-harga') || e.target.classList.contains('input-ongkir')) {
            hitungAkumulasi();
        }
    });

    btnTambahItem.addEventListener('click', function() {
        const barisBaru = document.createElement('div');
        barisBaru.className = "baris-item bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 relative shadow-xs space-y-3 transition-all";

        barisBaru.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5">
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description / Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="items[${itemIndex}][nama_barang]" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Nama barang">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:col-span-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Qty <span class="text-rose-500">*</span></label>
                        <input type="number" name="items[${itemIndex}][jumlah]" required min="1" class="input-jumlah w-full px-2 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center font-bold" placeholder="1">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="items[${itemIndex}][satuan]" required list="satuan-list" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center" placeholder="Pcs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="items[${itemIndex}][estimasi_harga]" required min="0" class="input-harga w-full px-2 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-amber-700 mb-1"><i class="fa-solid fa-truck-fast"></i> Ongkir (Rp)</label>
                        <input type="number" name="items[${itemIndex}][ongkir]" min="0" value="0" class="input-ongkir w-full px-2 py-2 rounded-xl border border-amber-300 bg-amber-50/20 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" placeholder="0">
                    </div>
                </div>

                <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-2.5 pt-2 md:pt-0 border-t border-slate-50 md:border-none">
                    <div class="text-left md:text-right">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Subtotal Baris</span>
                        <span class="text-sm font-bold text-slate-700 label-subtotal">Rp 0</span>
                    </div>
                    <button type="button" class="btn-hapus-item text-rose-500 hover:text-rose-700 transition-colors p-2 rounded-lg md:mt-4 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-base"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="w-full sm:w-2/3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Lampiran Nota / Proposal / Foto Barang (Opsional)</label>
                    <input type="file" name="items[${itemIndex}][dokumen_pendukung]"
                           class="input-file-dokumen w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-0.5">* Format: PDF, JPG, JPEG, PNG (Maks 2MB)</p>
                </div>

                <div class="preview-container hidden p-2.5 bg-slate-50 border border-dashed border-slate-200 rounded-xl w-full sm:w-1/3">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="p-1 bg-sky-50 text-sky-600 rounded-md text-[10px] font-semibold uppercase tracking-wider label-tipe-file">File</span>
                        <span class="text-[11px] text-slate-600 truncate font-medium nama-file-preview">nama_file.jpg</span>
                    </div>
                    <div class="area-preview-visual flex justify-start items-center"></div>
                </div>
            </div>
        `;

        containerItem.appendChild(barisBaru);
        setupPreviewListener(barisBaru.querySelector('.input-file-dokumen'));
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

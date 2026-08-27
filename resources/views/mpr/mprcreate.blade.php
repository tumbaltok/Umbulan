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
            <p class="text-xs text-slate-400">Material/Service Procurement Request (Pengadaan Material & Jasa)</p>
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

    <form id="formMpr" action="{{ route('mpr.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Header & Metadata Dokumen MPR --}}
        <div class="bg-slate-50/50 p-4 sm:p-5 rounded-2xl border border-slate-100 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-sky-500"></i> Metadata Dokumen MPR
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {{-- Nomor MPR (Readonly) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor MPR (Otomatis)</label>
                    <input type="text" name="nomor_mpr" value="{{ $nomorMpr }}" readonly
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/70 text-slate-600 text-sm font-semibold cursor-not-allowed focus:outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Format resmi: [No] / META / PAS / MPR / [Bulan] / [Tahun]</p>
                </div>

                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Priority / Urgensi <span class="text-rose-500">*</span></label>
                    <select name="priority" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer">
                        <option value="Normal" {{ old('priority', 'Normal') == 'Normal' ? 'selected' : '' }}>Normal</option>
                        <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Emergency" {{ old('priority') == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>

                {{-- Department --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Department <span class="text-rose-500">*</span></label>
                    <input type="text" name="department" value="{{ old('department', $defaultDepartment) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Contoh: Operation">
                </div>

                {{-- Delivery Point --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Point <span class="text-rose-500">*</span></label>
                    <input type="text" name="delivery_point" value="{{ old('delivery_point', $defaultDeliveryPoint) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Contoh: Site Umbulan">
                </div>

                {{-- Latest MPR Issued Date (Opsional) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Latest MPR Issued Date (Opsional)</label>
                    <input type="date" name="latest_mpr_date" value="{{ old('latest_mpr_date') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Tanggal pengajuan MPR sebelumnya jika ada</p>
                </div>

                {{-- Tanggal Hari Ini (Readonly) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Pengajuan</label>
                    <input type="text" value="{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}" readonly
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100/70 text-slate-600 text-sm cursor-not-allowed">
                </div>
            </div>
        </div>

        {{-- Section 2: Note & Explanation & Dokumen Pendukung --}}
        <div class="bg-slate-50/50 p-4 sm:p-5 rounded-2xl border border-slate-100 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-comment-dots text-sky-500"></i> Note & Explanation (Keperluan / Urgensi)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Rincian Catatan / Urgensi Lapangan <span class="text-rose-500">*</span></label>
                    <textarea name="keperluan_urgensi" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm leading-relaxed" placeholder="- Mohon izin mengajukan pengadaan material untuk keperluan operasional&#10;- Kondisi di ruang instalasi hari ini : sisa stok kritis&#10;- Mohon segera diproses...">{{ old('keperluan_urgensi') }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan tanda strip (-) di setiap baris untuk otomatis diformat sebagai butir catatan resmi pada PDF.</p>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dokumen Pendukung Utama (Opsional)</label>
                    <input type="file" id="dokumen_pendukung" name="dokumen_pendukung" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">* Format: PDF, JPG, JPEG, PNG (Maks 2MB)</p>

                    {{-- Container Preview untuk Dokumen --}}
                    <div id="preview-container" class="preview-container hidden mt-2 p-3 bg-slate-50 border border-dashed border-slate-200 rounded-xl max-w-full">
                        <div class="flex items-center space-x-3 mb-2">
                            <span id="label-tipe-file" class="p-1.5 bg-sky-50 text-sky-600 rounded-lg text-xs font-semibold uppercase tracking-wider">File</span>
                            <span id="nama-file-preview" class="text-xs text-slate-600 truncate font-medium">nama_file.jpg</span>
                        </div>
                        <div id="area-preview-visual" class="area-preview-visual flex justify-start items-center"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Rincian Material / Jasa (Requested Material/Service) --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-sky-500"></i> Requested Material / Service
                    </h3>
                    <p class="text-xs text-slate-400">Rincian barang/jasa, spesifikasi teknis, part number, dan kuantitas</p>
                </div>
                <button type="button" id="btn-tambah-item" class="w-full sm:w-auto bg-sky-50 hover:bg-sky-100 text-sky-600 font-semibold text-xs px-3.5 py-2 rounded-xl transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-plus"></i> Tambah Item
                </button>
            </div>

            {{-- Container Baris Item --}}
            <div id="container-item" class="space-y-4">
                {{-- Baris Pertama (Default Row) --}}
                <div class="baris-item bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 relative shadow-xs space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        {{-- Nama Item / Barang --}}
                        <div class="md:col-span-5">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Item / Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="items[0][nama_barang]" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Contoh: Refill tabung klorin site umbulan">
                        </div>

                        {{-- Quantity & Satuan & Est. Harga --}}
                        <div class="grid grid-cols-3 gap-2 md:col-span-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity <span class="text-rose-500">*</span></label>
                                <input type="number" name="items[0][jumlah]" required min="1" class="input-jumlah w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center font-bold" placeholder="1">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                                <input type="text" name="items[0][satuan]" required list="satuan-list" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center" placeholder="Tabung">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Est. Harga (Rp)</label>
                                <input type="number" name="items[0][estimasi_harga]" min="0" class="input-harga w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="0">
                            </div>
                        </div>

                        {{-- Subtotal & Tombol Hapus --}}
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

                    {{-- Description / Specification / Part number --}}
                    <div class="border-t border-slate-100 pt-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Description / Specification / Part number</label>
                        <input type="text" name="items[0][keterangan_item]" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Contoh: Klorin Inti, berat tabung @900 kg / P/N: CL-900">
                    </div>
                </div>
            </div>

            {{-- Datalist Satuan Umum --}}
            <datalist id="satuan-list">
                <option value="Tabung">
                <option value="Pcs">
                <option value="Unit">
                <option value="Box">
                <option value="Kg">
                <option value="Liter">
                <option value="Meter">
                <option value="Set">
                <option value="Lot">
                <option value="Batang">
                <option value="Roll">
            </datalist>
        </div>

        {{-- Ringkasan Total Akumulasi --}}
        <div class="p-4 bg-slate-900 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-white shadow-lg shadow-slate-900/10">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Estimasi Grand Total MPR:</span>
                <span class="text-xs text-slate-400 font-medium" id="label-jumlah-item">1 Macam Item</span>
            </div>
            <span id="grand_total" class="text-lg sm:text-xl font-black text-emerald-400">Rp 0</span>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="submit" class="w-full sm:w-auto bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm px-8 py-3 rounded-xl shadow-md shadow-sky-600/10 transition-colors cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan MPR
            </button>
        </div>
    </form>
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
    const containerItem = document.getElementById('container-item');
    const btnTambahItem = document.getElementById('btn-tambah-item');
    const grandTotalOutput = document.getElementById('grand_total');
    const labelJumlahItem = document.getElementById('label-jumlah-item');

    let itemIndex = 1;
    let isConfirmed = false;

    // HANDLER SUBMIT DENGAN KONFIRMASI
    document.addEventListener("DOMContentLoaded", function () {
        hitungAkumulasi();

        const form = document.getElementById('formMpr');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!isConfirmed) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Pengajuan MPR',
                        text: 'Apakah Anda yakin data barang dan urgensi MPR sudah sesuai?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Kirim Sekarang',
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

        // HANDLER PREVIEW DOKUMEN PENDUKUNG
        const inputDokumen = document.getElementById('dokumen_pendukung');
        const previewContainer = document.getElementById('preview-container');
        const labelTipeFile = document.getElementById('label-tipe-file');
        const namaFilePreview = document.getElementById('nama-file-preview');
        const areaPreviewVisual = document.getElementById('area-preview-visual');

        if (inputDokumen) {
            inputDokumen.addEventListener('change', function () {
                const file = this.files[0];

                if (file) {
                    namaFilePreview.textContent = file.name;
                    previewContainer.classList.remove('hidden');

                    if (file.type.startsWith('image/')) {
                        labelTipeFile.textContent = 'Gambar';
                        labelTipeFile.className = 'p-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-semibold uppercase tracking-wider';
                        areaPreviewVisual.innerHTML = `<img src="${URL.createObjectURL(file)}" class="max-h-40 rounded-lg border border-slate-200 shadow-inner object-contain" alt="Pratinjau Dokumen">`;
                    } else if (file.type === 'application/pdf') {
                        labelTipeFile.textContent = 'PDF';
                        labelTipeFile.className = 'p-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-semibold uppercase tracking-wider';
                        areaPreviewVisual.innerHTML = `
                            <div class="flex items-center space-x-2 text-slate-600 bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm shadow-sm">
                                <i class="fa-solid fa-file-pdf text-rose-500 text-lg"></i>
                                <span>Dokumen PDF Siap Diunggah</span>
                            </div>
                        `;
                    } else {
                        labelTipeFile.textContent = 'File';
                        labelTipeFile.className = 'p-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold uppercase tracking-wider';
                        areaPreviewVisual.innerHTML = `<span class="text-xs text-slate-400">Format file tidak mendukung pratinjau visual</span>`;
                    }
                } else {
                    previewContainer.classList.add('hidden');
                    areaPreviewVisual.innerHTML = '';
                }
            });
        }
    });

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
        if (labelJumlahItem) labelJumlahItem.textContent = `${semuaBaris.length} Macam Item`;

        // Atur status tombol hapus jika hanya 1 baris
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
        if (e.target.classList.contains('input-jumlah') || e.target.classList.contains('input-harga')) {
            hitungAkumulasi();
        }
    });

    btnTambahItem.addEventListener('click', function() {
        const barisBaru = document.createElement('div');
        barisBaru.className = "baris-item bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 relative shadow-xs space-y-3 transition-all";

        barisBaru.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Item / Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="items[${itemIndex}][nama_barang]" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Nama barang">
                </div>

                <div class="grid grid-cols-3 gap-2 md:col-span-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity <span class="text-rose-500">*</span></label>
                        <input type="number" name="items[${itemIndex}][jumlah]" required min="1" class="input-jumlah w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center font-bold" placeholder="1">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="items[${itemIndex}][satuan]" required list="satuan-list" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-center" placeholder="Pcs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Est. Harga (Rp)</label>
                        <input type="number" name="items[${itemIndex}][estimasi_harga]" min="0" class="input-harga w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="0">
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

            <div class="border-t border-slate-100 pt-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Description / Specification / Part number</label>
                <input type="text" name="items[${itemIndex}][keterangan_item]" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500" placeholder="Spesifikasi / Merek / Part number...">
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

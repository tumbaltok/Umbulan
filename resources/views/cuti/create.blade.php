@extends('layouts.app')
@section('title', 'Pengajuan Cuti')
@section('content')
<div class="max-w-3xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Formulir Pengajuan Cuti</h2>
            <p class="text-sm text-slate-500 mt-0.5">Silakan isi data di bawah ini untuk mengajukan permohonan cuti resmi</p>
        </div>

        <div class="p-6">
            @if($errors->has('error'))
                <div class="mb-4 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-sm font-medium">
                    {{ $errors->first('error') }}
                </div>
            @endif

            {{-- ELEMEN PENAMPUNG PESAN ERROR SALDO --}}
            <div id="pesan-error-saldo" class="mb-4 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-sm font-medium" style="display: none;"></div>

            <form action="{{ route('cuti.storeWeb') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- JENIS CUTI UTAMA --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Cuti</label>
                    <select name="jenis_cuti_id"
                        id="jenis_cuti_id"
                        class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('jenis_cuti_id') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}"
                        required>
                        <option value="" disabled selected hidden>-- Pilih Jenis Cuti --</option>
                        @foreach($jenisCuti as $jenis)
                            @php
                                $userGender = strtolower(auth()->user()->gender->name ?? auth()->user()->gender ?? '');
                                $isPria = ($userGender === 'pria' || $userGender === 'male' || $userGender === '1');
                                $namaCutiLower = strtolower($jenis->name_cuti);
                            @endphp

                            @if($isPria && (str_contains($namaCutiLower, 'melahirkan') || str_contains($namaCutiLower, 'haid') || str_contains($namaCutiLower, 'bersalin')))
                                @continue
                            @endif

                            <option value="{{ $jenis->id }}" data-nama-cuti="{{ $jenis->name_cuti }}" {{ old('jenis_cuti_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->name_cuti }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_cuti_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- SUB-CUTI / DETAIL PILIHAN --}}
                <div id="wrapper_sub_cuti" style="display: none;">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Detail Keperluan / Sub-Cuti</label>
                    <select id="sub_cuti_id"
                            name="sub_cuti_id"
                            class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 bg-sky-50/20 {{ $errors->has('sub_cuti_id') ? 'border-rose-500' : 'border-slate-200' }}">
                        <option value="" disabled selected hidden>-- Pilih Detail Perizinan --</option>
                    </select>
                    @error('sub_cuti_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- GRID TANGGAL MULAI & SELESAI --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date"
                               name="tanggal_mulai"
                               id="tanggal_mulai"
                               min="{{ date('Y-m-d') }}"
                               value="{{ old('tanggal_mulai') }}"
                               class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('tanggal_mulai') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}"
                               required>
                        @error('tanggal_mulai') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date"
                               name="tanggal_selesai"
                               id="tanggal_selesai"
                               min="{{ date('Y-m-d') }}"
                               value="{{ old('tanggal_selesai') }}"
                               class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('tanggal_selesai') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}"
                               required>
                        @error('tanggal_selesai') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- ALASAN CUTI --}}
                <div>
                    <label id="label-alasan" class="block text-sm font-semibold text-slate-700 mb-2">
                        Alasan / Catatan Tambahan <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <textarea name="alasan_cuti"
                            id="alasan_cuti"
                            rows="3"
                            class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('alasan_cuti') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}"
                            placeholder="Contoh: Menikahkan anak pertama di gedung serbaguna kota...">{{ old('alasan_cuti') }}</textarea>
                    @error('alasan_cuti') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- DOKUMEN PENDUKUNG --}}
                <div>
                    <label id="label-dokumen" class="block text-sm font-semibold text-slate-700 mb-2">Dokumen Pendukung <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                    <input type="file"
                           name="dokumen_pendukung"
                           id="input-dokumen"
                           class="w-full px-3 py-2 border rounded-xl bg-slate-50 text-slate-600 text-sm focus:outline-none focus:border-sky-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 {{ $errors->has('dokumen_pendukung') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}">
                    <p class="text-xs text-slate-400 mt-1.5">* Format yang didukung: PDF, JPG, JPEG, PNG (Maksimal 2MB)</p>
                    @error('dokumen_pendukung') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="pt-3 border-t border-slate-100 flex justify-end space-x-3">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" id="btn-submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow-sm shadow-sky-100 transition-colors">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataJenisCuti = @json($jenisCuti);

        const oldJenisCutiId = "{{ old('jenis_cuti_id') }}";
        const oldSubCutiId = "{{ old('sub_cuti_id') }}";

        const userGender = "{{ strtolower(auth()->user()->gender->name ?? auth()->user()->gender ?? '') }}";
        const isPria = (userGender === 'pria' || userGender === 'male' || userGender === '1');

        const jenisCutiSelect = document.getElementById('jenis_cuti_id');
        const wrapperSubCuti = document.getElementById('wrapper_sub_cuti');
        const subCutiSelect = document.getElementById('sub_cuti_id');
        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalSelesai = document.getElementById('tanggal_selesai');
        const labelAlasan = document.getElementById('label-alasan');
        const alasanCuti = document.getElementById('alasan_cuti');

        const sisaSaldoCutiTahunan = {{ $sisaSaldo ?? 0 }};

        const tombolSubmit = document.getElementById('btn-submit');
        const pesanErrorSaldo = document.getElementById('pesan-error-saldo');

        function periksaSaldo() {
            const idTerpilih = parseInt(jenisCutiSelect.value);

            if (idTerpilih === 4 && sisaSaldoCutiTahunan <= 0) {
                pesanErrorSaldo.textContent = 'Ditolak! Sisa kuota Cuti Tahunan Anda sudah habis (0 hari).';
                pesanErrorSaldo.style.display = 'block';
                tombolSubmit.disabled = true;
                tombolSubmit.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                pesanErrorSaldo.style.display = 'none';
                tombolSubmit.disabled = false;
                tombolSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        tanggalMulai.addEventListener('mousedown', function(e) {
            if (typeof this.showPicker === 'function') {
                e.preventDefault();
                this.showPicker();
            }
        });
        tanggalSelesai.addEventListener('mousedown', function(e) {
            if (typeof this.showPicker === 'function') {
                e.preventDefault();
                this.showPicker();
            }
        });

        function handleJenisCutiChange(selectedId, isInitialLoad = false) {
            const optionTerpilih = jenisCutiSelect.options[jenisCutiSelect.selectedIndex];
            const namaCuti = optionTerpilih ? optionTerpilih.getAttribute('data-nama-cuti') : '';

            if (namaCuti === 'Cuti') {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-rose-600">*</span>';
                alasanCuti.setAttribute('required', 'required');
            } else {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-xs font-normal text-slate-400">(Opsional)</span>';
                alasanCuti.removeAttribute('required');
            }

            subCutiSelect.innerHTML = '<option value="" disabled selected hidden>-- Pilih Detail Perizinan --</option>';
            const jenisTerpilih = dataJenisCuti.find(item => item.id == selectedId);

            if (jenisTerpilih) {
                let subCutis = jenisTerpilih.sub_cutis || jenisTerpilih.subCutis || [];

                if (isPria) {
                    subCutis = subCutis.filter(sub => {
                        const namaLower = sub.nama_sub_cuti.toLowerCase();
                        return !namaLower.includes('haid') && !namaLower.includes('melahirkan') && !namaLower.includes('bersalin');
                    });
                }

                if (subCutis.length > 0) {
                    wrapperSubCuti.style.display = 'block';
                    subCutiSelect.setAttribute('required', 'required');

                    subCutis.forEach(function (sub) {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = `${sub.nama_sub_cuti}`;
                        option.setAttribute('data-nama-sub', sub.nama_sub_cuti);
                        option.setAttribute('data-durasi', sub.durasi_default || '');
                        option.setAttribute('data-wajib-dokumen', sub.apakah_wajib_dokumen ? '1' : '0');

                        if (isInitialLoad && oldSubCutiId == sub.id) {
                            option.setAttribute('selected', 'selected');
                        }

                        subCutiSelect.appendChild(option);
                    });

                    checkDokumenRequirement();
                    // Jalankan pembatasan tanggal setelah sub-cuti berhasil dimuat
                    batasiKalenderSelesai();
                    return;
                }
            }

            wrapperSubCuti.style.display = 'none';
            subCutiSelect.removeAttribute('required');
            subCutiSelect.value = '';
            resetStatusDokumen();
            tanggalSelesai.removeAttribute('max');
        }

        function checkDokumenRequirement() {
            const optionTerpilih = subCutiSelect.options[subCutiSelect.selectedIndex];

            if (optionTerpilih && optionTerpilih.value !== "") {
                const namaSubCuti = (optionTerpilih.getAttribute('data-nama-sub') || optionTerpilih.textContent).toLowerCase().trim();
                const valWajib = optionTerpilih.getAttribute('data-wajib-dokumen');

                if (namaSubCuti.includes('sakit') || valWajib === '1' || valWajib === 'true') {
                    document.getElementById('label-dokumen').innerHTML = 'Dokumen Pendukung <span class="text-rose-600">*</span>';
                    document.getElementById('input-dokumen').required = true;
                } else {
                    resetStatusDokumen();
                }
            } else {
                resetStatusDokumen();
            }
        }

        // ==========================================
        // FUNGSI UTAMA UNTUK MEMBATASI KALENDER
        // ==========================================
        function batasiKalenderSelesai() {
            const selectedOption = subCutiSelect.options[subCutiSelect.selectedIndex];
            if (!selectedOption || selectedOption.value === "") {
                tanggalSelesai.removeAttribute('max');
                return;
            }

            const durasi = selectedOption.getAttribute('data-durasi');

            // Set batas minimal tanggal selesai agar tidak bisa kurang dari tanggal mulai
            if (tanggalMulai.value) {
                tanggalSelesai.min = tanggalMulai.value;
            }

            // Jika durasi kosong atau tidak terdefinisi berarti "Sakit" / Tidak terbatas
            if (!durasi || durasi === '') {
                tanggalSelesai.removeAttribute('max');
            } else {
                if (tanggalMulai.value) {
                    const maxDays = parseInt(durasi);
                    let dateMulai = new Date(tanggalMulai.value);

                    // Rumus batas maksimal kalender: Tanggal Mulai + (Durasi - 1)
                    dateMulai.setDate(dateMulai.getDate() + (maxDays - 1));

                    const yyyy = dateMulai.getFullYear();
                    const mm = String(dateMulai.getMonth() + 1).padStart(2, '0');
                    const dd = String(dateMulai.getDate()).padStart(2, '0');
                    const maxDateString = `${yyyy}-${mm}-${dd}`;

                    // Masukkan batasan ke attribute 'max' HTML sehingga tanggal setelahnya tidak bisa diklik
                    tanggalSelesai.max = maxDateString;

                    // Jika user mengubah sub-cuti dan tanggal selesai saat ini melanggar batas max, reset nilainya
                    if (tanggalSelesai.value && tanggalSelesai.value > maxDateString) {
                        tanggalSelesai.value = '';
                    }
                }
            }
        }

        function resetStatusDokumen() {
            const labelDokumen = document.getElementById('label-dokumen');
            const inputDokumen = document.getElementById('input-dokumen');

            if (labelDokumen && inputDokumen) {
                labelDokumen.innerHTML = 'Dokumen Pendukung <span class="text-xs font-normal text-slate-400">(Opsional)</span>';
                inputDokumen.required = false;
            }
        }

        jenisCutiSelect.addEventListener('change', function () {
            handleJenisCutiChange(this.value, false);
            periksaSaldo();
        });

        subCutiSelect.addEventListener('change', function() {
            checkDokumenRequirement();
            batasiKalenderSelesai();
        });

        tanggalMulai.addEventListener('change', function() {
            if (this.value) {
                tanggalSelesai.min = this.value;
                if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
                    tanggalSelesai.value = this.value;
                }
                batasiKalenderSelesai();
            }
        });

        if (oldJenisCutiId) {
            handleJenisCutiChange(oldJenisCutiId, true);
        }
        periksaSaldo();
    });
</script>
@endsection

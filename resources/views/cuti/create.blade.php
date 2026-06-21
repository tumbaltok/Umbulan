@extends('layouts.app')

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

            <form action="{{ route('cuti.storeWeb') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- JENIS CUTI UTAMA --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Cuti</label>
                    <select name="jenis_cuti_id"
                        id="jenis_cuti_id"
                        class="w-full px-4 py-2.5 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('jenis_cuti_id') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-200' }}"
                        required>
                        {{-- DI-PERBARUI: Ditambahkan disabled dan hidden dari awal --}}
                        <option value="" disabled selected hidden>-- Pilih Jenis Cuti --</option>
                        @foreach($jenisCuti as $jenis)
                            @php
                                $userGender = strtolower(auth()->user()->gender->name ?? '');
                                $isPria = ($userGender === 'pria' || $userGender === 'male');
                                $namaCutiLower = strtolower($jenis->name_cuti);
                            @endphp

                            @if($isPria && (str_contains($namaCutiLower, 'melahirkan') || str_contains($namaCutiLower, 'haid')))
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
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 bg-sky-50/20 {{ $errors->has('sub_cuti_id') ? 'border-rose-500' : 'border-slate-200' }}">
                        {{-- DI-PERBARUI: Ditambahkan disabled, selected, dan hidden dari awal --}}
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
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow-sm shadow-sky-100 transition-colors">
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
        const isPria = {{ (strtolower(auth()->user()->gender->name ?? '') === 'pria' || strtolower(auth()->user()->gender->name ?? '') === 'male') ? 'true' : 'false' }};

        const jenisCutiSelect = document.getElementById('jenis_cuti_id');
        const wrapperSubCuti = document.getElementById('wrapper_sub_cuti');
        const subCutiSelect = document.getElementById('sub_cuti_id');
        const tanggalMulai = document.getElementById('tanggal_mulai');
        const tanggalSelesai = document.getElementById('tanggal_selesai');
        const labelAlasan = document.getElementById('label-alasan');
        const alasanCuti = document.getElementById('alasan_cuti');

        tanggalMulai.addEventListener('mousedown', function(e) {
            e.preventDefault();
            if (typeof this.showPicker === 'function') {
                this.showPicker();
            }
        });
        tanggalSelesai.addEventListener('mousedown', function(e) {
            e.preventDefault();
            if (typeof this.showPicker === 'function') {
                this.showPicker();
            }
        });

        // ==========================================
        // 1. LOGIKA SAAT DROPDOWN JENIS CUTI BERUBAH
        // ==========================================
        jenisCutiSelect.addEventListener('change', function () {
            const selectedId = this.value;
            const optionTerpilih = this.options[this.selectedIndex];
            const namaCuti = optionTerpilih ? optionTerpilih.getAttribute('data-nama-cuti') : '';

            // Validasi Wajib Alasan untuk jenis "Cuti"
            if (namaCuti === 'Cuti') {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-rose-600">*</span>';
                alasanCuti.setAttribute('required', 'required');
            } else {
                labelAlasan.innerHTML = 'Alasan / Catatan Tambahan <span class="text-xs font-normal text-slate-400">(Opsional)</span>';
                alasanCuti.removeAttribute('required');
            }

            // Bersihkan dropdown sub-cuti sebelumnya & setel ulang placeholder awal dengan disabled hidden
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
                        option.textContent = `${sub.nama_sub_cuti} ${sub.durasi_default ? '(' + sub.durasi_default + ' Hari)' : ''}`;
                        option.setAttribute('data-durasi', sub.durasi_default || '');
                        option.setAttribute('data-wajib-dokumen', sub.apakah_wajib_dokumen ? '1' : '0');
                        subCutiSelect.appendChild(option);
                    });

                    resetStatusDokumen();
                    return;
                }
            }

            wrapperSubCuti.style.display = 'none';
            subCutiSelect.removeAttribute('required');
            subCutiSelect.value = '';
            resetStatusDokumen();
        });


        // ==========================================
        // 2. LOGIKA SAAT DROPDOWN SUB-CUTI BERUBAH
        // ==========================================
        subCutiSelect.addEventListener('change', function() {
            const optionTerpilih = this.options[this.selectedIndex];

            if (optionTerpilih) {
                const teksOpsi = optionTerpilih.textContent.toLowerCase();
                const val = optionTerpilih.getAttribute('data-wajib-dokumen');

                if (teksOpsi.includes('sakit') || val === '1' || val === 1 || val === 'true') {
                    document.getElementById('label-dokumen').innerHTML = 'Dokumen Pendukung <span class="text-rose-600">*</span>';
                    document.getElementById('input-dokumen').required = true;
                } else {
                    resetStatusDokumen();
                }
            } else {
                resetStatusDokumen();
            }
        });


        // ==========================================
        // 3. AUTOMATISASI HITUNG TANGGAL SELESAI
        // ==========================================
        function hitungTanggalSelesaiOtomatis() {
            const selectedOption = subCutiSelect.options[subCutiSelect.selectedIndex];
            if (!selectedOption) return;

            const durasi = selectedOption.getAttribute('data-durasi');

            if (durasi && tanggalMulai.value) {
                const dateMulai = new Date(tanggalMulai.value);
                const dateSelesai = new Date(dateMulai.setDate(dateMulai.getDate() + parseInt(durasi) - 1));
                tanggalSelesai.value = dateSelesai.toISOString().split('T')[0];
            }
        }

        subCutiSelect.addEventListener('change', hitungTanggalSelesaiOtomatis);

        tanggalMulai.addEventListener('change', function() {
            if (this.value) {
                tanggalSelesai.min = this.value;
                if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
                    tanggalSelesai.value = this.value;
                }
                hitungTanggalSelesaiOtomatis();
            }
        });

        // ==========================================
        // 4. HELPER FUNCTION
        // ==========================================
        function resetStatusDokumen() {
            const labelDokumen = document.getElementById('label-dokumen');
            const inputDokumen = document.getElementById('input-dokumen');

            if (labelDokumen && inputDokumen) {
                labelDokumen.innerHTML = 'Dokumen Pendukung <span class="text-xs font-normal text-slate-400">(Opsional - Surat Dokter / Undangan / dll)</span>';
                inputDokumen.required = false;
            }
        }
    });
</script>
@endsection

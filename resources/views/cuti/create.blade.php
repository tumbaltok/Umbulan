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

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Cuti</label>
                    <select name="jenis_cuti_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('jenis_cuti_id') border-rose-500 @enderror" required>
                        <option value="">-- Pilih Jenis Cuti --</option>
                        @foreach($jenisCuti as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_cuti_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->name_cuti }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_cuti_id') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('tanggal_mulai') border-rose-500 @enderror" required>
                        @error('tanggal_mulai') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('tanggal_selesai') border-rose-500 @enderror" required>
                        @error('tanggal_selesai') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alasan Cuti</label>
                    <textarea name="alasan_cuti" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('alasan_cuti') border-rose-500 @enderror" placeholder="Tulis alasan pengajuan cuti secara jelas..." required>{{ old('alasan_cuti') }}</textarea>
                    @error('alasan_cuti') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Dokumen Pendukung <span class="text-xs font-normal text-slate-400">(Opsional - Surat Dokter / Undangan / dll)</span></label>
                    <input type="file" name="dokumen_pendukung" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 text-slate-600 text-sm focus:outline-none focus:border-sky-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 @error('dokumen_pendukung') border-rose-500 @enderror">
                    <p class="text-xs text-slate-400 mt-1.5">* Format yang didukung: PDF, JPG, JPEG, PNG (Maksimal 2MB)</p>
                    @error('dokumen_pendukung') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

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
@endsection

@extends('layouts.app')
@section('title', 'Pengaturan Profil Karyawan')
@section('content')
<div class="max-w-5xl mx-auto py-6 sm:py-8 px-4 sm:px-6 pb-28 sm:pb-32">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-200 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-sm backdrop-blur-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i class="fa-solid fa-circle-check text-base"></i>
            </div>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/80 text-rose-800 dark:text-rose-200 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-sm backdrop-blur-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
            </div>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xl shadow-slate-200/50 dark:shadow-slate-950/50 transition-colors overflow-hidden">
        {{-- Header Card --}}
        <div class="p-6 sm:p-7 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50 via-sky-50/20 to-slate-50 dark:from-slate-800/90 dark:via-slate-800/70 dark:to-slate-800/90 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center text-xl shadow-lg shadow-sky-500/25 shrink-0 border border-sky-400/30"
                     style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important; color: #ffffff !important;">
                    <i class="fa-solid fa-user-gear text-white" style="color: #ffffff !important;"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Pengaturan Akun & Keamanan</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            Profil Pegawai
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui data profil, berkas tanda tangan (TTD), status biometrik, jadwal kerja, dan kata sandi akun.</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700/60 border border-slate-200/80 dark:border-slate-600/80 text-slate-600 dark:text-slate-300 text-xs font-semibold flex items-center gap-2 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Akun Aktif</span>
                </span>
            </div>
        </div>

        {{-- Form data umum, jadwal kerja, & keamanan --}}
        <form id="form-profile-account" action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-7 space-y-7">
            @csrf
            @method('PUT')

            <div>
                <div class="flex items-center gap-2.5 mb-5 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                    <span class="w-7 h-7 rounded-lg bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs shadow-2xs">
                        <i class="fa-solid fa-id-badge"></i>
                    </span>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Identitas & Berkas Verifikasi</h3>
                </div>

                {{-- Container Foto Profil, TTD, & Biometrik Wajah --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7 pb-7 border-b border-slate-100 dark:border-slate-700/80">
                    {{-- Foto Profil --}}
                    <div class="flex flex-col items-center justify-between text-center p-5 bg-slate-50/80 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-sky-300 dark:hover:border-sky-700/60 transition-all shadow-xs group">
                        <div class="flex flex-col items-center w-full">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Foto Profil</span>
                            
                            @if($user->profile_photo)
                                {{-- Foto Tersedia --}}
                                <div class="relative w-24 h-24 rounded-2xl bg-white dark:bg-slate-800 shadow-md overflow-hidden border-2 border-white dark:border-slate-700 ring-4 ring-sky-500/15 group-hover:scale-105 transition-all">
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
                                    <div onclick="document.getElementById('openModalPhotoBtn').click()" class="absolute inset-0 bg-slate-900/50 backdrop-blur-2xs opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white cursor-pointer">
                                        <i class="fa-solid fa-camera text-base"></i>
                                        <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">Ubah</span>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mr-1.5 text-[9px]"></i> Foto Terpasang
                                    </span>
                                </div>
                            @else
                                {{-- Foto Kosong / Null (Desain Lebih Bagus & Estetik) --}}
                                <div class="w-full max-w-[200px] h-24 bg-sky-50/50 dark:bg-sky-950/30 rounded-2xl border-2 border-dashed border-sky-300/80 dark:border-sky-800/80 flex items-center justify-center p-2.5 transition-all group-hover:border-sky-400 dark:group-hover:border-sky-600 relative overflow-hidden">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-md shadow-sky-600/25 border border-white/40 dark:border-sky-400/30 text-white shrink-0 group-hover:scale-105 transition-transform"
                                             style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important;">
                                            <div class="flex flex-col items-center leading-none">
                                                <i class="fa-solid fa-user text-xs text-white drop-shadow-xs mb-0.5"></i>
                                                <span class="text-[10px] font-black font-mono uppercase tracking-wider text-white">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 leading-tight">Belum Ada Foto</span>
                                            <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Maks. 2MB (JPG/PNG)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1.5 text-[9px]"></i> Belum Terdaftar
                                    </span>
                                </div>
                            @endif
                        </div>

                        <button type="button" id="openModalPhotoBtn" class="mt-3 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-sky-950/60 border border-slate-200 dark:border-slate-700 text-sky-600 dark:text-sky-400 text-xs font-bold transition-all shadow-2xs hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-camera text-xs"></i>
                            <span>{{ $user->profile_photo ? 'Ubah Foto Profil' : 'Unggah Foto Profil' }}</span>
                        </button>
                    </div>

                    {{-- Tanda Tangan Digital (TTD) --}}
                    @php
                        $hasValidSignature = !empty($user->signature) && (
                            file_exists(public_path('storage/' . $user->signature)) || 
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($user->signature)
                        );
                    @endphp
                    <div id="signature" class="flex flex-col items-center justify-between text-center p-5 bg-slate-50/80 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all shadow-xs group">
                        <div class="flex flex-col items-center w-full">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Tanda Tangan Digital</span>
                            <div class="w-full max-w-[200px] h-22 bg-white dark:bg-slate-950/90 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden p-2 shadow-inner relative group-hover:border-emerald-300 dark:group-hover:border-emerald-600 transition-colors">
                                @if($hasValidSignature)
                                    <img src="{{ asset('storage/' . $user->signature) }}?v={{ time() }}"
                                        alt="Tanda Tangan"
                                        onerror="this.style.display='none'; document.getElementById('signature_default_fallback').classList.remove('hidden');"
                                        class="max-w-full max-h-full object-contain filter dark:drop-shadow-[0_0_1px_rgba(255,255,255,0.7)]">
                                    
                                    <div id="signature_default_fallback" class="hidden flex flex-col items-center justify-center text-center w-full h-full">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-1">
                                            <i class="fa-solid fa-signature text-xs"></i>
                                        </div>
                                        <span class="text-[10.5px] font-bold text-slate-500 dark:text-slate-400">Belum Ada TTD</span>
                                    </div>
                                @else
                                    <div class="w-full h-full bg-emerald-50/30 dark:bg-emerald-950/20 rounded-xl border border-dashed border-emerald-300/80 dark:border-emerald-800/60 flex flex-col items-center justify-center text-center p-1.5 transition-all">
                                        <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs border border-emerald-100 dark:border-emerald-800/50 mb-1">
                                            <i class="fa-solid fa-signature text-xs"></i>
                                        </div>
                                        <span class="text-[10.5px] font-bold text-slate-600 dark:text-slate-300">Belum Ada TTD</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2.5">
                                @if($hasValidSignature)
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mr-1.5 text-[9px]"></i> TTD Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1.5 text-[9px]"></i> Belum Terdaftar
                                    </span>
                                @endif
                            </div>
                        </div>

                        <button type="button" id="openModalSignatureBtn" class="mt-3 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 border border-slate-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400 text-xs font-bold transition-all shadow-2xs hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5 cursor-pointer">
                            <i class="fa-solid fa-file-signature text-xs"></i>
                            <span>{{ $hasValidSignature ? 'Ubah TTD' : 'Unggah TTD' }}</span>
                        </button>
                    </div>

                    {{-- Biometrik Wajah --}}
                    <div class="flex flex-col items-center justify-between text-center p-5 bg-slate-50/80 dark:bg-slate-900/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-sky-300 dark:hover:border-sky-700/60 transition-all shadow-xs group">
                        <div class="flex flex-col items-center w-full">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Biometrik Wajah</span>
                            <div class="w-22 h-22 rounded-2xl {{ !empty($user->face_descriptor) ? 'bg-gradient-to-tr from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80' : 'bg-gradient-to-tr from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/80' }} flex items-center justify-center text-3xl shadow-sm ring-4 {{ !empty($user->face_descriptor) ? 'ring-emerald-500/10 dark:ring-emerald-500/20' : 'ring-amber-500/10 dark:ring-amber-500/20' }} group-hover:scale-105 transition-transform">
                                <i class="fa-solid {{ !empty($user->face_descriptor) ? 'fa-face-smile text-emerald-600 dark:text-emerald-400' : 'fa-camera-rotate' }}"></i>
                            </div>

                            <div class="mt-2.5">
                                @if(!empty($user->face_descriptor))
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <i class="fa-solid fa-lock text-emerald-600 dark:text-emerald-400 mr-1.5 text-[9px]"></i> Biometrik Terkunci
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1.5 text-[9px]"></i> Belum Terdaftar
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($user->face_descriptor))
                            <div class="mt-3 p-2 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-200/70 dark:border-sky-800/60 rounded-xl text-[10.5px] text-slate-600 dark:text-slate-300 leading-snug text-center">
                                <i class="fa-solid fa-circle-check text-sky-600 dark:text-sky-400 mr-1"></i> Terverifikasi untuk presensi.
                            </div>
                        @else
                            <a href="{{ route('dashboard') }}" class="mt-3 px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition-all shadow-xs hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5">
                                <i class="fa-solid fa-camera text-xs"></i>
                                <span>Rekam di Dashboard</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2.5 mb-5 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                    <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs shadow-2xs">
                        <i class="fa-solid fa-address-card"></i>
                    </span>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Data Pegawai & Penugasan Kedinasan</h3>
                </div>

                {{-- Grid untuk Form Data Profil --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- NIP --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="input_nip" class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <span>Nomor Induk Pegawai (NIP)</span>
                                <button type="button" 
                                    onclick="bukaModalPanduanNip()" 
                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-sky-100 hover:bg-sky-200 dark:bg-sky-950/70 dark:hover:bg-sky-900/80 text-sky-600 dark:text-sky-400 text-[10px] font-bold transition-all shadow-2xs hover:scale-105 active:scale-95 focus:outline-none cursor-pointer" 
                                    title="Petunjuk cara mengetahui NIP">
                                    <i class="fa-solid fa-info text-[9px]"></i>
                                </button>
                            </label>
                            @if(empty($user->nip))
                                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2.5 py-0.5 rounded-lg border border-amber-200/80 dark:border-amber-900/60 flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Belum diatur
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-0.5 rounded-lg border border-emerald-200/80 dark:border-emerald-900/60 flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Terdaftar
                                </span>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="text" id="input_nip" name="nip" value="{{ old('nip', $user->nip) }}" 
                                placeholder="Belum diatur — klik (i) untuk panduan NIP"
                                class="w-full px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all {{ $errors->has('nip') ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }}">
                        </div>
                        @error('nip') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                            <span class="text-[10px] text-slate-400 font-medium">Sesuai KTP / SK</span>
                        </div>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border rounded-xl text-slate-800 dark:text-slate-100 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }}" required>
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="gender_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jenis Kelamin</label>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-600 flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                            </span>
                        </div>
                        <select id="gender_id" disabled class="block w-full px-4 py-2.5 bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 text-sm font-medium cursor-not-allowed pointer-events-none select-none transition-all">
                            @php
                                $userGenderValue = old('gender_id', $user->gender_id ?? '');
                            @endphp
                            <option value="" disabled {{ empty($userGenderValue) ? 'selected' : '' }}>Pilih Jenis Kelamin</option>
                            @if(isset($daftarGender) && count($daftarGender) > 0)
                                @foreach($daftarGender as $gender)
                                    <option value="{{ $gender->id }}" {{ (string)$userGenderValue === (string)$gender->id ? 'selected' : '' }}>
                                        {{ $gender->name ?? $gender->gender_name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="1" {{ (string)$userGenderValue === '1' ? 'selected' : '' }}>Laki-laki (Pria)</option>
                                <option value="2" {{ (string)$userGenderValue === '2' ? 'selected' : '' }}>Perempuan (Wanita)</option>
                            @endif
                        </select>
                        <input type="hidden" name="gender_id" value="{{ $user->gender_id }}">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-sky-500 text-[10px]"></i>
                            Jenis kelamin terkunci otomatis demi mencegah manipulasi kuota cuti.
                        </p>
                        @error('gender_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Dropdown Pilihan Stasiun Penempatan Kerja --}}
                    @php
                        $userStationValue = old('station_id', $user->station_id ?? '');
                        $isStationLocked = !empty($user->station_id);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="station_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Penempatan Kerja</label>
                            @if($isStationLocked)
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-600 flex items-center gap-1 shadow-2xs">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                                </span>
                            @endif
                        </div>
                        <select id="station_id" {{ $isStationLocked ? 'disabled' : 'name=station_id required' }} class="block w-full px-4 py-2.5 {{ $isStationLocked ? 'bg-slate-100/80 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 cursor-not-allowed pointer-events-none select-none' : 'bg-slate-50/50 dark:bg-slate-900/60 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500' }} border rounded-xl text-sm font-medium transition-all {{ $errors->has('station_id') ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }}">
                            <option value="" disabled {{ empty($userStationValue) ? 'selected' : '' }}>Pilih Tempat Kerja</option>
                            @if(isset($daftarStasiun) && count($daftarStasiun) > 0)
                                @foreach($daftarStasiun as $stasiun)
                                    {{-- Sembunyikan jika type stasiun adalah 'rumah_meter' --}}
                                    @if(($stasiun->type ?? '') !== 'rumah_meter')
                                        <option value="{{ $stasiun->id }}" {{ (string)$userStationValue === (string)$stasiun->id ? 'selected' : '' }}>
                                            {{ $stasiun->name }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @if($isStationLocked)
                            <input type="hidden" name="station_id" value="{{ $user->station_id }}">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-sky-500 text-[10px]"></i>
                                Penempatan kerja terkunci demi akurasi absensi. Hubungi Admin jika mutasi.
                            </p>
                        @endif
                        @error('station_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jabatan / Role (Terkunci Otomatis / Dikelola Admin) --}}
                    <div class="md:col-span-2 relative" id="roleSelectWrapper">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Peran & Jabatan Organisasi</label>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-600 flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                            </span>
                        </div>

                        @php
                            $currentRoleIds = old('roles');
                            if ($currentRoleIds === null) {
                                $currentRoleIds = $user->roles->pluck('id')->toArray();
                                if (empty($currentRoleIds) && $user->role_id) {
                                    $currentRoleIds = [$user->role_id];
                                }
                            }
                            $currentRoleNames = [];
                            if (isset($daftarRole) && count($daftarRole) > 0) {
                                foreach ($daftarRole as $r) {
                                    if (in_array($r->id, (array)$currentRoleIds)) {
                                        $currentRoleNames[] = $r->role_name;
                                    }
                                }
                            }
                            if (empty($currentRoleNames) && $user->role) {
                                $currentRoleNames[] = $user->role->role_name;
                            }
                            $initialRoleDisplay = !empty($currentRoleNames) ? implode(', ', $currentRoleNames) : 'Tidak Ada Role';
                        @endphp

                        {{-- Tampilan Box Terkunci --}}
                        <div class="w-full px-4 py-2.5 bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 text-sm cursor-not-allowed pointer-events-none select-none transition-all flex items-center justify-between gap-2 min-h-[44px]">
                            <div class="flex items-center gap-1.5 flex-1 min-w-0 flex-wrap">
                                @if(!empty($currentRoleNames))
                                    @foreach($currentRoleNames as $rName)
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-600 shadow-2xs">
                                            {{ $rName }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum Ada Peran / Jabatan</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-slate-400 shrink-0">
                                @if(count((array)$currentRoleIds) > 1)
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 rounded-full border border-sky-200 dark:border-sky-800 shadow-2xs">
                                        {{ count((array)$currentRoleIds) }} Peran
                                    </span>
                                @endif
                                <i class="fa-solid fa-lock text-xs text-slate-400"></i>
                            </div>
                        </div>

                        {{-- Hidden inputs agar data peran tetap terkirim saat form disimpan --}}
                        <input type="hidden" id="primary_role_id" name="role_id" value="{{ old('role_id', $user->role_id) }}">
                        @foreach((array)$currentRoleIds as $rId)
                            <input type="hidden" name="roles[]" value="{{ $rId }}">
                        @endforeach

                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-sky-500 text-[10px]"></i>
                            Peran kerja dikunci otomatis demi hierarki organisasi. Hubungi Administrator jika terdapat penyesuaian.
                        </p>

                        @error('roles') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @error('role_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Cakupan Wilayah Rumah Meter (Khusus Role AREA (PIPELINE) - Terkunci) --}}
                    @php
                        $userAssignedRmIds = old('assigned_stations', $user->assignedStations->pluck('id')->toArray());
                        $isUserPipeline = $user->hasRole('AREA (PIPELINE)') || $user->hasRole(14);
                    @endphp
                    @if($isUserPipeline)
                        <div class="md:col-span-2 transition-all duration-300" id="pengaturanPipelineRumahMeterContainer">
                            <div class="p-4.5 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/90 dark:border-amber-800/60 rounded-2xl space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                            <i class="fa-solid fa-gauge-high text-amber-600 dark:text-amber-400"></i> Cakupan Wilayah Rumah Meter (Role Pipeline)
                                        </label>
                                        <p class="text-[11px] text-amber-700/90 dark:text-amber-400 font-medium mt-0.5">
                                            Daftar Checkpoint Rumah Meter penugasan resmi dari Administrator:
                                        </p>
                                    </div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold bg-white/80 dark:bg-slate-800/80 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800 flex items-center gap-1 shadow-2xs self-start sm:self-auto">
                                        <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto pr-1">
                                    @if(isset($daftarRumahMeter) && count($daftarRumahMeter) > 0)
                                        @foreach($daftarRumahMeter as $rm)
                                            @php
                                                $isRmChecked = in_array($rm->id, (array)$userAssignedRmIds);
                                            @endphp
                                            <div class="flex items-center space-x-2 p-2.5 {{ $isRmChecked ? 'bg-white dark:bg-slate-800 border-amber-300 dark:border-amber-700 shadow-2xs' : 'bg-slate-100 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 opacity-60' }} border rounded-xl text-xs select-none pointer-events-none">
                                                <input type="checkbox" disabled {{ $isRmChecked ? 'checked' : '' }}
                                                    class="rounded border-slate-300 dark:border-slate-600 text-amber-600 w-3.5 h-3.5 cursor-not-allowed">
                                                <span class="font-medium text-slate-700 dark:text-slate-200 truncate">
                                                    <strong class="font-mono text-amber-700 dark:text-amber-400">{{ $rm->kode_stasiun }}</strong> {{ $rm->name }}
                                                </span>
                                            </div>
                                            @if($isRmChecked)
                                                <input type="hidden" name="assigned_stations[]" value="{{ $rm->id }}">
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Alamat Email --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat Email</label>
                            <div>
                                @if($user->email_verified_at)
                                    <span id="email-badge" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Terverifikasi
                                    </span>
                                @else
                                    <span id="email-badge" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="relative">
                            <input type="email"
                                value="{{ $user->email }}"
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 rounded-xl cursor-not-allowed select-none font-medium text-sm"
                                readonly
                                disabled>
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-lock text-slate-400 text-[10px]"></i>
                            Email akun kedinasan bersifat permanen dan tidak dapat diubah mandiri.
                        </p>
                    </div>

                    {{-- No. WhatsApp / Telepon --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">No. WhatsApp / Telepon</label>
                            <div class="flex items-center gap-2">
                                @if($user->hasVerifiedPhone())
                                    <span id="phone-badge" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                                        <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> Terverifikasi
                                    </span>
                                    <button type="button"
                                        id="btn-change-phone"
                                        onclick="openChangePhoneModal()"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/60 dark:hover:bg-sky-900/60 border border-sky-200/80 dark:border-sky-800 px-2.5 py-0.5 rounded-lg transition-all shadow-2xs cursor-pointer active:scale-95"
                                        title="Ajukan permohonan ganti nomor ke Admin">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                        <span>Ganti</span>
                                    </button>
                                @else
                                    <span id="phone-badge" class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="relative">
                            @if($user->hasVerifiedPhone())
                                <input type="text"
                                    value="{{ $user->phone_number }}"
                                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-100/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 rounded-xl cursor-not-allowed select-none font-medium text-sm font-mono"
                                    readonly
                                    disabled>
                                <input type="hidden" name="phone_number" value="{{ $user->phone_number }}">
                            @else
                                <input type="text"
                                    name="phone_number"
                                    id="phone_number"
                                    value="{{ old('phone_number', $user->phone_number ?? '') }}"
                                    class="w-full px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 font-medium text-slate-800 dark:text-slate-100 text-sm font-mono transition-all"
                                    placeholder="Contoh: 08123456789">
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                            @if($user->hasVerifiedPhone())
                                * Nomor terverifikasi terkunci demi keamanan dinas. Klik tombol <strong class="text-sky-600 dark:text-sky-400">Ganti</strong> untuk hubungi Admin.
                            @else
                                * Pastikan nomor aktif terhubung WhatsApp untuk penerimaan OTP dan notifikasi dinas.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700/80">

            {{-- PENGATURAN JADWAL KERJA --}}
            @php
                $activeScheduleType = old('schedule_type', $user->schedule_type ?? '');
                $scheduleAlreadySet = !empty($user->schedule_type);

                $currentDbShift = 'pagi';
                if ($user->schedule_type === 'roster') {
                    try {
                        $scheduleService = app(App\Services\ScheduleService::class);
                        $todaySchedule = $scheduleService->getTodaySchedule($user);
                        $currentDbShift = $todaySchedule['shift_type'] ?? 'pagi';
                    } catch (\Exception $e) {
                        $currentDbShift = 'pagi';
                    }
                }
                $selectedRosterShift = old('current_shift_choice', $currentDbShift);
            @endphp

            <div id="schedule_setting" class="p-5 sm:p-6 bg-slate-50/60 dark:bg-slate-900/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition-all duration-300">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/60 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs shadow-2xs">
                            <i class="fa-solid fa-business-time"></i>
                        </span>
                        <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Pengaturan Jadwal Kerja</h3>
                    </div>
                    @if($scheduleAlreadySet)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">
                            <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 text-[10px]"></i> Terkunci (Anti-Cheating)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-bold bg-sky-50 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 shadow-2xs">
                            <i class="fa-solid fa-pen-clip text-[10px]"></i> Pengaturan Awal
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    @if($scheduleAlreadySet)
                        Status jadwal kerja operasional akun Anda terkunci demi integritas absensi kedinasan.
                    @else
                        Pilih jenis jadwal kerja yang berlaku untuk akun Anda (Normal atau Roster). Pengaturan ini hanya dapat dilakukan satu kali.
                    @endif
                </p>

                <div class="space-y-4">

                    {{-- KARTU MODE BACA JADWAL TERKUNCI (JIKA SUDAH DI-SET) --}}
                    @if($scheduleAlreadySet)
                        <div id="schedule_read_mode" class="p-4 sm:p-5 bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-2xl space-y-4 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/80">
                                <div class="flex items-center gap-3.5">
                                    @if($user->schedule_type === 'roster')
                                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-amber-500/20 border border-amber-400/30"
                                             style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%) !important; color: #ffffff !important;">
                                            <i class="fa-solid fa-rotate text-white text-lg" style="color: #ffffff !important;"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Jadwal Aktif</p>
                                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                                Sistem Roster / Shift (12 Jam)
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Shift Saat Ini:
                                                @if($currentDbShift === 'pagi')
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Shift Pagi (07:00 – 19:00 WIB)</span>
                                                @elseif($currentDbShift === 'malam')
                                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">Shift Malam (19:00 – 07:00 WIB)</span>
                                                @else
                                                    <span class="font-bold text-rose-600 dark:text-rose-400">OFF / Libur</span>
                                                @endif
                                            </p>
                                        </div>
                                    @elseif($user->schedule_type === 'reguler_6_hari')
                                        <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-teal-500/20 border border-teal-400/30"
                                             style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%) !important; color: #ffffff !important;">
                                            <i class="fa-solid fa-business-time text-white text-lg" style="color: #ffffff !important;"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Jadwal Aktif</p>
                                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                                Reguler (6 Hari: Senin – Sabtu)
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Sen–Jum: 07:00 – 16:00 WIB &bull; Sabtu: 07:00 – 12:00 WIB (1/2 Hari)
                                            </p>
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-sky-500/20 border border-sky-400/30"
                                             style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important; color: #ffffff !important;">
                                            <i class="fa-solid fa-business-time text-white text-lg" style="color: #ffffff !important;"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Jadwal Aktif</p>
                                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                                Reguler (5 Hari: Senin – Jumat)
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Senin s/d Jumat: 07:00 – 16:00 WIB &bull; Sabtu & Minggu (OFF)
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Aksi: Admin Ubah Jadwal & Karyawan Hubungi Admin --}}
                                <div class="flex items-center gap-2 self-start sm:self-center">
                                    @if(!empty($isAdmin))
                                        <button type="button" onclick="showScheduleEditMode()" class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer active:scale-95" title="Ubah jadwal sebagai Administrator">
                                            <i class="fa-solid fa-user-shield text-amber-600 dark:text-amber-400"></i>
                                            <span>Ubah (Admin)</span>
                                        </button>
                                    @endif
                                    <button type="button" onclick="openScheduleContactAdminModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>Hubungi Admin</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Alert Informasi Terkunci & Petunjuk Hubungi Admin --}}
                            <div class="p-3.5 bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 rounded-xl flex items-start gap-3 text-xs text-amber-900 dark:text-amber-200">
                                <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 mt-0.5 text-sm shrink-0"></i>
                                <div class="leading-relaxed">
                                    <p class="font-bold text-amber-950 dark:text-amber-100">
                                        Jadwal Kerja Terkunci (Pencegahan Kecurangan/Anti-Cheating)
                                    </p>
                                    <p class="text-[11px] text-amber-800/90 dark:text-amber-300/90 mt-0.5">
                                        Pengaturan jadwal kerja akun Anda telah aktif dan dinonaktifkan (disable) agar tidak dapat diubah-ubah secara mandiri. Apabila terdapat <strong>kesalahan input</strong> saat pengaturan awal atau terdapat <strong>perubahan/mutasi shift dinas resmi</strong>, silakan klik tombol <strong>Hubungi Admin</strong> untuk mengajukan penyesuaian resmi ke Administrator Sistem.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs jika bukan admin untuk memastikan nilai jadwal tidak terhapus saat submit form profil --}}
                        @if(empty($isAdmin))
                            <input type="hidden" name="schedule_type" value="{{ $user->schedule_type }}">
                            @if($user->schedule_type === 'roster' && $user->roster_start_date)
                                <input type="hidden" name="roster_start_date" value="{{ \Carbon\Carbon::parse($user->roster_start_date)->format('Y-m-d') }}">
                            @endif
                        @endif
                    @endif

                    {{-- FORM EDIT (Hanya jika belum di-set ATAU jika pengguna adalah Administrator) --}}
                    @if(!$scheduleAlreadySet || !empty($isAdmin))
                        <div id="schedule_edit_mode" class="{{ $scheduleAlreadySet ? 'hidden' : '' }} space-y-4">
                            @if($scheduleAlreadySet && !empty($isAdmin))
                                <div class="flex items-center justify-between pb-2 border-b border-amber-200 dark:border-amber-800/60 mb-1">
                                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-shield"></i> Mode Pengeditan Khusus Administrator
                                    </span>
                                    <button type="button" onclick="cancelScheduleEditMode()" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 font-semibold underline cursor-pointer">
                                        Batal Ubah
                                    </button>
                                </div>
                            @elseif(!$scheduleAlreadySet)
                                <div class="p-3.5 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-2xl flex items-start gap-3 text-xs text-sky-900 dark:text-sky-200 mb-2">
                                    <i class="fa-solid fa-circle-info text-sky-600 dark:text-sky-400 mt-0.5 text-base shrink-0"></i>
                                    <div class="leading-relaxed">
                                        <p class="font-bold text-sky-950 dark:text-sky-100">
                                            Perhatian: Pengaturan Jadwal Kerja Hanya Dapat Disetel Sekali
                                        </p>
                                        <p class="text-[11px] text-sky-800/90 dark:text-sky-300/90 mt-0.5">
                                            Pilih tipe jadwal kerja dengan cermat sesuai surat tugas/penugasan Anda. Setelah disimpan, pengaturan jadwal akan <strong>otomatis dikunci permanen (disable)</strong> untuk mencegah kecurangan/cheating absensi. Jika terdapat kesalahan input, Anda harus menghubungi Administrator.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Pilihan Jenis Jadwal --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Tipe Jadwal Kerja</label>
                                <select id="schedule_type" name="schedule_type" onchange="toggleScheduleOptions()" class="w-full md:w-2/3 px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-medium">
                                    <option value="" disabled {{ empty($activeScheduleType) ? 'selected' : '' }}>-- Pilih Jenis Jadwal --</option>
                                    <option value="reguler_5_hari" {{ in_array($activeScheduleType, ['reguler_5_hari', 'normal']) ? 'selected' : '' }}>
                                        Reguler (5 Hari: Senin – Jumat)
                                    </option>
                                    <option value="reguler_6_hari" {{ $activeScheduleType === 'reguler_6_hari' ? 'selected' : '' }}>
                                        Reguler (6 Hari: Senin – Sabtu, Setengah Hari)
                                    </option>
                                    <option value="roster" {{ $activeScheduleType === 'roster' ? 'selected' : '' }}>
                                        Roster / Shift (12 Jam)
                                    </option>
                                </select>
                            </div>

                            {{-- Card Info Jadwal Reguler 5 Hari --}}
                            <div id="section_reguler_5_info" class="hidden p-4 sm:p-5 bg-sky-50/60 dark:bg-sky-950/30 border border-sky-200/80 dark:border-sky-800/60 rounded-2xl space-y-3">
                                <div class="flex items-center gap-2 text-sky-800 dark:text-sky-300 font-bold text-xs uppercase tracking-wider">
                                    <i class="fa-solid fa-calendar-check text-sky-600 dark:text-sky-400"></i>
                                    Ketentuan Baku Jadwal Reguler 5 Hari
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-sky-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Hari Kerja</span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Senin s/d Jumat</span>
                                    </div>
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-sky-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Jam Masuk & Pulang</span>
                                        <span class="text-xs font-bold text-sky-600 dark:text-sky-400">07:00 – 16:00 WIB</span>
                                    </div>
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-sky-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Hari Libur</span>
                                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Sabtu & Minggu (OFF)</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info text-sky-500"></i>
                                    Mengikuti seluruh Tanggal Merah Hari Libur Nasional resmi pemerintah (SKB 3 Menteri). Jam kerja dibakukan secara otomatis oleh sistem.
                                </p>
                            </div>

                            {{-- Card Info Jadwal Reguler 6 Hari --}}
                            <div id="section_reguler_6_info" class="hidden p-4 sm:p-5 bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/80 dark:border-teal-800/60 rounded-2xl space-y-3">
                                <div class="flex items-center gap-2 text-teal-800 dark:text-teal-300 font-bold text-xs uppercase tracking-wider">
                                    <i class="fa-solid fa-calendar-check text-teal-600 dark:text-teal-400"></i>
                                    Ketentuan Baku Jadwal Reguler 6 Hari
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-teal-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Hari Kerja</span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Senin s/d Sabtu</span>
                                    </div>
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-teal-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Jam Kerja Baku</span>
                                        <span class="text-xs font-bold text-teal-600 dark:text-teal-400">Sen–Jum: 07:00–16:00<br>Sabtu: 07:00–12:00 WIB</span>
                                    </div>
                                    <div class="p-3.5 bg-white dark:bg-slate-800/90 rounded-xl border border-teal-100 dark:border-slate-700 shadow-2xs">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Hari Libur</span>
                                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Minggu (OFF)</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info text-teal-500"></i>
                                    Khusus hari Sabtu berlaku setengah hari kerja (pulang pukul 12:00 WIB). Mengikuti Tanggal Merah Hari Libur Nasional resmi pemerintah (SKB 3 Menteri).
                                </p>
                            </div>

                            {{-- Form Opsi Jadwal Roster --}}
                            <div id="section_roster_schedule" class="hidden p-4 sm:p-5 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider mb-1">Shift Anda Saat Ini</label>
                                    <p class="text-[11px] text-amber-700/90 dark:text-amber-400 mb-3">Sistem akan secara otomatis menghitung dan memutar jadwal rotasi shift Anda setiap hari Selasa pukul 07:00 WIB.</p>

                                    <input type="hidden" id="roster_start_date_input" name="roster_start_date" value="{{ old('roster_start_date', $user->roster_start_date ? \Carbon\Carbon::parse($user->roster_start_date)->format('Y-m-d') : '') }}">

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <label class="flex items-center space-x-3 p-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-sky-500 hover:shadow-2xs transition-all">
                                            <input type="radio" name="current_shift_choice" value="pagi" {{ $selectedRosterShift === 'pagi' ? 'checked' : '' }} onchange="calculateRosterAnchor('pagi')" class="text-sky-600 focus:ring-sky-500">
                                            <div>
                                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Shift Pagi</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">07:00 - 19:00 WIB</span>
                                            </div>
                                        </label>
                                        <label class="flex items-center space-x-3 p-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-indigo-500 hover:shadow-2xs transition-all">
                                            <input type="radio" name="current_shift_choice" value="malam" {{ $selectedRosterShift === 'malam' ? 'checked' : '' }} onchange="calculateRosterAnchor('malam')" class="text-indigo-600 focus:ring-indigo-500">
                                            <div>
                                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">Shift Malam</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">19:00 - 07:00 WIB</span>
                                            </div>
                                        </label>
                                        <label class="flex items-center space-x-3 p-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 hover:shadow-2xs transition-all">
                                            <input type="radio" name="current_shift_choice" value="libur" {{ $selectedRosterShift === 'libur' ? 'checked' : '' }} onchange="calculateRosterAnchor('libur')" class="text-emerald-600 focus:ring-emerald-500">
                                            <div>
                                                <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">OFF</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">OFF / Libur</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- BOX PRATINJAU HASIL ROTASI --}}
                                <div id="roster_preview_box" class="p-3.5 bg-white dark:bg-slate-800 border border-amber-300/80 dark:border-amber-700/60 rounded-xl shadow-xs text-xs space-y-2.5">
                                    <div class="font-bold text-amber-900 dark:text-amber-300 border-b border-slate-100 dark:border-slate-700 pb-2 flex items-center">
                                        <i class="fa-solid fa-eye text-amber-600 dark:text-amber-400 mr-2"></i> Pratinjau Jadwal Rotasi Roster Anda:
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-slate-700 dark:text-slate-300">
                                        <div class="bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                            <span class="block text-[10px] text-slate-400 font-semibold">MINGGU INI:</span>
                                            <span id="preview_week_1" class="font-bold text-sky-600 dark:text-sky-400">Shift Pagi</span>
                                        </div>
                                        <div class="bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                            <span class="block text-[10px] text-slate-400 font-semibold">SELASA DEPAN (07:00 WIB):</span>
                                            <span id="preview_week_2" class="font-bold text-indigo-600 dark:text-indigo-400">Shift Malam</span>
                                        </div>
                                        <div class="bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                            <span class="block text-[10px] text-slate-400 font-semibold">2 MINGGU LAGI:</span>
                                            <span id="preview_week_3" class="font-bold text-emerald-600 dark:text-emerald-400">Minggu Libur</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700/80">

            <div>
                <div class="flex items-center gap-2.5 mb-2 pb-2 border-b border-slate-100 dark:border-slate-700/80">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs shadow-2xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Keamanan Akun & Kata Sandi</h3>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password akun.</p>

                <div class="space-y-4">
                    {{-- Password Lama --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Password Saat Ini (Password Lama)</label>
                        <input type="password" name="current_password" class="w-full md:w-1/2 px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono text-sm {{ $errors->has('current_password') ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }}" placeholder="••••••••">
                        @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password Baru --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Password Baru</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono text-sm {{ $errors->has('new_password') ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }}" placeholder="Minimal 8 karakter">
                            @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" class="w-full px-4 py-2.5 bg-slate-50/50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all font-mono text-sm" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('modals')
{{-- FLOATING ACTION DOCK (MELAYANG DI BAWAH VIEWPORT) --}}
<div class="fixed bottom-0 inset-x-0 z-40 p-4 sm:p-5 pointer-events-none">
    <div class="max-w-5xl mx-auto pointer-events-auto">
        <div class="p-3.5 sm:px-6 sm:py-3.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-700/90 shadow-2xl shadow-slate-900/20 dark:shadow-black/70 flex flex-col sm:flex-row items-center justify-between gap-3 transition-all ring-1 ring-black/5 dark:ring-white/5">
            <div class="flex items-center gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500 animate-pulse shrink-0"></span>
                <span class="font-medium text-xs sm:text-sm">Perubahan data profil & keamanan akun dapat langsung disimpan.</span>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <button type="submit"
                    id="btnSubmitProfile"
                    form="form-profile-account"
                    class="w-full sm:w-auto px-7 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 transition-all cursor-pointer flex items-center justify-center gap-2 group disabled:opacity-75 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important; color: #ffffff !important;">
                    <i id="btnSubmitProfileIcon" class="fa-solid fa-floppy-disk text-xs text-white transition-transform group-hover:scale-110" style="color: #ffffff !important;"></i>
                    <i id="btnSubmitProfileSpinner" class="fa-solid fa-circle-notch fa-spin text-xs text-white hidden" style="color: #ffffff !important;"></i>
                    <span id="btnSubmitProfileText" class="text-white font-bold" style="color: #ffffff !important;">Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL FOTO PROFIL --}}
<div id="photoModal"
     data-has-error="{{ $errors->has('profile_photo') ? 'true' : 'false' }}"
     class="fixed inset-0 z-50 items-center justify-center hidden p-4 sm:p-6 overflow-y-auto">
    <div id="modalBackdrop" class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md p-6 relative z-10 transform transition-all m-auto my-auto max-h-[90vh] overflow-y-auto border border-slate-200/80 dark:border-slate-700/80">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shadow-2xs">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base">Perbarui Foto Profil</h3>
            </div>
            <button type="button" id="closeModalPhotoBtn" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4">
            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" id="uploadPhotoForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-5 text-center hover:border-sky-400 dark:hover:border-sky-500 transition-colors bg-slate-50/70 dark:bg-slate-900/50">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-sky-500/70 dark:text-sky-400/70 mb-2 block"></i>
                    <input type="file" name="profile_photo" id="profile_photo_input" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 dark:file:bg-sky-950/70 dark:file:text-sky-300 hover:file:bg-sky-100 dark:hover:file:bg-sky-900/60 cursor-pointer" required/>
                </div>
                @error('profile_photo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center mt-2">Format file: JPG, JPEG, PNG. Maksimal ukuran 2MB.</p>
            </form>

            @if($user->profile_photo)
                <div class="pt-2 text-center border-t border-slate-100 dark:border-slate-700/80">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="delete_photo" value="1">
                        <button type="submit" class="inline-flex items-center space-x-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 transition-colors bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 px-3.5 py-1.5 rounded-xl border border-rose-200/80 dark:border-rose-900/60 shadow-2xs active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i> <span>Hapus Foto Saat Ini</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-2.5 mt-6 justify-end border-t border-slate-100 dark:border-slate-700/80 pt-4">
            <button type="button" id="cancelModalPhotoBtn" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer">Batal</button>
            <button type="submit" form="uploadPhotoForm" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/25 transition-all cursor-pointer" style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important; color: #ffffff !important;">Simpan Foto</button>
        </div>
    </div>
</div>

{{-- MODAL UPLOAD TANDA TANGAN DIGITAL (TTD) --}}
<div id="signatureModal"
     data-has-error="{{ $errors->has('signature') ? 'true' : 'false' }}"
     class="fixed inset-0 z-50 items-center justify-center hidden p-4 sm:p-6 overflow-y-auto">
    <div id="modalSignatureBackdrop" class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md p-6 relative z-10 transform transition-all m-auto my-auto max-h-[90vh] overflow-y-auto border border-slate-200/80 dark:border-slate-700/80">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3.5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shadow-2xs">
                    <i class="fa-solid fa-signature"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-base">Unggah Berkas TTD</h3>
            </div>
            <button type="button" id="closeModalSignatureBtn" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4">
            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" id="uploadSignatureForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-5 text-center hover:border-emerald-400 dark:hover:border-emerald-500 transition-colors bg-slate-50/70 dark:bg-slate-900/50">
                    <i class="fa-solid fa-file-signature text-3xl text-emerald-500/70 dark:text-emerald-400/70 mb-2 block"></i>
                    <input type="file" name="signature" id="signature_input" accept="image/png,image/jpeg,image/jpg" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950/70 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 cursor-pointer" required/>
                </div>
                @error('signature') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center mt-2">Disarankan format PNG transparan. Maksimal ukuran 2MB.</p>
            </form>

            @if($user->signature)
                <div class="pt-2 text-center border-t border-slate-100 dark:border-slate-700/80">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="delete_signature" value="1">
                        <button type="submit" class="inline-flex items-center space-x-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:text-rose-700 transition-colors bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 px-3.5 py-1.5 rounded-xl border border-rose-200/80 dark:border-rose-900/60 shadow-2xs active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i> <span>Hapus TTD Saat Ini</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-2.5 mt-6 justify-end border-t border-slate-100 dark:border-slate-700/80 pt-4">
            <button type="button" id="cancelModalSignatureBtn" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer">Batal</button>
            <button type="submit" form="uploadSignatureForm" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/25 transition-all cursor-pointer" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%) !important; color: #ffffff !important;">Simpan TTD</button>
        </div>
    </div>
</div>

{{-- MODAL HUBUNGI ADMIN UNTUK GANTI NOMOR WHATSAPP --}}
<div id="modalChangePhone" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-950/75 backdrop-blur-sm overflow-y-auto transition-opacity duration-200">
    <div class="relative w-full max-w-lg max-h-[85vh] sm:max-h-[88vh] flex flex-col bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden my-auto transform transition-all animate-fadeIn">
        
        {{-- Header Modal Hubungi Admin (shrink-0) --}}
        <div class="shrink-0 p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between gap-4 bg-slate-50 dark:bg-slate-900/60">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs border border-emerald-200/80 dark:border-emerald-800">
                    <i class="fa-brands fa-whatsapp text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-tight">
                        Ganti Nomor WhatsApp
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Hubungi Administrator untuk Perubahan Nomor Resmi
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeChangePhoneModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Body Modal Hubungi Admin (flex-1 overflow-y-auto) --}}
        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
            
            {{-- Alert Informasi Keamanan Nomor WhatsApp --}}
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3">
                <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 mt-0.5 text-base shrink-0"></i>
                <div class="leading-relaxed">
                    <p class="font-bold text-amber-950 dark:text-amber-100 mb-1">Nomor WhatsApp Terverifikasi Terkunci</p>
                    <p>
                        Nomor <span class="font-mono font-bold">{{ $user->phone_number }}</span> telah terdaftar dan terverifikasi untuk presensi absensi, notifikasi cuti, dan keamanan sistem ERP. Penggantian nomor harus disetujui dan diperbarui langsung oleh <strong>Administrator Sistem</strong>.
                    </p>
                </div>
            </div>

            {{-- Daftar Kontak Administrator Sistem --}}
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 px-1">
                    <span>Pilih Administrator Tujuan:</span>
                    <span class="text-[11px] text-slate-400 font-medium">{{ isset($adminUsers) ? $adminUsers->count() : 0 }} Admin Tersedia</span>
                </div>

                @if(isset($adminUsers) && $adminUsers->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($adminUsers as $admin)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $admin->phone_number);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                } elseif (str_starts_with($cleanPhone, '8')) {
                                    $cleanPhone = '62' . $cleanPhone;
                                }

                                $waText = "Halo Admin {$admin->name}, perkenalkan saya *" . ($user->name ?? 'Karyawan') . "* (NIP: *" . ($user->nip ?? '-') . "*).\n\n"
                                    . "Saya bermaksud mengajukan permohonan penggantian nomor WhatsApp akun ERP Umbulan saya:\n"
                                    . "• Nomor Terverifikasi Lama: *" . ($user->phone_number ?? '-') . "*\n"
                                    . "• Mohon bantuan dan verifikasi untuk nomor baru saya.\n\n"
                                    . "Terima kasih.";

                                $waUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waText);
                                $adminRoleName = $admin->role?->role_name ?? ($admin->roles->first()?->role_name ?? 'Administrator');
                            @endphp

                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/90 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-emerald-300 dark:hover:border-emerald-700 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-all shadow-2xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-extrabold text-xs flex items-center justify-center shrink-0 border border-emerald-300/70 dark:border-emerald-800 shadow-2xs">
                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                                                {{ $admin->name }}
                                            </h4>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300">
                                                {{ $adminRoleName }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="font-mono text-emerald-700 dark:text-emerald-400 font-semibold">
                                                <i class="fa-brands fa-whatsapp text-xs mr-0.5"></i> {{ $admin->phone_number }}
                                            </span>
                                            @if($admin->station)
                                                <span>•</span>
                                                <span class="truncate">{{ $admin->station->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 self-end sm:self-auto">
                                    <a href="{{ $waUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-all hover:scale-[1.02] cursor-pointer">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>Chat WhatsApp</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-80 ml-0.5"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center py-6">
                        <i class="fa-solid fa-user-shield text-3xl text-slate-400 mb-2"></i>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Kontak WhatsApp Administrator Belum Tersedia</p>
                        <p class="text-[11px] text-slate-400 mt-1">Silakan hubungi tim IT atau HRD di kantor stasiun Anda untuk bantuan penggantian nomor.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- Footer Modal Hubungi Admin (shrink-0) --}}
        <div class="shrink-0 p-4 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-700 flex justify-end">
            <button type="button" onclick="closeChangePhoneModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>

{{-- MODAL HUBUNGI ADMIN UNTUK PERUBAHAN JADWAL KERJA --}}
<div id="modalScheduleContactAdmin" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6 bg-slate-950/75 backdrop-blur-sm overflow-y-auto transition-opacity duration-200">
    <div class="relative w-full max-w-lg max-h-[85vh] sm:max-h-[88vh] flex flex-col bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden my-auto transform transition-all animate-fadeIn">
        
        {{-- Header Modal Hubungi Admin Jadwal (shrink-0) --}}
        <div class="shrink-0 p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between gap-4 bg-slate-50 dark:bg-slate-900/60">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs border border-emerald-200 dark:border-emerald-800">
                    <i class="fa-brands fa-whatsapp text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-tight">
                        Penyesuaian Jadwal Kerja
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Hubungi Administrator untuk Koreksi atau Perubahan Jadwal
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeScheduleContactAdminModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Body Modal Hubungi Admin Jadwal (flex-1 overflow-y-auto) --}}
        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
            
            {{-- Alert Informasi Keamanan Jadwal --}}
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3">
                <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 mt-0.5 text-base shrink-0"></i>
                <div class="leading-relaxed">
                    <p class="font-bold text-amber-950 dark:text-amber-100 mb-1">Pengaturan Jadwal Kerja Terkunci</p>
                    <p>
                        Jadwal kerja Anda saat ini (<span class="font-bold underline">
                            @if($user->schedule_type === 'roster')
                                Sistem Roster / Shift (12 Jam)
                            @elseif($user->schedule_type === 'reguler_6_hari')
                                Reguler (6 Hari: Sen–Sab)
                            @else
                                Reguler (5 Hari: Sen–Jum)
                            @endif
                        </span>) telah dikunci demi integritas absensi kedinasan. Apabila terjadi <strong>salah input</strong> atau <strong>pergantian shift dinas</strong>, silakan hubungi salah satu Administrator di bawah ini untuk pembaruan data resmi.
                    </p>
                </div>
            </div>

            {{-- Daftar Kontak Administrator Sistem --}}
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-200 px-1">
                    <span>Pilih Administrator Tujuan:</span>
                    <span class="text-[11px] text-slate-400 font-medium">{{ isset($adminUsers) ? $adminUsers->count() : 0 }} Admin Tersedia</span>
                </div>

                @if(isset($adminUsers) && $adminUsers->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($adminUsers as $admin)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $admin->phone_number);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                } elseif (str_starts_with($cleanPhone, '8')) {
                                    $cleanPhone = '62' . $cleanPhone;
                                }

                                $scheduleLabelForWa = match($user->schedule_type) {
                                    'roster' => 'Sistem Roster / Shift (12 Jam)',
                                    'reguler_6_hari' => 'Reguler (6 Hari: Sen–Sab)',
                                    'reguler_5_hari', 'normal' => 'Reguler (5 Hari: Sen–Jum)',
                                    default => 'Belum Diatur'
                                };

                                $waScheduleText = "Halo Admin {$admin->name}, perkenalkan saya *" . ($user->name ?? 'Karyawan') . "* (NIP: *" . ($user->nip ?? '-') . "*).\n\n"
                                    . "Saya bermaksud mengajukan permohonan penyesuaian/koreksi Jadwal Kerja pada akun ERP Umbulan saya:\n"
                                    . "• Jadwal Saat Ini: *" . $scheduleLabelForWa . "*\n"
                                    . "• Keterangan: (Mohon bantuan penyesuaian jadwal kerja karena salah input / mutasi shift dinas)\n\n"
                                    . "Mohon bantuan dan verifikasi penyesuaian jadwal akun saya. Terima kasih.";

                                $waScheduleUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waScheduleText);
                                $adminRoleName = $admin->role?->role_name ?? ($admin->roles->first()?->role_name ?? 'Administrator');
                            @endphp

                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/90 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all shadow-2xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-extrabold text-xs flex items-center justify-center shrink-0 border border-emerald-300/70 dark:border-emerald-800 shadow-2xs">
                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                                                {{ $admin->name }}
                                            </h4>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300">
                                                {{ $adminRoleName }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="font-mono text-emerald-700 dark:text-emerald-400 font-semibold">
                                                <i class="fa-brands fa-whatsapp text-xs mr-0.5"></i> {{ $admin->phone_number }}
                                            </span>
                                            @if($admin->station)
                                                <span>•</span>
                                                <span class="truncate">{{ $admin->station->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 self-end sm:self-auto">
                                    <a href="{{ $waScheduleUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs transition-all hover:scale-[1.02] cursor-pointer">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                        <span>Chat WhatsApp</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] opacity-80 ml-0.5"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-center py-6">
                        <i class="fa-solid fa-user-shield text-3xl text-slate-400 mb-2"></i>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Kontak WhatsApp Administrator Belum Tersedia</p>
                        <p class="text-[11px] text-slate-400 mt-1">Silakan hubungi tim IT atau HRD di kantor stasiun Anda untuk bantuan penyesuaian jadwal.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- Footer Modal Hubungi Admin Jadwal (shrink-0) --}}
        <div class="shrink-0 p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex justify-end">
            <button type="button" onclick="closeScheduleContactAdminModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>

{{-- MODAL PANDUAN CARA MENGETAHUI NIP DARI SLIP GAJI --}}
<div id="modalPanduanNip"
     class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 bg-slate-950/75 backdrop-blur-sm transition-opacity duration-200"
     style="position: fixed; inset: 0; z-index: 9999; margin: 0;">
    <div class="relative w-full max-w-lg max-h-[85vh] sm:max-h-[88vh] flex flex-col bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transition-all m-auto">
        
        {{-- Header Modal Panduan NIP (shrink-0) --}}
        <div class="shrink-0 p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between gap-4 bg-gradient-to-r from-sky-50/90 via-slate-50 to-white dark:from-slate-900/90 dark:via-slate-800/90 dark:to-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-100 dark:bg-sky-950/70 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 shadow-xs border border-sky-200/80 dark:border-sky-800">
                    <i class="fa-solid fa-id-card-clip text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-tight">
                            Cara Mengetahui NIP
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            Info HRD
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Panduan pengecekan Nomor Induk Pegawai resmi Anda
                    </p>
                </div>
            </div>
            <button type="button" onclick="tutupModalPanduanNip()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Body Modal Panduan NIP (flex-1 overflow-y-auto) --}}
        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
            
            {{-- Alert Informasi Ringkas --}}
            <div class="p-3.5 rounded-2xl bg-sky-50/80 dark:bg-sky-950/40 border border-sky-200/80 dark:border-sky-800 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                <div class="font-bold text-sky-900 dark:text-sky-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-sky-500"></i>
                    <span>Tercantum pada Dokumen Slip Gaji</span>
                </div>
                <p class="text-[11.5px] leading-relaxed text-slate-600 dark:text-slate-300">
                    Nomor Induk Pegawai (NIP) resmi Anda dapat dilihat langsung pada berkas <strong>Slip Gaji bulanan</strong> yang dikirimkan oleh <strong>HRD ke email resmi</strong> masing-masing karyawan.
                </p>
            </div>

            {{-- 3 Tahapan Cepat --}}
            <div class="space-y-2.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                    3 Langkah Menemukan NIP Anda:
                </span>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 text-xs">
                    <div class="w-6 h-6 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        1
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-200">Buka Email dari HRD</p>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 leading-relaxed">
                            Cek kotak masuk (<em>Inbox</em>) email Anda dan cari pesan email dari HRD dengan perihal pengiriman <em>Slip Gaji / Payslip</em>.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 text-xs">
                    <div class="w-6 h-6 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        2
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 dark:text-slate-200">Unduh & Buka Berkas Slip Gaji</p>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5 leading-relaxed">
                            Buka lampiran dokumen slip gaji (PDF) yang dikirimkan oleh HRD.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-sky-500/10 dark:bg-sky-950/40 border-2 border-sky-400/80 dark:border-sky-600/80 text-xs shadow-2xs">
                    <div class="w-6 h-6 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        3
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-sky-900 dark:text-sky-200">Periksa di Bagian Sebelah Kiri Atas</span>
                            <span class="px-1.5 py-0.2 rounded bg-sky-600 text-white text-[9px] font-bold uppercase tracking-wider">Lokasi NIP</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 text-[11px] mt-0.5 leading-relaxed">
                            Pada formulir slip gaji, perhatikan kolom identitas pegawai di <strong>sebelah kiri atas</strong>. NIP Anda tertera di baris identitas tersebut.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Mockup Visual Letak NIP pada Slip Gaji --}}
            <div class="p-3.5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700/80 space-y-2">
                <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                    <span class="font-bold uppercase tracking-wider flex items-center gap-1">
                        <i class="fa-solid fa-file-invoice-dollar text-sky-500"></i> Ilustrasi Tata Letak Slip Gaji
                    </span>
                    <span class="font-mono text-[9px] bg-slate-200 dark:bg-slate-800 px-1.5 py-0.5 rounded">Dokumen Resmi HRD</span>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-3 border border-slate-200 dark:border-slate-700 space-y-2.5 shadow-2xs">
                    {{-- Header Slip Mockup --}}
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/70 pb-2">
                        <span class="text-[10px] font-black text-slate-800 dark:text-slate-100 tracking-tight">PT META ADHYA TIRTA UMBULAN</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase font-mono">SLIP GAJI BULANAN</span>
                    </div>

                    {{-- Baris Identitas Mockup --}}
                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                        {{-- KIRI ATAS: NIP HIGHLIGHTED --}}
                        <div class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/70 border-2 border-sky-400 dark:border-sky-500 relative ring-2 ring-sky-200/50 dark:ring-sky-900/30">
                            <span class="absolute -top-2 right-1.5 px-1.5 py-0.2 rounded-full bg-sky-600 text-white text-[8px] font-bold uppercase tracking-wider shadow-2xs flex items-center gap-0.5">
                                <i class="fa-solid fa-arrow-down text-[7px]"></i> Kiri Atas
                            </span>
                            <div class="space-y-0.5">
                                <div class="text-[9px] font-bold text-sky-700 dark:text-sky-300 uppercase tracking-wide">Nomor Induk Pegawai:</div>
                                <div class="font-mono font-black text-sky-800 dark:text-sky-200 text-xs">NIP: KRY-2024-XXX</div>
                                <div class="text-[9px] text-slate-500 dark:text-slate-400">Nama: {{ $user->name }}</div>
                            </div>
                        </div>

                        {{-- Kanan Atas: Data Lain --}}
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 flex flex-col justify-center">
                            <div class="text-[9px] text-slate-600 dark:text-slate-400 uppercase font-semibold">Periode Gaji:</div>
                            <div class="font-mono text-slate-900 dark:text-white text-[10px] font-bold">
                                {{ (\Carbon\Carbon::now()->day < 25 ? \Carbon\Carbon::now()->subMonth() : \Carbon\Carbon::now())->translatedFormat('F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catatan Hubungi HRD --}}
            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/50 text-[11px] text-amber-800 dark:text-amber-300">
                <i class="fa-solid fa-lightbulb text-amber-500 shrink-0"></i>
                <span>Belum menerima email slip gaji atau NIP belum tercantum? Silakan hubungi tim <strong>HRD / Kepegawaian</strong> untuk klarifikasi data Anda.</span>
            </div>

        </div>

        {{-- Footer Modal Panduan NIP (shrink-0) --}}
        <div class="shrink-0 p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex justify-end">
            <button type="button" onclick="tutupModalPanduanNip()"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-blue-600/20 flex items-center gap-1.5 cursor-pointer"
                    style="background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%) !important; color: #ffffff !important;">
                <i class="fa-solid fa-check text-white" style="color: #ffffff !important;"></i>
                <span class="text-white" style="color: #ffffff !important;">Mengerti, Tutup Panduan</span>
            </button>
        </div>

    </div>
</div>
@endpush

@push('scripts')
<script>
    function openChangePhoneModal() {
        const modal = document.getElementById('modalChangePhone');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeChangePhoneModal() {
        const modal = document.getElementById('modalChangePhone');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    function openScheduleContactAdminModal() {
        const modal = document.getElementById('modalScheduleContactAdmin');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeScheduleContactAdminModal() {
        const modal = document.getElementById('modalScheduleContactAdmin');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChangePhoneModal();
            closeScheduleContactAdminModal();
        }
    });

    document.addEventListener('click', function(e) {
        const modalPhone = document.getElementById('modalChangePhone');
        if (modalPhone && e.target === modalPhone) {
            closeChangePhoneModal();
        }
        const modalSchedule = document.getElementById('modalScheduleContactAdmin');
        if (modalSchedule && e.target === modalSchedule) {
            closeScheduleContactAdminModal();
        }
    });

    function showScheduleEditMode() {
        const readMode = document.getElementById('schedule_read_mode');
        const editMode = document.getElementById('schedule_edit_mode');
        if (readMode) readMode.classList.add('hidden');
        if (editMode) {
            editMode.classList.remove('hidden');
            toggleScheduleOptions();
        }
    }

    function cancelScheduleEditMode() {
        const readMode = document.getElementById('schedule_read_mode');
        const editMode = document.getElementById('schedule_edit_mode');
        if (readMode) readMode.classList.remove('hidden');
        if (editMode) editMode.classList.add('hidden');
    }

    function toggleScheduleOptions() {
        const scheduleType = document.getElementById('schedule_type')?.value;
        const sectionReguler5 = document.getElementById('section_reguler_5_info');
        const sectionReguler6 = document.getElementById('section_reguler_6_info');
        const sectionRoster = document.getElementById('section_roster_schedule');

        if (!scheduleType) return;

        if (sectionReguler5) sectionReguler5.classList.add('hidden');
        if (sectionReguler6) sectionReguler6.classList.add('hidden');
        if (sectionRoster) sectionRoster.classList.add('hidden');

        if (scheduleType === 'reguler_6_hari') {
            if (sectionReguler6) sectionReguler6.classList.remove('hidden');
        } else if (scheduleType === 'roster') {
            if (sectionRoster) sectionRoster.classList.remove('hidden');
        } else {
            // Default: reguler_5_hari atau fallback normal
            if (sectionReguler5) sectionReguler5.classList.remove('hidden');
        }
    }

    function calculateRosterAnchor(selectedShift) {
        const now = new Date();
        const day = now.getDay();

        let daysSinceTuesday = (day - 2 + 7) % 7;
        let currentTuesday = new Date(now);
        currentTuesday.setDate(now.getDate() - daysSinceTuesday);
        currentTuesday.setHours(7, 0, 0, 0);

        if (day === 2 && now.getHours() < 7) {
            currentTuesday.setDate(currentTuesday.getDate() - 7);
        }

        if (selectedShift === 'malam') {
            currentTuesday.setDate(currentTuesday.getDate() - 7);
        } else if (selectedShift === 'libur') {
            currentTuesday.setDate(currentTuesday.getDate() - 14);
        }

        const year = currentTuesday.getFullYear();
        const month = String(currentTuesday.getMonth() + 1).padStart(2, '0');
        const date = String(currentTuesday.getDate()).padStart(2, '0');

        const input = document.getElementById('roster_start_date_input');
        if (input) {
            input.value = `${year}-${month}-${date}`;
        }

        if (typeof updateRosterPreviewUI === 'function') {
            updateRosterPreviewUI(selectedShift);
        }
    }

    function updateRosterPreviewUI(selectedShift) {
        const previewBox = document.getElementById('roster_preview_box');
        const w1 = document.getElementById('preview_week_1');
        const w2 = document.getElementById('preview_week_2');
        const w3 = document.getElementById('preview_week_3');

        if (!previewBox || !w1 || !w2 || !w3) return;

        previewBox.classList.remove('hidden');

        if (selectedShift === 'pagi') {
            w1.innerText = "Shift Pagi"; w1.className = "font-bold text-emerald-600";
            w2.innerText = "Shift Malam"; w2.className = "font-bold text-indigo-600";
            w3.innerText = "Minggu Libur"; w3.className = "font-bold text-red-600";
        } else if (selectedShift === 'malam') {
            w1.innerText = "Shift Malam"; w1.className = "font-bold text-indigo-600";
            w2.innerText = "Minggu Libur"; w2.className = "font-bold text-red-600";
            w3.innerText = "Shift Pagi"; w3.className = "font-bold text-emerald-600";
        } else {
            w1.innerText = "Minggu Libur"; w1.className = "font-bold text-red-600";
            w2.innerText = "Shift Pagi"; w2.className = "font-bold text-emerald-600";
            w3.innerText = "Shift Malam"; w3.className = "font-bold text-indigo-600";
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('schedule_required') === '1' || window.location.hash === '#schedule_setting') {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);

            setTimeout(function () {
                const scheduleSection = document.getElementById("schedule_setting");
                const scheduleSelect = document.getElementById("schedule_type");

                if (scheduleSection) {
                    scheduleSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });

                    scheduleSection.classList.add("ring-4", "ring-amber-400", "bg-amber-50/40", "border-amber-300");
                    if (scheduleSelect) scheduleSelect.focus();

                    setTimeout(() => {
                        scheduleSection.classList.remove("ring-4", "ring-amber-400", "bg-amber-50/40", "border-amber-300");
                    }, 3500);
                }
            }, 300);
        }

        if (urlParams.get('phone_required') === '1' || window.location.hash === '#phone_number') {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);

            setTimeout(function () {
                const phoneInput = document.getElementById("phone_number");

                if (phoneInput) {
                    phoneInput.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });

                    if (!phoneInput.readOnly) {
                        phoneInput.focus();
                        phoneInput.classList.add("ring-4", "ring-rose-400", "border-rose-500", "animate-pulse");

                        phoneInput.addEventListener('input', function () {
                            phoneInput.classList.remove("ring-4", "ring-rose-400", "border-rose-500", "animate-pulse");
                        }, { once: true });
                    }
                }
            }, 300);
        }

        toggleScheduleOptions();

        const selectedRadio = document.querySelector('input[name="current_shift_choice"]:checked');
        if (selectedRadio) {
            updateRosterPreviewUI(selectedRadio.value);
        }

        const modalPhoto = document.getElementById("photoModal");
        const openPhotoBtn = document.getElementById("openModalPhotoBtn");
        const closePhotoBtn = document.getElementById("closeModalPhotoBtn");
        const cancelPhotoBtn = document.getElementById("cancelModalPhotoBtn");
        const backdropPhoto = document.getElementById("modalBackdrop");

        function showPhotoModal() {
            if (modalPhoto) {
                if (modalPhoto.parentElement !== document.body) {
                    document.body.appendChild(modalPhoto);
                }
                modalPhoto.classList.remove("hidden");
                modalPhoto.classList.add("flex");
                document.body.classList.add("overflow-hidden");
            }
        }
        function hidePhotoModal() {
            if (modalPhoto) {
                modalPhoto.classList.remove("flex");
                modalPhoto.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            }
        }

        if (openPhotoBtn) openPhotoBtn.addEventListener("click", showPhotoModal);
        if (closePhotoBtn) closePhotoBtn.addEventListener("click", hidePhotoModal);
        if (cancelPhotoBtn) cancelPhotoBtn.addEventListener("click", hidePhotoModal);
        if (backdropPhoto) backdropPhoto.addEventListener("click", hidePhotoModal);

        if (modalPhoto && modalPhoto.dataset.hasError === 'true') {
            showPhotoModal();
        }

        const modalSig = document.getElementById("signatureModal");
        const openSigBtn = document.getElementById("openModalSignatureBtn");
        const closeSigBtn = document.getElementById("closeModalSignatureBtn");
        const cancelSigBtn = document.getElementById("cancelModalSignatureBtn");
        const backdropSig = document.getElementById("modalSignatureBackdrop");

        function showSigModal() {
            if (modalSig) {
                if (modalSig.parentElement !== document.body) {
                    document.body.appendChild(modalSig);
                }
                modalSig.classList.remove("hidden");
                modalSig.classList.add("flex");
                document.body.classList.add("overflow-hidden");
            }
        }
        function hideSigModal() {
            if (modalSig) {
                modalSig.classList.remove("flex");
                modalSig.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            }
        }

        if (openSigBtn) openSigBtn.addEventListener("click", showSigModal);
        if (closeSigBtn) closeSigBtn.addEventListener("click", hideSigModal);
        if (cancelSigBtn) cancelSigBtn.addEventListener("click", hideSigModal);
        if (backdropSig) backdropSig.addEventListener("click", hideSigModal);

        if (modalSig && modalSig.dataset.hasError === 'true') {
            showSigModal();
        }

        const urlParamsSig = new URLSearchParams(window.location.search);
        if (urlParamsSig.get('signature_required') === '1' || window.location.hash === '#signature' || window.location.hash === '#signatureModal') {
            showSigModal();
        }
    });

    // ==========================================
    // RUMAH METER HANDLER (ROLE PIPELINE)
    // ==========================================
    function selectAllPengaturanRumahMeter(selectAll = true) {
        document.querySelectorAll('.pengaturan-rm-checkbox').forEach(cb => {
            cb.checked = selectAll;
        });
    }

    // ==========================================
    // PERAN / JABATAN SELECT DROPDOWN HANDLER
    // ==========================================
    function toggleRoleDropdown(forceState = null) {
        const panel = document.getElementById('roleDropdownPanel');
        const chevron = document.getElementById('roleChevronIcon');
        const searchInput = document.getElementById('roleSearchInput');

        if (!panel) return;

        const isCurrentlyOpen = !panel.classList.contains('hidden');
        const shouldOpen = forceState !== null ? forceState : !isCurrentlyOpen;

        if (shouldOpen) {
            panel.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 50);
            }
        } else {
            panel.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function onRoleSelectionChanged() {
        const checkedBoxes = Array.from(document.querySelectorAll('.role-checkbox-item:checked'));
        const displayText = document.getElementById('roleSelectText');
        const countBadge = document.getElementById('roleCountBadge');
        const primaryHidden = document.getElementById('primary_role_id');

        // Update highlight baris yang dicentang
        document.querySelectorAll('.role-list-item').forEach(item => {
            const cb = item.querySelector('.role-checkbox-item');
            if (cb && cb.checked) {
                item.classList.add('bg-sky-50/70', 'dark:bg-sky-950/30');
            } else {
                item.classList.remove('bg-sky-50/70', 'dark:bg-sky-950/30');
            }
        });

        if (checkedBoxes.length > 0) {
            const names = checkedBoxes.map(cb => cb.getAttribute('data-name') || cb.value);
            if (displayText) {
                displayText.innerText = names.join(', ');
                displayText.classList.remove('text-slate-400');
                displayText.classList.add('text-slate-800', 'dark:text-slate-100', 'font-medium');
            }
            if (countBadge) {
                if (checkedBoxes.length > 1) {
                    countBadge.innerText = checkedBoxes.length;
                    countBadge.classList.remove('hidden');
                } else {
                    countBadge.classList.add('hidden');
                }
            }
            if (primaryHidden) {
                primaryHidden.value = checkedBoxes[0].value;
            }
        } else {
            if (displayText) {
                displayText.innerText = 'Pilih satu atau beberapa jabatan...';
                displayText.classList.add('text-slate-400');
                displayText.classList.remove('text-slate-800', 'dark:text-slate-100', 'font-medium');
            }
            if (countBadge) countBadge.classList.add('hidden');
            if (primaryHidden) primaryHidden.value = '';
        }

        // Sinkronisasi container Rumah Meter (Role AREA PIPELINE)
        const isPipelineSelected = checkedBoxes.some(cb => {
            const label = (cb.getAttribute('data-name') || '').toLowerCase();
            return label.includes('pipeline') || cb.value === '14';
        });

        const rmContainer = document.getElementById('pengaturanPipelineRumahMeterContainer');
        if (rmContainer) {
            if (isPipelineSelected) {
                rmContainer.classList.remove('hidden');
            } else {
                rmContainer.classList.add('hidden');
                document.querySelectorAll('.pengaturan-rm-checkbox').forEach(c => c.checked = false);
            }
        }
    }

    function filterRoleList(query) {
        const cleanQuery = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.role-list-item');
        const noFound = document.getElementById('noRoleFoundMsg');
        const clearBtn = document.getElementById('clearRoleSearchBtn');
        const summary = document.getElementById('roleFilterSummary');

        if (clearBtn) {
            if (cleanQuery.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        let visibleCount = 0;
        items.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            if (name.includes(cleanQuery)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        if (noFound) {
            if (visibleCount === 0) {
                noFound.classList.remove('hidden');
            } else {
                noFound.classList.add('hidden');
            }
        }

        if (summary) {
            summary.innerText = cleanQuery.length > 0
                ? `Ditemukan: ${visibleCount} jabatan`
                : 'Daftar Jabatan:';
        }
    }

    function clearRoleSearch() {
        const input = document.getElementById('roleSearchInput');
        if (input) {
            input.value = '';
            filterRoleList('');
            input.focus();
        }
    }

    function resetRoleSelection() {
        document.querySelectorAll('.role-checkbox-item:checked').forEach(cb => cb.checked = false);
        onRoleSelectionChanged();
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('roleSelectWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            toggleRoleDropdown(false);
        }
    });

    // Indikator loading & proteksi double submit form profil
    const formProfile = document.getElementById('form-profile-account');
    const btnSubmitProfile = document.getElementById('btnSubmitProfile');
    const btnSubmitProfileIcon = document.getElementById('btnSubmitProfileIcon');
    const btnSubmitProfileSpinner = document.getElementById('btnSubmitProfileSpinner');
    const btnSubmitProfileText = document.getElementById('btnSubmitProfileText');

    if (formProfile && btnSubmitProfile) {
        formProfile.addEventListener('submit', function() {
            btnSubmitProfile.disabled = true;
            if (btnSubmitProfileIcon) btnSubmitProfileIcon.classList.add('hidden');
            if (btnSubmitProfileSpinner) btnSubmitProfileSpinner.classList.remove('hidden');
            if (btnSubmitProfileText) btnSubmitProfileText.innerText = 'Menyimpan...';
        });
    }

    document.addEventListener('turbo:submit-end', function() {
        if (btnSubmitProfile) {
            btnSubmitProfile.disabled = false;
            if (btnSubmitProfileIcon) btnSubmitProfileIcon.classList.remove('hidden');
            if (btnSubmitProfileSpinner) btnSubmitProfileSpinner.classList.add('hidden');
            if (btnSubmitProfileText) btnSubmitProfileText.innerText = 'Simpan Perubahan';
        }
    });

    // === PANDUAN CARA MENGETAHUI NIP ===
    function bukaModalPanduanNip() {
        const modal = document.getElementById('modalPanduanNip');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function tutupModalPanduanNip() {
        const modal = document.getElementById('modalPanduanNip');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    // Event listener backdrop modal panduan NIP
    const modalPanduanNip = document.getElementById('modalPanduanNip');
    if (modalPanduanNip) {
        modalPanduanNip.addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModalPanduanNip();
            }
        });
    }

    // Event listener Escape key untuk menutup modal panduan NIP
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            tutupModalPanduanNip();
        }
    });
</script>
@endpush

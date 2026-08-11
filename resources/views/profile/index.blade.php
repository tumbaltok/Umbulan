@extends('layouts.app')
@section('title', 'Pengaturan Profil Karyawan')
@section('content')
<div class="max-w-4xl mx-auto mt-8 px-4">
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-check mr-2 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Pengaturan Akun & Keamanan</h2>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui informasi profil, tanda tangan digital, dan amankan akun dengan kombinasi password baru.</p>
        </div>

        {{-- Form data umum, jadwal kerja, & keamanan --}}
        <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Profil</h3>

                {{-- Container Foto Profil & TTD --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b border-slate-100 pb-6">
                    {{-- Foto Profil --}}
                    <div class="flex flex-col items-center justify-center text-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                        <div class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden border-2 border-white ring-2 ring-sky-100">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>

                        <button type="button" id="openModalPhotoBtn" class="mt-3 text-xs font-bold text-sky-600 hover:text-sky-700 transition-colors flex items-center space-x-1">
                            <i class="fa-solid fa-camera"></i>
                            <span>Ubah Foto Profil</span>
                        </button>
                    </div>

                    {{-- Tanda Tangan Digital (TTD) --}}
                    <div class="flex flex-col items-center justify-center text-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                        <div class="w-36 h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden p-2 shadow-inner">
                            @if($user->signature)
                                <img src="{{ asset('storage/' . $user->signature) }}" alt="Tanda Tangan" class="max-w-full max-h-full object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">Belum Ada TTD</span>
                            @endif
                        </div>

                        <button type="button" id="openModalSignatureBtn" class="mt-3 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center space-x-1">
                            <i class="fa-solid fa-file-signature"></i>
                            <span>Unggah Tanda Tangan (TTD)</span>
                        </button>
                    </div>
                </div>

                {{-- Grid untuk Form Data Profil --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- NIP --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIP</label>
                        <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('nip') ? 'border-rose-500' : 'border-slate-200' }}">
                        @error('nip') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-200' }}" required>
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label for="gender_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin</label>
                        <select id="gender_id" name="gender_id" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('gender_id') ? 'border-rose-500' : 'border-slate-200' }}">
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
                                <option value="1" {{ (string)$userGenderValue === '1' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="2" {{ (string)$userGenderValue === '2' ? 'selected' : '' }}>Perempuan</option>
                            @endif
                        </select>
                        @error('gender_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Sektor Kerja --}}
                    <div>
                        <label for="sektor" class="block text-sm font-semibold text-slate-700 mb-1.5">Sektor Kerja</label>
                        <select id="sektor" name="sektor" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('sektor') ? 'border-rose-500' : 'border-slate-200' }}">
                            @php
                                $userSektorValue = old('sektor', $user->sektor ?? '');
                            @endphp
                            <option value="" disabled {{ empty($userSektorValue) ? 'selected' : '' }}>Pilih Sektor Kerja</option>
                            @if(isset($daftarSektor) && count($daftarSektor) > 0)
                                @foreach($daftarSektor as $sektorItem)
                                    @php 
                                        $sektorVal = is_object($sektorItem) ? ($sektorItem->value ?? $sektorItem->name) : $sektorItem; 
                                    @endphp
                                    <option value="{{ $sektorVal }}" {{ strtolower($userSektorValue) === strtolower($sektorVal) ? 'selected' : '' }}>
                                        {{ ucfirst($sektorVal) }}
                                    </option>
                                @endforeach
                            @else
                                <option value="operasional" {{ strtolower($userSektorValue) === 'operasional' ? 'selected' : '' }}>Operasional</option>
                                <option value="manajemen" {{ strtolower($userSektorValue) === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                            @endif
                        </select>
                        @error('sektor') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Penempatan Kerja / Stasiun --}}
                    <div>
                        <label for="station_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Penempatan Kerja</label>
                        <select id="station_id" name="station_id" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('station_id') ? 'border-rose-500' : 'border-slate-200' }}">
                            @php
                                $userStationValue = old('station_id', $user->station_id ?? '');
                            @endphp
                            <option value="" disabled {{ empty($userStationValue) ? 'selected' : '' }}>Pilih Tempat Kerja</option>
                            @if(isset($daftarStasiun) && count($daftarStasiun) > 0)
                                @foreach($daftarStasiun as $stasiun)
                                    <option value="{{ $stasiun->id }}" {{ (string)$userStationValue === (string)$stasiun->id ? 'selected' : '' }}>
                                        {{ $stasiun->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('station_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jabatan / Role --}}
                    <div>
                        <label for="role_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Jabatan / Peran</label>
                        
                        @php
                            $isAdmin = ($user->role && strtolower($user->role->role_name) === 'admin') || $user->role_id == 1;
                            $userRoleValue = old('role_id', $user->role_id ?? '');
                        @endphp

                        @if($isAdmin)
                            <div class="relative">
                                <input type="text" 
                                    value="Admin (Akses Penuh System)" 
                                    class="w-full px-4 py-2 border border-slate-200 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm cursor-not-allowed select-none" 
                                    readonly 
                                    disabled>
                                <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">* Role Admin terkunci demi keamanan sistem.</p>
                        @else
                            <select id="role_id" name="role_id" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('role_id') ? 'border-rose-500' : 'border-slate-200' }}">
                                <option value="" disabled {{ empty($userRoleValue) ? 'selected' : '' }}>Pilih Peran Jabatan</option>
                                @if(isset($daftarRole) && count($daftarRole) > 0)
                                    @foreach($daftarRole as $role)
                                        <option value="{{ $role->id }}" {{ (string)$userRoleValue === (string)$role->id ? 'selected' : '' }}>
                                            {{ $role->role_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('role_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @endif
                    </div>

                    {{-- Jobdesk --}}
                    <div>
                        <label for="jobdesk" class="block text-sm font-semibold text-slate-700 mb-1.5">Jobdesk / Bidang Tugas</label>
                        <select id="jobdesk" name="jobdesk" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('jobdesk') ? 'border-rose-500' : 'border-slate-200' }}">
                            @php
                                $userJobValue = old('jobdesk', $user->job_title ?? $user->jobdesk ?? '');
                                $selectedClean = strtolower(trim((string)$userJobValue));
                            @endphp
                            <option value="" disabled {{ $selectedClean === '' ? 'selected' : '' }}>Pilih Jobdesk / Bidang</option>
                            @if(isset($daftarJobdesk) && count($daftarJobdesk) > 0)
                                @foreach($daftarJobdesk as $jd)
                                    @php 
                                        $namaJobdesk = $jd->job_title ?? $jd->name ?? $jd->nama_jobdesk ?? $jd->jobdesk ?? ''; 
                                        $namaClean = strtolower(trim((string)$namaJobdesk));
                                        $idClean = strtolower(trim((string)$jd->id));
                                        $isSelected = ($selectedClean === $namaClean) || ($selectedClean === $idClean);
                                    @endphp
                                    <option value="{{ $namaJobdesk }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $namaJobdesk }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('jobdesk') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Alamat Email --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                            <div>
                                @if($user->email_verified_at)
                                    <span id="email-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Terverifikasi
                                    </span>
                                @else
                                    <span id="email-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="relative">
                            <input type="email" 
                                value="{{ $user->email }}"
                                class="w-full px-4 py-2 border border-slate-200 bg-slate-100 text-slate-500 rounded-xl cursor-not-allowed select-none font-medium"
                                readonly 
                                disabled>
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">* Email akun tidak dapat diubah.</p>
                    </div>

                    {{-- No. Telephone --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-semibold text-slate-700">No. Telephone</label>
                            <div class="flex items-center space-x-2">
                                @if($user->phone_verified_at)
                                    <span id="phone-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Terverifikasi
                                    </span>
                                    <button type="button" id="btn-change-phone" onclick="enableEditPhone()" class="text-xs font-bold text-sky-600 hover:underline">
                                        Ganti
                                    </button>
                                @else
                                    <span id="phone-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="text"
                                    name="phone_number"
                                    id="phone_number"
                                    value="{{ old('phone_number', $user->phone_number ?? '') }}"
                                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 transition-all {{ $user->phone_verified_at ? 'border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed select-none' : 'border-slate-200' }}"
                                    placeholder="Contoh: 08123456789"
                                    {{ $user->phone_verified_at ? 'readonly' : '' }}>
                            </div>

                            <button type="button"
                                id="btn-send-otp"
                                class="px-4 py-2 bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200 hover:border-sky-200 rounded-xl text-sm font-semibold shadow-sm transition-all whitespace-nowrap h-[42px] flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed {{ $user->phone_verified_at ? 'hidden' : '' }}">
                                <i class="fa-solid fa-shield-halved mr-1.5 text-xs text-slate-400"></i>
                                Verifikasi
                            </button>
                        </div>
                        <span id="phone-error" class="text-xs text-rose-500 mt-1 hidden"></span>
                    </div>

                    {{-- Container OTP --}}
                    <div id="otp-container" class="hidden animate-fadeIn">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Masukkan 6 Digit Kode OTP</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="text"
                                    id="otp_input"
                                    maxlength="6"
                                    class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 tracking-[0.5em] text-center font-bold text-lg"
                                    placeholder="******">
                            </div>
                            <button type="button"
                                id="btn-verify-otp"
                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-400 disabled:opacity-70 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold shadow-sm transition-all h-[42px]">
                                Konfirmasi
                            </button>
                        </div>
                        <span id="otp-message" class="text-xs mt-1 block"></span>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            {{-- PENGATURAN JADWAL KERJA --}}
            <div id="schedule_setting" class="p-4 rounded-2xl transition-all duration-300">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Pengaturan Jadwal Kerja</h3>
                <p class="text-xs text-slate-400 mb-4">Pilih jenis jadwal kerja yang berlaku untuk akun Anda (Normal atau Roster).</p>

                @php
                    $activeScheduleType = old('schedule_type', $user->schedule_type ?? '');
                    
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

                <div class="space-y-4">
                    {{-- Pilihan Jenis Jadwal --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Jadwal Kerja</label>
                        <select id="schedule_type" name="schedule_type" onchange="toggleScheduleOptions()" class="w-full md:w-1/2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all">
                            <option value="" disabled {{ empty($activeScheduleType) ? 'selected' : '' }}>-- Pilih Jenis Jadwal --</option>
                            <option value="normal" {{ $activeScheduleType === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="roster" {{ $activeScheduleType === 'roster' ? 'selected' : '' }}>Roster/Shift</option>
                        </select>
                    </div>

                    {{-- Form Opsi Jadwal Normal --}}
                    <div id="section_normal_schedule" class="{{ $activeScheduleType === 'normal' ? '' : 'hidden' }} p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih Hari Kerja</label>
                        @php
                            $workDays = old('normal_work_days', $user->normal_work_days ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
                        @endphp
                        <div class="flex flex-wrap gap-3">
                            @foreach(['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'] as $key => $dayLabel)
                                <label class="inline-flex items-center space-x-1.5 text-xs font-semibold text-slate-700 bg-white px-3 py-1.5 rounded-lg border border-slate-200 cursor-pointer">
                                    <input type="checkbox" name="normal_work_days[]" value="{{ $key }}" {{ in_array($key, (array)$workDays) ? 'checked' : '' }} class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    <span>{{ $dayLabel }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk</label>
                                <input type="time" name="normal_check_in" value="{{ old('normal_check_in', $user->normal_check_in ?? '08:00') }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang</label>
                                <input type="time" name="normal_check_out" value="{{ old('normal_check_out', $user->normal_check_out ?? '17:00') }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs">
                            </div>
                        </div>
                    </div>

                    {{-- Form Opsi Jadwal Roster --}}
                    <div id="section_roster_schedule" class="{{ $activeScheduleType === 'roster' ? '' : 'hidden' }} p-4 bg-amber-50/50 border border-amber-200 rounded-2xl space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-amber-900 uppercase tracking-wider mb-1">Shift Anda Saat Ini</label>
                            <p class="text-[11px] text-amber-700 mb-3">Sistem akan secara otomatis menghitung dan memutar jadwal rotasi shift Anda setiap hari Selasa pukul 07:00 WIB.</p>
                            
                            <input type="hidden" id="roster_start_date_input" name="roster_start_date" value="{{ old('roster_start_date', $user->roster_start_date ? \Carbon\Carbon::parse($user->roster_start_date)->format('Y-m-d') : '') }}">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex items-center space-x-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 transition-all">
                                    <input type="radio" name="current_shift_choice" value="pagi" {{ $selectedRosterShift === 'pagi' ? 'checked' : '' }} onchange="calculateRosterAnchor('pagi')" class="text-sky-600 focus:ring-sky-500">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">Shift Pagi</span>
                                        <span class="text-[10px] text-slate-500">07:00 - 19:00 WIB</span>
                                    </div>
                                </label>
                                <label class="flex items-center space-x-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-500 transition-all">
                                    <input type="radio" name="current_shift_choice" value="malam" {{ $selectedRosterShift === 'malam' ? 'checked' : '' }} onchange="calculateRosterAnchor('malam')" class="text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">Shift Malam</span>
                                        <span class="text-[10px] text-slate-500">19:00 - 07:00 WIB</span>
                                    </div>
                                </label>
                                <label class="flex items-center space-x-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-red-500 transition-all">
                                    <input type="radio" name="current_shift_choice" value="libur" {{ $selectedRosterShift === 'libur' ? 'checked' : '' }} onchange="calculateRosterAnchor('libur')" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">OFF</span>
                                        <span class="text-[10px] text-slate-500">OFF / Libur</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- BOX PRATINJAU HASIL ROTASI --}}
                        <div id="roster_preview_box" class="p-3 bg-white border border-amber-300 rounded-xl shadow-sm text-xs space-y-2">
                            <div class="font-bold text-amber-900 border-b border-slate-100 pb-1.5 flex items-center">
                                <i class="fa-solid fa-eye text-amber-600 mr-2"></i> Pratinjau Jadwal Rotasi Roster Anda:
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 text-slate-700">
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <span class="block text-[10px] text-slate-400 font-semibold">MINGGU INI:</span>
                                    <span id="preview_week_1" class="font-bold text-sky-600">Shift Pagi</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <span class="block text-[10px] text-slate-400 font-semibold">SELASA DEPAN (07:00 WIB):</span>
                                    <span id="preview_week_2" class="font-bold text-indigo-600">Shift Malam</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <span class="block text-[10px] text-slate-400 font-semibold">2 MINGGU LAGI:</span>
                                    <span id="preview_week_3" class="font-bold text-emerald-600">Minggu Libur</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Keamanan Akun</h3>
                <p class="text-xs text-slate-400 mb-4">Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password akun.</p>

                <div class="space-y-4">
                    {{-- Password Lama --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Saat Ini (Password Lama)</label>
                        <input type="password" name="current_password" class="w-full md:w-1/2 px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('current_password') ? 'border-rose-500' : 'border-slate-200' }}" placeholder="••••••••">
                        @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password Baru --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('new_password') ? 'border-rose-500' : 'border-slate-200' }}" placeholder="Minimal 8 karakter">
                            @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL FOTO PROFIL --}}
<div id="photoModal" 
     data-has-error="{{ $errors->has('profile_photo') ? 'true' : 'false' }}" 
     class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="modalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 transform transition-all m-4">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Perbarui Foto Profil</h3>
            <button type="button" id="closeModalPhotoBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-4">
            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" id="uploadPhotoForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-sky-400 transition-colors bg-slate-50/50">
                    <input type="file" name="profile_photo" id="profile_photo_input" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" required/>
                </div>
                @error('profile_photo') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 text-center mt-2">Format file: JPG, JPEG, PNG. Maksimal 2MB.</p>
            </form>

            @if($user->profile_photo)
                <div class="pt-2 text-center border-t border-slate-100">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="delete_photo" value="1">
                        <button type="submit" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors bg-rose-50 hover:bg-rose-100/70 px-3 py-1.5 rounded-lg border border-rose-200/40">
                            <i class="fa-solid fa-trash-can"></i> <span>Hapus Foto Saat Ini</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-3 mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="cancelModalPhotoBtn" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">Batal</button>
            <button type="submit" form="uploadPhotoForm" class="px-4 py-2 bg-sky-600 text-white rounded-xl text-sm font-medium hover:bg-sky-700 transition-colors shadow-sm">Simpan Foto</button>
        </div>
    </div>
</div>

{{-- MODAL UPLOAD TANDA TANGAN DIGITAL (TTD) --}}
<div id="signatureModal" 
     data-has-error="{{ $errors->has('signature') ? 'true' : 'false' }}"
     class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="modalSignatureBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 transform transition-all m-4">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Unggah Berkas TTD</h3>
            <button type="button" id="closeModalSignatureBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-4">
            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" id="uploadSignatureForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-emerald-400 transition-colors bg-slate-50/50">
                    <input type="file" name="signature" id="signature_input" accept="image/png,image/jpeg,image/jpg" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required/>
                </div>
                @error('signature') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 text-center mt-2">Disarankan menggunakan format PNG transparan. Maksimal 2MB.</p>
            </form>

            @if($user->signature)
                <div class="pt-2 text-center border-t border-slate-100">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="delete_signature" value="1">
                        <button type="submit" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors bg-rose-50 hover:bg-rose-100/70 px-3 py-1.5 rounded-lg border border-rose-200/40">
                            <i class="fa-solid fa-trash-can"></i> <span>Hapus TTD Saat Ini</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-3 mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="cancelModalSignatureBtn" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50">Batal</button>
            <button type="submit" form="uploadSignatureForm" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 shadow-sm">Simpan TTD</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function enableEditPhone() {
        const phoneInput = document.getElementById('phone_number');
        const phoneBadge = document.getElementById('phone-badge');
        const btnSendOtp = document.getElementById('btn-send-otp');
        const btnChange = document.getElementById('btn-change-phone');

        if (phoneInput) {
            phoneInput.readOnly = false;
            phoneInput.classList.remove('bg-slate-50', 'text-slate-500', 'cursor-not-allowed', 'select-none');
            phoneInput.focus();
            phoneInput.select();
        }
        if (phoneBadge) {
            phoneBadge.className = "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800";
            phoneBadge.innerHTML = "Belum Verifikasi";
        }
        if (btnSendOtp) btnSendOtp.classList.remove('hidden');
        if (btnChange) btnChange.classList.add('hidden');
    }

    function toggleScheduleOptions() {
        const scheduleType = document.getElementById('schedule_type')?.value;
        const sectionNormal = document.getElementById('section_normal_schedule');
        const sectionRoster = document.getElementById('section_roster_schedule');

        if (!scheduleType || !sectionNormal || !sectionRoster) return;

        if (scheduleType === 'normal') {
            sectionNormal.classList.remove('hidden');
            sectionRoster.classList.add('hidden');
        } else {
            sectionNormal.classList.add('hidden');
            sectionRoster.classList.remove('hidden');
        }
    }

    // HITUNG ANCHOR DENGAN MENYESUAIKAN PERSYARATAN JAM 07:00 WIB
    function calculateRosterAnchor(selectedShift) {
        const now = new Date();
        const day = now.getDay(); // 0: Sun, 1: Mon, 2: Tue, ...

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

        const btnSendOtp = document.getElementById("btn-send-otp");
        const btnVerifyOtp = document.getElementById("btn-verify-otp");
        const inputPhone = document.getElementById("phone_number");
        const inputOtp = document.getElementById("otp_input");
        const otpContainer = document.getElementById("otp-container");
        const phoneError = document.getElementById("phone-error");
        const otpMessage = document.getElementById("otp-message");
        const phoneBadge = document.getElementById("phone-badge");

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (btnSendOtp) {
            btnSendOtp.addEventListener("click", function () {
                phoneError.classList.add("hidden");
                btnSendOtp.disabled = true;
                btnSendOtp.innerHTML = "Mengirim...";

                fetch("{{ route('phone.send-otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ phone_number: inputPhone.value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        otpContainer.classList.remove("hidden");
                        otpMessage.className = "text-xs text-emerald-600 mt-1 block";
                        otpMessage.innerText = data.message;
                        inputPhone.readOnly = true;

                        startSendOtpCooldown(60);
                    } else {
                        phoneError.classList.remove("hidden");
                        phoneError.innerText = data.message;
                        btnSendOtp.disabled = false;
                        btnSendOtp.innerHTML = "Verifikasi";
                    }
                })
                .catch(() => {
                    phoneError.classList.remove("hidden");
                    phoneError.innerText = "Terjadi kesalahan sistem.";
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = "Verifikasi";
                });
            });
        }

        if (btnVerifyOtp) {
            btnVerifyOtp.addEventListener("click", function () {
                otpMessage.innerText = "";
                btnVerifyOtp.disabled = true;
                btnVerifyOtp.innerHTML = "Memeriksa...";

                fetch("{{ route('phone.verify-otp') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ otp_input: inputOtp.value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        otpMessage.className = "text-xs text-emerald-600 mt-1 block font-semibold";
                        otpMessage.innerText = "✓ " + data.message;

                        inputOtp.readOnly = true;
                        btnVerifyOtp.classList.add("hidden");
                        btnSendOtp.classList.add("hidden");

                        inputPhone.readOnly = true;
                        inputPhone.classList.remove("border-emerald-500", "bg-emerald-50/30");
                        inputPhone.classList.add("border-slate-200", "bg-slate-50", "text-slate-500", "cursor-not-allowed", "select-none");

                        if(phoneBadge) {
                            phoneBadge.className = "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800";
                            phoneBadge.innerHTML = '<i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Terverifikasi';
                        }
                    } else {
                        otpMessage.className = "text-xs text-rose-500 mt-1 block";
                        otpMessage.innerText = data.message || "Kode OTP salah.";
                        
                        btnVerifyOtp.disabled = false;
                        btnVerifyOtp.innerHTML = "Konfirmasi";
                        
                        inputOtp.focus();
                        inputOtp.select();
                    }
                })
                .catch(() => {
                    otpMessage.className = "text-xs text-rose-500 mt-1 block";
                    otpMessage.innerText = "Terjadi kesalahan saat memverifikasi.";
                    
                    btnVerifyOtp.disabled = false;
                    btnVerifyOtp.innerHTML = "Konfirmasi";
                });
            });
        }

        function startSendOtpCooldown(duration) {
            let timeLeft = duration;
            btnSendOtp.disabled = true;
            const timer = setInterval(function() {
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = "Kirim Ulang";
                } else {
                    btnSendOtp.innerHTML = `Tunggu (${timeLeft}s)`;
                    timeLeft--;
                }
            }, 1000);
        }
    });
</script>
@endpush
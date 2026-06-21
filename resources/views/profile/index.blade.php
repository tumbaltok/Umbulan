@extends('layouts.app')

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
            <p class="text-sm text-slate-500 mt-0.5">Perbarui informasi profil Anda dan amankan akun dengan kombinasi password baru.</p>
        </div>

        {{-- Form data umum & keamanan --}}
        <form action="{{ route('account.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Profil</h3>

                {{-- Container Foto Profil Utama --}}
                <div class="flex flex-col items-center justify-center text-center mb-8">
                    {{-- Lingkaran/Kotak Foto Profil --}}
                    <div class="w-24 h-24 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg overflow-hidden border-4 border-white ring-4 ring-sky-100">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto Profil" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>

                    {{-- Teks Tombol di Bawah Foto --}}
                    <button type="button" id="openModalPhotoBtn" class="mt-3 text-sm font-semibold text-sky-600 hover:text-sky-700 transition-colors flex items-center space-x-1">
                        <i class="fa-solid fa-camera"></i>
                        <span>Ubah Foto Profil</span>
                    </button>
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

                    {{-- Alamat Email --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('email') ? 'border-rose-500' : 'border-slate-200' }}" required>
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tipe Jobs --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jobdesk / Tipe Jobs</label>
                        <select id="job_title" name="job_title" class="block w-full px-4 py-2 bg-white border rounded-xl text-slate-800 text-sm focus:outline-none focus:border-sky-500 transition-all {{ $errors->has('job_title') ? 'border-rose-500' : 'border-slate-200' }}">
                            <option value="" disabled {{ old('job_title', $user->job_title) == '' ? 'selected' : '' }}>Pilih Tipe Jobs</option>
                            <option value="Operator" {{ old('job_title', $user->job_title) == 'Operator' ? 'selected' : '' }}>Operator</option>
                            <option value="Maintenance" {{ old('job_title', $user->job_title) == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="HSE" {{ old('job_title', $user->job_title) == 'HSE' ? 'selected' : '' }}>Safety (HSE)</option>
                            <option value="Dokumentasi" {{ old('job_title', $user->job_title) == 'Dokumentasi' ? 'selected' : '' }}>Documenter</option>
                        </select>
                        @error('job_title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- No. Telephone --}}
                    {{-- <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telephone</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="text"
                                    name="phone_number"
                                    value="{{ old('phone_number', $user->phone_number) }}"
                                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-sky-500 {{ $errors->has('phone_number') ? 'border-rose-500' : 'border-slate-200' }}"
                                    placeholder="Contoh: 08123456789">
                            </div>
                            <button type="button"
                                    id="btn-verify"
                                    class="px-4 py-2 bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-600 border border-slate-200 hover:border-sky-200 rounded-xl text-sm font-semibold shadow-sm transition-all whitespace-nowrap h-[42px] flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved mr-1.5 text-xs text-slate-400"></i>
                                Verifikasi
                            </button>
                        </div>
                        @error('phone_number')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div> --}}
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

{{-- ========================================================= --}}
{{-- COMPONENT POPUP / MODAL (KHUSUS FOTO PROFIL)              --}}
{{-- ========================================================= --}}
<div id="photoModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    {{-- Backdrop --}}
    <div id="modalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Konten Box Popup --}}
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 transform transition-all m-4">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Perbarui Foto Profil</h3>
            <button type="button" id="closeModalPhotoBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-4">
            {{-- FORM A: Khusus Unggah Foto Baru --}}
            <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data" id="uploadPhotoForm">
                @csrf
                @method('PUT')

                    {{-- Hidden input standar agar lolos validasi request 'required' di Controller --}}
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">


                <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-sky-400 transition-colors bg-slate-50/50">
                    <input type="file" name="profile_photo" id="profile_photo_input" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" required/>
                </div>

                @error('profile_photo')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror

                <p class="text-xs text-slate-400 text-center mt-2">Format file yang didukung: JPG, JPEG, PNG. Maksimal 2MB.</p>
            </form>

            {{-- FORM B: Terpisah & Mandiri Hanya untuk Hapus Foto (Mencegah kegagalan pembacaan request) --}}
            @if($user->profile_photo)
                <div class="pt-2 text-center border-t border-slate-100">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        {{-- Kirim data minimal yang dibutuhkan oleh logika hapus di controller --}}
                        <input type="hidden" name="delete_photo" value="1">

                        <button type="submit" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors bg-rose-50 hover:bg-rose-100/70 px-3 py-1.5 rounded-lg border border-rose-200/40">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Hapus Foto Saat Ini</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Tombol Aksi Batal / Simpan Foto Baru --}}
        <div class="flex items-center space-x-3 mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="cancelModalPhotoBtn" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">
                Batal
            </button>
            {{-- Menggunakan atribut form untuk menembak FORM A --}}
            <button type="submit" form="uploadPhotoForm" class="px-4 py-2 bg-sky-600 text-white rounded-xl text-sm font-medium hover:bg-sky-700 transition-colors shadow-sm shadow-sky-900/10">
                Simpan Foto
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("photoModal");
        const openBtn = document.getElementById("openModalPhotoBtn");
        const closeBtn = document.getElementById("closeModalPhotoBtn");
        const cancelBtn = document.getElementById("cancelModalPhotoBtn");
        const backdrop = document.getElementById("modalBackdrop");

        function showModal() {
            if (modal) {
                modal.classList.remove("hidden");
                modal.classList.add("flex");
                document.body.classList.add("overflow-hidden");
            }
        }

        function hideModal() {
            if (modal) {
                modal.classList.remove("flex");
                modal.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            }
        }

        if (openBtn) openBtn.addEventListener("click", showModal);
        if (closeBtn) closeBtn.addEventListener("click", hideModal);
        if (cancelBtn) cancelBtn.addEventListener("click", hideModal);
        if (backdrop) backdrop.addEventListener("click", hideModal);

        @if($errors->has('profile_photo'))
            showModal();
        @endif
    });
</script>
@endpush

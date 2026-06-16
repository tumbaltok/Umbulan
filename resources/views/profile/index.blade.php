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

        <form action="{{ route('account.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Profil</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIP</label>
                        <input type="text" name="nip" value="{{ old('email', $user->nip) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('email') border-rose-500 @enderror">
                        @error('nip') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('name') border-rose-500 @enderror" required>
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('email') border-rose-500 @enderror" required>
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Keamanan Akun</h3>
                <p class="text-xs text-slate-400 mb-4">Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password akun.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Saat Ini (Password Lama)</label>
                        <input type="password" name="current_password" class="w-full md:w-1/2 px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('current_password') border-rose-500 @enderror" placeholder="••••••••">
                        @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-sky-500 @error('new_password') border-rose-500 @enderror" placeholder="Minimal 8 karakter">
                            @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

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
@endsection

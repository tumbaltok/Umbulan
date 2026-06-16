@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Manajemen Karyawan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data seluruh staf, hak akses role, penempatan stasiun kerja, dan informasi akun.</p>
            </div>
            <button class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                + Tambah Karyawan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Email / Akun</th>
                        <th class="px-6 py-4">Jabatan / Role</th>
                        <th class="px-6 py-4">Penempatan Stasiun</th>
                        <th class="px-6 py-4 text-center">Status Data</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarKaryawan as $karyawan)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-xs font-bold uppercase">
                                        {{ substr($karyawan->name, 0, 2) }}
                                    </div>
                                    <span>{{ $karyawan->name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-600 text-sm">{{ $karyawan->email }}</td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                    {{ strtolower($karyawan->role_name) == 'manager' ? 'bg-purple-50 text-purple-700 border border-purple-100' : (strtolower($karyawan->role_name) == 'supervisor' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $karyawan->role_name ?? 'Tidak Ada Role' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if(!empty($karyawan->nama_stasiun))
                                    <span class="inline-flex items-center text-sm text-slate-700">
                                        <i class="fa-solid fa-location-dot mr-1.5 text-rose-500 text-xs"></i>
                                        {{ $karyawan->nama_stasiun }} (ID: {{ $karyawan->station_id }})
                                    </span>
                                @else
                                    <span class="text-xs text-rose-500 font-medium bg-rose-50 px-2 py-0.5 rounded-md italic border border-rose-100">
                                        ⚠️ Stasiun Belum Diatur
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if(!empty($karyawan->station_id))
                                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full font-semibold">Ready</span>
                                @else
                                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full font-semibold" title="Karyawan ini tidak akan muncul di SPV manapun saat ajukan cuti">Filter Terkunci</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="#" class="px-3 py-1.5 bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 rounded-lg text-xs font-semibold transition-colors inline-block">
                                    <i class="fa-solid fa-user-gear mr-1"></i> Atur Sektor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2 block text-slate-200"></i>
                                Belum ada data karyawan terdaftar di database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

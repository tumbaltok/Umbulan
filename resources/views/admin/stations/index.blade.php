@extends('layouts.app')
@section('title', 'Daftar Stasiun Kerja')
@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Sektor / Stasiun Kerja</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manajemen titik lokasi wilayah kerja untuk distribusi karyawan dan validasi struktur persetujuan.</p>
            </div>
            {{-- <button class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                + Tambah Stasiun
            </button> --}}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4 w-20 text-center">ID</th>
                        <th class="px-6 py-4">Nama Sektor / Stasiun</th>
                        <th class="px-6 py-4 text-center">Total Penempatan Staf</th>
                        {{-- <th class="px-6 py-4 text-center">Status Operasional</th>
                        <th class="px-6 py-4 text-center">Aksi</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarStasiun as $stasiun)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center font-mono text-sm font-bold text-slate-400">{{ $stasiun->id }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500"></div>
                                    <span>{{ $stasiun->name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold font-mono {{ $stasiun->total_karyawan > 0 ? 'bg-sky-50 text-sky-700 border border-sky-100' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $stasiun->total_karyawan }} Orang
                                </span>
                            </td>

                            {{-- <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Aktif
                                </span>
                            </td> --}}

                            {{-- <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex space-x-1">
                                    <a href="#" class="px-2.5 py-1.5 bg-slate-50 hover:bg-amber-50 text-slate-500 hover:text-amber-700 rounded-lg text-xs font-semibold transition-colors border border-slate-100">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <a href="#" class="px-2.5 py-1.5 bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-700 rounded-lg text-xs font-semibold transition-colors border border-slate-100">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-map-location-dot text-3xl mb-2 block text-slate-200"></i>
                                Belum ada data stasiun kerja yang diinput ke dalam database erp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

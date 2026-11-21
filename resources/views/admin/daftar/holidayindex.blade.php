@extends('layouts.app')
@section('title', 'Kalender Hari Libur Nasional & Cuti Bersama')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .modal-scroll-area::-webkit-scrollbar {
        width: 6px;
    }
    .modal-scroll-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .modal-scroll-area::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.4);
        border-radius: 9999px;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto mt-6 px-4 space-y-6"
     id="page-container"
     data-success="{{ session('success') }}"
     data-error="{{ session('error') }}"
     data-errors='@json($errors->all())'>

    {{-- HEADER & ACTION BUTTONS --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-lg shadow-xs">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Kalender Hari Libur & Cuti Bersama</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Sinkronisasi otomatis SKB 3 Menteri untuk presensi, izin cuti, dan perhitungan payroll.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- Tombol Sinkronisasi SKB 3 Menteri --}}
            <form id="form-sync-holiday" action="{{ route('admin.holidays.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button type="button" onclick="confirmSyncHolidays({{ $selectedYear }})" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Sinkronisasi SKB 3 Menteri</span>
                </button>
            </form>

            {{-- Tombol Tambah Manual --}}
            <button type="button" onclick="bukaModalTambah()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Manual</span>
            </button>
        </div>
    </div>

    {{-- STATISTIK KARTU TAHUN TERPILIH --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Hari Libur --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 p-5 shadow-xs transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Hari Libur {{ $selectedYear }}</span>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalHolidays }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
            </div>
        </div>

        {{-- Libur Nasional --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 p-5 shadow-xs transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-red-500 uppercase tracking-wider">Libur Nasional</span>
                    <p class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">{{ $totalNational }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-flag"></i>
                </div>
            </div>
        </div>

        {{-- Cuti Bersama --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 p-5 shadow-xs transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-500 uppercase tracking-wider">Cuti Bersama</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $totalCutiBersama }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-business-time"></i>
                </div>
            </div>
        </div>

        {{-- Input Manual --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 p-5 shadow-xs transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-sky-500 uppercase tracking-wider">Input Manual Admin</span>
                    <p class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ $totalManual }} <span class="text-xs font-normal text-slate-400">Hari</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-xs p-5 transition-colors">
        <form method="GET" action="{{ route('admin.holidays.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            {{-- Filter Tahun --}}
            <div>
                <label for="filter_year" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Pilih Tahun</label>
                <select name="year" id="filter_year" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden transition-colors">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Kategori --}}
            <div>
                <label for="filter_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Kategori Libur</label>
                <select name="type" id="filter_type" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden transition-colors">
                    <option value="" {{ empty($type) ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="national" {{ $type === 'national' ? 'selected' : '' }}>Libur Nasional Saja</option>
                    <option value="cuti_bersama" {{ $type === 'cuti_bersama' ? 'selected' : '' }}>Cuti Bersama Saja</option>
                </select>
            </div>

            {{-- Input Pencarian Nama --}}
            <div>
                <label for="filter_q" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Cari Nama Hari Libur</label>
                <div class="relative">
                    <input type="text" name="q" id="filter_q" value="{{ $search }}" placeholder="Misal: Idul Fitri, Kemerdekaan..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden transition-colors">
                    <span class="absolute left-3 top-3 text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>
            </div>

            {{-- Tombol Filter & Reset --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2.5 px-4 rounded-xl transition-colors text-center shadow-xs">
                    <i class="fa-solid fa-filter mr-1"></i> Terapkan
                </button>
                @if($search || $type)
                <a href="{{ route('admin.holidays.index', ['year' => $selectedYear]) }}" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-semibold py-2.5 px-3 rounded-xl transition-colors">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL DATA HARI LIBUR --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-xs overflow-hidden transition-colors">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700/80 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Daftar Tanggal Merah Tahun {{ $selectedYear }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Menampilkan {{ $holidays->total() }} tanggal libur resmi terdata.</p>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/40">
                Auto-update aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-5 py-3.5 w-16 text-center">No</th>
                        <th class="px-5 py-3.5 w-48">Tanggal</th>
                        <th class="px-5 py-3.5">Nama Hari Libur</th>
                        <th class="px-5 py-3.5 text-center w-36">Kategori</th>
                        <th class="px-5 py-3.5 text-center w-36">Sumber Data</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    @forelse($holidays as $index => $item)
                        @php
                            $carbon = \Carbon\Carbon::parse($item->holiday_date)->locale('id');
                            $namaHari = $carbon->translatedFormat('l');
                            $tanggalLengkap = $carbon->translatedFormat('d F Y');
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-5 py-3.5 text-center text-slate-400 dark:text-slate-400 font-medium">
                                {{ $holidays->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $tanggalLengkap }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fa-regular fa-clock text-[10px]"></i> {{ $namaHari }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $item->holiday_name }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($item->is_cuti_bersama)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                        <i class="fa-solid fa-umbrella-beach text-[10px]"></i> Cuti Bersama
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                                        <i class="fa-solid fa-flag text-[10px]"></i> Libur Nasional
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($item->source === 'manual_admin')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/40">
                                        <i class="fa-solid fa-user-pen"></i> Manual Admin
                                    </span>
                                @elseif($item->source === 'skb_3_menteri')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
                                        <i class="fa-solid fa-stamp"></i> SKB 3 Menteri
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                        {{ $item->source ?? 'Sistem' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                {{ $item->description ?: '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Edit --}}
                                    <button type="button" 
                                            onclick='bukaModalEdit(@json($item))'
                                            class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/60 hover:bg-sky-100 text-sky-600 dark:text-sky-400 flex items-center justify-center transition-colors"
                                            title="Edit Hari Libur">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <form id="form-delete-{{ $item->id }}" action="{{ route('admin.holidays.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmDeleteHoliday({{ $item->id }}, '{{ addslashes($item->holiday_name) }}')"
                                                class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-950/60 hover:bg-red-100 text-red-600 dark:text-red-400 flex items-center justify-center transition-colors"
                                                title="Hapus Tanggal Merah">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600 flex items-center justify-center text-2xl mb-3">
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                    </div>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">Belum ada data hari libur pada tahun {{ $selectedYear }}</p>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm">Klik tombol sinkronisasi untuk mengunduh ketetapan resmi SKB 3 Menteri atau tambah manual.</p>
                                    <button type="button" onclick="confirmSyncHolidays({{ $selectedYear }})" class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                                        <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Unduh SKB 3 Menteri {{ $selectedYear }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">
            {{ $holidays->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH LIBUR MANUAL --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700 animate-in fade-in zoom-in duration-200">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-sky-600"></i>
                Tambah Hari Libur / Cuti Bersama
            </h3>
            <button type="button" onclick="tutupModalTambah()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.holidays.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label for="create_holiday_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Libur <span class="text-red-500">*</span></label>
                <input type="date" name="holiday_date" id="create_holiday_date" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
            </div>

            <div>
                <label for="create_holiday_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Hari Libur <span class="text-red-500">*</span></label>
                <input type="text" name="holiday_name" id="create_holiday_name" placeholder="Misal: Cuti Bersama Hari Raya Idul Fitri" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kategori Libur <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 cursor-pointer text-xs">
                        <input type="radio" name="is_cuti_bersama" value="0" checked class="text-sky-600 focus:ring-sky-500">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Libur Nasional</div>
                            <div class="text-[11px] text-slate-400">Tanggal merah wajib</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 cursor-pointer text-xs">
                        <input type="radio" name="is_cuti_bersama" value="1" class="text-sky-600 focus:ring-sky-500">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Cuti Bersama</div>
                            <div class="text-[11px] text-slate-400">Instruksi pemerintah</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="create_description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keterangan Tambahan (Opsional)</label>
                <textarea name="description" id="create_description" rows="2" placeholder="Catatan perihal surat keputusan, edaran direksi, dsb..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-hidden"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2.5">
                <button type="button" onclick="tutupModalTambah()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    Simpan Data Libur
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT LIBUR --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700 animate-in fade-in zoom-in duration-200">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-sky-600"></i>
                Edit Informasi Hari Libur
            </h3>
            <button type="button" onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-edit-holiday" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Libur</label>
                <input type="text" id="edit_holiday_date_display" disabled class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 text-xs rounded-xl px-3 py-2.5 cursor-not-allowed">
            </div>

            <div>
                <label for="edit_holiday_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Hari Libur <span class="text-red-500">*</span></label>
                <input type="text" name="holiday_name" id="edit_holiday_name" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-hidden">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kategori Libur <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 cursor-pointer text-xs">
                        <input type="radio" name="is_cuti_bersama" id="edit_type_national" value="0" class="text-sky-600 focus:ring-sky-500">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Libur Nasional</div>
                            <div class="text-[11px] text-slate-400">Tanggal merah wajib</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 cursor-pointer text-xs">
                        <input type="radio" name="is_cuti_bersama" id="edit_type_cuti" value="1" class="text-sky-600 focus:ring-sky-500">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-200">Cuti Bersama</div>
                            <div class="text-[11px] text-slate-400">Instruksi pemerintah</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keterangan Tambahan</label>
                <textarea name="description" id="edit_description" rows="2" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-hidden"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-2.5">
                <button type="button" onclick="tutupModalEdit()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pageContainer = document.getElementById('page-container');
        const successMsg = pageContainer.dataset.success;
        const errorMsg = pageContainer.dataset.error;
        let errors = [];
        try {
            errors = JSON.parse(pageContainer.dataset.errors || '[]');
        } catch(e) {}

        if (successMsg) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successMsg,
                timer: 3000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });
        }

        if (errorMsg) {
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: errorMsg,
                customClass: { popup: 'rounded-2xl' }
            });
        }

        if (errors.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                html: '<ul class="text-left text-xs list-disc pl-5 space-y-1">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>',
                customClass: { popup: 'rounded-2xl' }
            });
        }
    });

    function bukaModalTambah() {
        document.getElementById('modalTambah').classList.remove('hidden');
    }

    function tutupModalTambah() {
        document.getElementById('modalTambah').classList.add('hidden');
    }

    function bukaModalEdit(holiday) {
        const form = document.getElementById('form-edit-holiday');
        form.action = `/admin/holidays/${holiday.id}`;
        
        document.getElementById('edit_holiday_date_display').value = holiday.holiday_date;
        document.getElementById('edit_holiday_name').value = holiday.holiday_name;
        document.getElementById('edit_description').value = holiday.description || '';

        if (holiday.is_cuti_bersama) {
            document.getElementById('edit_type_cuti').checked = true;
        } else {
            document.getElementById('edit_type_national').checked = true;
        }

        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function tutupModalEdit() {
        document.getElementById('modalEdit').classList.add('hidden');
    }

    function confirmSyncHolidays(year) {
        Swal.fire({
            title: `Sinkronkan SKB 3 Menteri ${year}?`,
            text: `Sistem akan mengambil ketetapan hari libur dan cuti bersama resmi tahun ${year}. Data manual admin tidak akan terhapus.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Sinkronkan Sekarang',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Sinkronisasi...',
                    text: 'Menghubungkan ke repositori ketetapan hari libur resmi.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                document.getElementById('form-sync-holiday').submit();
            }
        });
    }

    function confirmDeleteHoliday(id, name) {
        Swal.fire({
            title: 'Hapus Hari Libur?',
            text: `Apakah Anda yakin ingin menghapus '${name}' dari kalender?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`form-delete-${id}`).submit();
            }
        });
    }
</script>
@endpush

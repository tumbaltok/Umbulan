@extends('layouts.app')
@section('title', 'Daftar Stasiun Kerja')

@push('styles')
{{-- Library SweetAlert2 CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Kustomisasi scrollbar halus pada form modal */
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
<div class="max-w-6xl mx-auto mt-8 px-4 space-y-8" 
     id="page-container"
     data-success="{{ session('success') }}"
     data-error="{{ session('error') }}"
     data-errors='@json($errors->all())'>

    @php
        // Pemisahan koleksi data stasiun berdasarkan tipe
        $stasiunUtama = isset($daftarStasiunUtama) ? $daftarStasiunUtama : $daftarStasiun->filter(function($s) {
            return ($s->type ?? 'stasiun') !== 'rumah_meter';
        });

        $stasiunRumahMeter = isset($daftarRumahMeter) ? $daftarRumahMeter : $daftarStasiun->filter(function($s) {
            return ($s->type ?? 'stasiun') === 'rumah_meter';
        });
    @endphp

    {{-- TABEL 1: KANTOR & STASIUN KERJA UTAMA --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-building-shield"></i>
                    </span>
                    Daftar Tempat Kerja Utama
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manajemen Kantor dan Stasiun Operasional untuk penempatan staf & alur persetujuan pengajuan.</p>
            </div>
            @if(Auth::user()->isLevel1())
            <button type="button" onclick="bukaModalTambahStasiun('stasiun')" class="bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0">
                <i class="fa-solid fa-plus"></i> Tambah Lokasi Baru
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-6 py-4 w-24">Kode</th>
                        <th class="px-6 py-4">Nama Tempat Kerja</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">Total Penempatan Staf</th>
                        @if(Auth::user()->isLevel1())
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70 text-slate-700 dark:text-slate-200 text-sm">
                    @forelse($stasiunUtama as $stasiun)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">{{ $stasiun->kode_stasiun }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500 shrink-0"></div>
                                    <span>{{ $stasiun->name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(($stasiun->type ?? 'stasiun') === 'kantor')
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800/60 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-building text-blue-500"></i> Kantor
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-bolt text-emerald-500"></i> Stasiun
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $totalStaf = $stasiun->users_count ?? ($stasiun->total_karyawan ?? 0);
                                    $staffList = $stasiun->users ?? collect();
                                    $staffNames = $staffList->pluck('name')->implode(', ');
                                @endphp
                                <div class="relative inline-block group">
                                    <span data-id="{{ $stasiun->id }}" data-name="{{ $stasiun->name }}"
                                        title="{{ $staffNames ? 'Staf: ' . $staffNames : ($totalStaf > 0 ? 'Klik untuk melihat staf' : 'Belum ada staf') }}"
                                        class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200 inline-flex items-center gap-1.5
                                        {{ $totalStaf > 0 ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800 hover:bg-sky-100 dark:hover:bg-sky-900/60 cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 cursor-not-allowed' }}">
                                        @if($totalStaf > 0)
                                            <i class="fa-solid fa-users text-[10px] text-sky-500"></i>
                                        @endif
                                        <span>{{ $totalStaf }} Orang</span>
                                    </span>

                                    @if($totalStaf > 0 && $staffList->count() > 0)
                                    {{-- Popover Hover Daftar Staf Stasiun --}}
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none transition-all duration-200 animate-in fade-in zoom-in-95">
                                        <div class="bg-slate-900/95 dark:bg-slate-800 text-white text-[11px] font-medium py-2 px-3.5 rounded-xl shadow-xl border border-slate-700 whitespace-nowrap min-w-[150px] text-left backdrop-blur-xs">
                                            <p class="font-bold text-sky-400 text-[10px] uppercase tracking-wider mb-1.5 flex items-center gap-1.5 border-b border-slate-700/80 pb-1">
                                                <i class="fa-solid fa-user-check text-[9px]"></i> Staf Penempatan ({{ $totalStaf }})
                                            </p>
                                            <ul class="text-slate-200 space-y-0.5">
                                                @foreach($staffList->take(5) as $staf)
                                                    <li class="flex items-center gap-1.5 truncate max-w-[200px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400 shrink-0"></span>
                                                        <span class="truncate">{{ $staf->name }}</span>
                                                    </li>
                                                @endforeach
                                                @if($staffList->count() > 5)
                                                    <li class="text-slate-400 text-[10px] italic pt-0.5">+{{ $staffList->count() - 5 }} staf lainnya (klik untuk detail)</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="w-2 h-2 bg-slate-900/95 dark:bg-slate-800 rotate-45 -mt-1 border-r border-b border-slate-700"></div>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            @if(Auth::user()->isLevel1())
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-stasiun='@json($stasiun)'
                                            onclick="bukaModalEditStasiun(this)"
                                            class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-xl text-xs transition-colors" 
                                            title="Edit Stasiun">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form id="form-delete-stasiun-{{ $stasiun->id }}" action="{{ route('admin.stations.destroy', $stasiun->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="konfirmasiHapus('form-delete-stasiun-{{ $stasiun->id }}', 'Stasiun / Lokasi: {{ $stasiun->name }}')"
                                                class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl text-xs transition-colors" 
                                                title="Hapus Lokasi">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-building-circle-exclamation text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada data Kantor / Stasiun Kerja yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL 2: KHUSUS RUMAH METER --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 bg-amber-50/30 dark:bg-amber-950/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shadow-xs">
                            <i class="fa-solid fa-gauge-high"></i>
                        </span>
                        Daftar Rumah Meter
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800">
                        {{ $stasiunRumahMeter->count() }} Checkpoint
                    </span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar lokasi checkpoint Rumah Meter untuk penempatan dan penugasan staf Pipeline.</p>
            </div>
            @if(Auth::user()->isLevel1())
            <button type="button" onclick="bukaModalTambahStasiun('rumah_meter')" class="bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-sm shrink-0">
                <i class="fa-solid fa-plus"></i> Tambah Rumah Meter
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                        <th class="px-6 py-4 w-24">Kode</th>
                        <th class="px-6 py-4">Nama Rumah Meter</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">Total Staf Terikat</th>
                        @if(Auth::user()->isLevel1())
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70 text-slate-700 dark:text-slate-200 text-sm">
                    @forelse($stasiunRumahMeter as $stasiun)
                        <tr class="hover:bg-amber-50/20 dark:hover:bg-amber-950/20 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">{{ $stasiun->kode_stasiun }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></div>
                                    <span>{{ $stasiun->name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-gauge-high text-amber-500"></i> Rumah Meter
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $rm = $stasiun;
                                    $totalStafRm = $rm->assigned_users_count ?? ($rm->assignedUsers ? $rm->assignedUsers->count() : ($rm->users_count ?? 0));
                                    $staffRmList = $rm->assignedUsers ?? collect();
                                    $staffRmNames = $staffRmList->pluck('name')->implode(', ');
                                @endphp
                                <div class="relative inline-block group">
                                    <span data-id="{{ $rm->id }}" data-name="{{ $rm->name }}"
                                        title="{{ $staffRmNames ? 'Staf Terikat: ' . $staffRmNames : ($totalStafRm > 0 ? 'Klik untuk melihat staf' : 'Belum ada staf') }}"
                                        class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200 inline-flex items-center gap-1.5
                                        {{ $totalStafRm > 0 ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 cursor-not-allowed' }}">
                                        @if($totalStafRm > 0)
                                            <i class="fa-solid fa-users text-[10px] text-amber-500"></i>
                                        @endif
                                        <span>{{ $totalStafRm }} Orang</span>
                                    </span>

                                    @if($totalStafRm > 0 && $staffRmList->count() > 0)
                                    {{-- Popover Hover Daftar Staf Rumah Meter --}}
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none transition-all duration-200 animate-in fade-in zoom-in-95">
                                        <div class="bg-slate-900/95 dark:bg-slate-800 text-white text-[11px] font-medium py-2 px-3.5 rounded-xl shadow-xl border border-slate-700 whitespace-nowrap min-w-[150px] text-left backdrop-blur-xs">
                                            <p class="font-bold text-amber-400 text-[10px] uppercase tracking-wider mb-1.5 flex items-center gap-1.5 border-b border-slate-700/80 pb-1">
                                                <i class="fa-solid fa-shield-halved text-[9px]"></i> Staf Terikat ({{ $totalStafRm }})
                                            </p>
                                            <ul class="text-slate-200 space-y-0.5">
                                                @foreach($staffRmList->take(5) as $staf)
                                                    <li class="flex items-center gap-1.5 truncate max-w-[200px]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                                        <span class="truncate">{{ $staf->name }}</span>
                                                    </li>
                                                @endforeach
                                                @if($staffRmList->count() > 5)
                                                    <li class="text-slate-400 text-[10px] italic pt-0.5">+{{ $staffRmList->count() - 5 }} staf lainnya (klik untuk detail)</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="w-2 h-2 bg-slate-900/95 dark:bg-slate-800 rotate-45 -mt-1 border-r border-b border-slate-700"></div>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            @if(Auth::user()->isLevel1())
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-stasiun='@json($stasiun)'
                                            onclick="bukaModalEditStasiun(this)"
                                            class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-xl text-xs transition-colors" 
                                            title="Edit Rumah Meter">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form id="form-delete-stasiun-{{ $stasiun->id }}" action="{{ route('admin.stations.destroy', $stasiun->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="konfirmasiHapus('form-delete-stasiun-{{ $stasiun->id }}', 'Rumah Meter: {{ $stasiun->name }}')"
                                                class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl text-xs transition-colors" 
                                                title="Hapus Rumah Meter">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-gauge-simple text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada checkpoint Rumah Meter yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('modals')
{{-- MODAL FORM TAMBAH / EDIT STASIUN --}}
<div id="modalFormStasiun" class="fixed inset-0 z-[9999] items-center justify-center hidden p-3 sm:p-5 overflow-y-auto">
    <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity -z-10 cursor-pointer" onclick="tutupModalFormStasiun()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl p-5 sm:p-7 relative z-10 animate-in fade-in zoom-in-95 duration-200 my-auto max-h-[92vh] flex flex-col border border-slate-100 dark:border-slate-700">
        
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700/80 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-sky-500/20">
                    <i class="fa-solid fa-map-location-dot text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-lg leading-tight" id="judulModalForm">Tambah Lokasi Kerja</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Konfigurasi kode unik, tipe, dan nama lokasi kerja atau Rumah Meter</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalFormStasiun()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- FORM ACTIONS & REPEATER CONTAINER --}}
        <form id="formStasiunAction" action="{{ route('admin.stations.store') }}" method="POST" class="space-y-5 overflow-y-auto pr-1 flex-1 py-4 modal-scroll-area">
            @csrf
            <input type="hidden" name="_method" id="methodFormStasiun" value="POST">

            {{-- CONTAINER INPUT REPEATER STASIUN --}}
            <div id="stationRowsContainer" class="space-y-6">
                {{-- Dynamic Rows injected by JavaScript --}}
            </div>

            {{-- TOMBOL TAMBAH BARIS STASIUN (JIKA MODE TAMBAH BANYAK) --}}
            <div id="btnTambahStasiunContainer" class="pt-2">
                <button type="button" onclick="tambahBarisStasiun()" class="w-full py-3 bg-slate-50 hover:bg-sky-50/50 dark:bg-slate-800/60 dark:hover:bg-slate-700/60 text-sky-600 dark:text-sky-400 border-2 border-dashed border-sky-300 dark:border-sky-700/70 rounded-2xl text-xs font-bold flex items-center justify-center gap-2 transition-all group">
                    <i class="fa-solid fa-plus text-xs group-hover:scale-125 transition-transform"></i> Tambah Baris Stasiun / Lokasi Lain
                </button>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700/80 shrink-0">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 hidden sm:inline flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-sky-500"></i> Data lokasi digunakan untuk penempatan staf & perutean persetujuan
                </span>
                <div class="flex items-center space-x-2.5 w-full sm:w-auto justify-end">
                    <button type="button" onclick="tutupModalFormStasiun()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-600/25 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Lokasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL POPUP VIEW DAFTAR KARYAWAN PER STASIUN --}}
<div id="staffStationModal" class="fixed inset-0 z-[9999] items-center justify-center hidden p-3 sm:p-5 overflow-y-auto">
    <div id="staffModalBackdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity -z-10 cursor-pointer"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl p-6 relative z-10 transform transition-all my-auto max-h-[85vh] flex flex-col border border-slate-100 dark:border-slate-700">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Daftar Anggota Staf</h3>
                <p id="modalStationTitle" class="text-xs text-sky-600 dark:text-sky-400 font-medium mt-0.5"></p>
            </div>
            <button type="button" id="closeStaffModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalStaffLoading" class="py-12 text-center my-auto">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">Menarik data staf...</p>
        </div>

        <div id="modalStaffContent" class="hidden overflow-y-auto my-4 flex-1 pr-1 modal-scroll-area">
            <table class="w-full text-left border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-slate-400 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                        <th class="px-6 pb-1">Nama Lengkap</th>
                        <th class="px-6 pb-1">Jabatan</th>
                    </tr>
                </thead>
                <tbody id="staffListContainer">
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end border-t border-slate-100 dark:border-slate-700 pt-4">
            <button type="button" id="closeStaffModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP DETAIL LENGKAP KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-[9999] items-center justify-center hidden p-3 sm:p-5 overflow-y-auto">
    <div id="detailModalBackdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity -z-10 cursor-pointer"></div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg p-6 relative z-10 transform transition-all my-auto max-h-[90vh] overflow-y-auto border border-slate-100 dark:border-slate-700 modal-scroll-area">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoadingDetail" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 dark:border-slate-700 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">Memuat data...</p>
        </div>

        <div id="modalDataContentDetail" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white dark:border-slate-700 ring-4 ring-sky-50 dark:ring-sky-950"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800 dark:text-slate-100"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/50 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100 dark:border-sky-800"></p>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-4 grid grid-cols-1 gap-y-4 text-sm">
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">NIP</span>
                    <span id="detail_nip" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold font-mono">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2 items-center">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Email</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_email" class="text-slate-800 dark:text-slate-100 font-semibold truncate">-</span>
                        <a id="detail_email_link" href="#" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-sky-500 hover:bg-sky-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 dark:border-slate-700/60 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Jobdesk</span>
                    <span id="detail_job" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 pb-2">
                    <span class="text-slate-400 dark:text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 dark:text-slate-100 font-semibold">-</span>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 dark:border-slate-700 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
{{-- Library SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var stationIndex = 0;
    var defaultTypeGlobal = 'stasiun';

    // SweetAlert2 Toast & Popup Notification Handler
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('page-container');
        const sessionSuccess = container.getAttribute('data-success');
        const sessionError = container.getAttribute('data-error');
        const validationErrors = JSON.parse(container.getAttribute('data-errors') || '[]');

        if (sessionSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: sessionSuccess,
                confirmButtonColor: '#0284c7',
                timer: 3000,
                timerProgressBar: true
            });
        }

        if (sessionError) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: sessionError,
                confirmButtonColor: '#e11d48'
            });
        }

        if (validationErrors && validationErrors.length > 0) {
            let errorListHtml = validationErrors.map(err => `• ${err}`).join('<br>');

            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                html: `<div class="text-left text-xs font-medium text-slate-600 leading-relaxed">${errorListHtml}</div>`,
                confirmButtonColor: '#f59e0b'
            });
        }
    });

    // POPUP KONFIRMASI HAPUS STASIUN / RUMAH METER
    function konfirmasiHapus(formId, itemLabel) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: `Data "${itemLabel}" yang dihapus tidak dapat dikembalikan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl dark:bg-slate-800 dark:text-slate-100',
                confirmButton: 'rounded-xl text-xs font-bold px-4 py-2.5',
                cancelButton: 'rounded-xl text-xs font-bold px-4 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // =========================================================================
    // 📋 MODUL FORM REPEATER STASIUN & MODAL HANDLER
    // =========================================================================

    function tambahBarisStasiun(stasiunData = null) {
        const container = document.getElementById('stationRowsContainer');
        const showDelete = container.children.length > 0;
        const curIdx = stationIndex;

        const typeVal = stasiunData ? (stasiunData.type || defaultTypeGlobal) : defaultTypeGlobal;
        const kodeVal = stasiunData ? (stasiunData.kode_stasiun || '') : '';
        const nameVal = stasiunData ? (stasiunData.name || '') : '';

        // Label dan placeholder dinamis berdasarkan tipe stasiun
        let typeFieldHtml = '';
        let placeholderKode = '';
        let placeholderNama = '';
        let labelNama = '';

        if (defaultTypeGlobal === 'rumah_meter') {
            typeFieldHtml = `<input type="hidden" name="stations[${curIdx}][type]" value="rumah_meter">`;
            placeholderKode = 'Contoh: RM-GIRI';
            placeholderNama = 'Contoh: Rumah Meter Giri';
            labelNama = 'Nama Checkpoint Rumah Meter';
        } else {
            typeFieldHtml = `
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-sky-500"></i> Tipe Tempat Kerja
                    </label>
                    <select name="stations[${curIdx}][type]" required class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none cursor-pointer transition-all">
                        <option value="stasiun" ${typeVal === 'stasiun' ? 'selected' : ''}>Stasiun Operasional</option>
                        <option value="kantor" ${typeVal === 'kantor' ? 'selected' : ''}>Kantor / Manajemen Operasional (MO)</option>
                    </select>
                </div>
            `;
            placeholderKode = 'Contoh: MO-SBY atau ST-UMB';
            placeholderNama = 'Contoh: Kantor Surabaya atau Stasiun Umbulan';
            labelNama = 'Nama Tempat Kerja / Stasiun';
        }

        const rowHtml = `
            <div class="station-item-row bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/80 rounded-3xl p-5 sm:p-6 relative space-y-5 shadow-xs transition-all" id="station_row_${curIdx}">
                
                {{-- HEADER BARIS: INDIKATOR DAN TOMBOL HAPUS BARIS --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/80 font-mono">
                            Lokasi #${curIdx + 1}
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 hidden sm:inline">• Konfigurasi data lokasi</span>
                    </div>

                    ${showDelete ? `
                        <button type="button" onclick="hapusBarisStasiun(this, ${curIdx})" class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Hapus Baris Ini">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    ` : ''}
                </div>

                {{-- INFO UTAMA STASIUN --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    ${typeFieldHtml}
                    <div class="${defaultTypeGlobal === 'rumah_meter' ? 'md:col-span-1' : ''}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-barcode text-sky-500"></i> Kode Unik
                        </label>
                        <input type="text" 
                               name="stations[${curIdx}][kode_stasiun]" 
                               value="${kodeVal}" 
                               required 
                               placeholder="${placeholderKode}" 
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs uppercase font-mono font-bold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                    </div>
                    <div class="${defaultTypeGlobal === 'rumah_meter' ? 'md:col-span-2' : ''}">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-building-flag text-sky-500"></i> ${labelNama}
                        </label>
                        <input type="text" 
                               name="stations[${curIdx}][name]" 
                               value="${nameVal}" 
                               required 
                               placeholder="${placeholderNama}" 
                               class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 focus:outline-none transition-all">
                    </div>
                </div>

            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        stationIndex++;
    }

    function hapusBarisStasiun(btn, index) {
        const parentRow = btn.closest('.station-item-row');
        if (parentRow) parentRow.remove();
    }

    function bukaModalTambahStasiun(defaultType = 'stasiun') {
        defaultTypeGlobal = defaultType;
        document.getElementById('judulModalForm').innerText = defaultType === 'rumah_meter' ? 'Tambah Rumah Meter Baru' : 'Tambah Lokasi Kerja Utama';
        document.getElementById('formStasiunAction').action = "{{ route('admin.stations.store') }}";
        document.getElementById('methodFormStasiun').value = 'POST';

        // Bersihkan baris sebelumnya
        document.getElementById('stationRowsContainer').innerHTML = '';
        document.getElementById('btnTambahStasiunContainer').classList.remove('hidden');
        stationIndex = 0;

        tambahBarisStasiun();

        const modal = document.getElementById('modalFormStasiun');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

    }

    function bukaModalEditStasiun(button) {
        const stasiun = JSON.parse(button.getAttribute('data-stasiun'));

        defaultTypeGlobal = stasiun.type || 'stasiun';
        document.getElementById('judulModalForm').innerText = stasiun.type === 'rumah_meter' ? 'Edit Checkpoint Rumah Meter' : 'Edit Lokasi Kerja';
        document.getElementById('formStasiunAction').action = `/admin/stations/${stasiun.id}`;
        document.getElementById('methodFormStasiun').value = 'PUT';

        // Bersihkan baris sebelumnya
        document.getElementById('stationRowsContainer').innerHTML = '';
        document.getElementById('btnTambahStasiunContainer').classList.add('hidden');
        stationIndex = 0;

        tambahBarisStasiun(stasiun);

        const modal = document.getElementById('modalFormStasiun');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

    }

    function tutupModalFormStasiun() {
        const modal = document.getElementById('modalFormStasiun');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');

    }

    // =========================================================================
    // 🔍 MODAL VIEW STAFF & DETAIL KARYAWAN
    // =========================================================================

    // Global Modal Functions for Station Page

    function openStaffModal() {
        const modalStaff = document.getElementById("staffStationModal");
        if (modalStaff) {
            if (modalStaff.parentElement !== document.body) {
                document.body.appendChild(modalStaff);
            }
            modalStaff.classList.remove("hidden");
            modalStaff.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }
    }
    window.openStaffModal = openStaffModal;

    function closeStaffModal() {
        const modalStaff = document.getElementById("staffStationModal");
        if (modalStaff) {
            modalStaff.classList.remove("flex");
            modalStaff.classList.add("hidden");
        }
        document.body.classList.remove("overflow-hidden");
    }
    window.closeStaffModal = closeStaffModal;

    function openDetailModal() {
        const modalDetail = document.getElementById("detailKaryawanModal");
        if (modalDetail) {
            if (modalDetail.parentElement !== document.body) {
                document.body.appendChild(modalDetail);
            }
            modalDetail.classList.remove("hidden");
            modalDetail.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }
    }
    window.openDetailModal = openDetailModal;

    function closeDetailModal() {
        const modalDetail = document.getElementById("detailKaryawanModal");
        const modalStaff = document.getElementById("staffStationModal");
        if (modalDetail) {
            modalDetail.classList.remove("flex");
            modalDetail.classList.add("hidden");
        }
        if (!modalStaff || modalStaff.classList.contains("hidden")) {
            document.body.classList.remove("overflow-hidden");
        }
    }
    window.closeDetailModal = closeDetailModal;

    // Escape key listener untuk menutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStaffModal();
            closeDetailModal();
            if (typeof tutupModalFormStasiun === 'function') {
                tutupModalFormStasiun();
            }
        }
    });

    // Global click listener delegation untuk modal stasiun & staf
    document.addEventListener("click", function(e) {
        // Close Buttons & Backdrops
        if (e.target.closest("#closeStaffModalBtn") || e.target.closest("#closeStaffModalBtn2") || e.target.id === "staffModalBackdrop") {
            closeStaffModal();
            return;
        }
        if (e.target.closest("#closeDetailModalBtn") || e.target.id === "detailModalBackdrop") {
            closeDetailModal();
            return;
        }

        // KLIK TOMBOL VIEW STAF PER STASIUN
        const btnStaff = e.target.closest(".btn-view-staff");
        if (btnStaff) {
            const stationId = btnStaff.getAttribute("data-id");
            const stationName = btnStaff.getAttribute("data-name");

            if (!stationId || btnStaff.classList.contains('cursor-not-allowed')) return;

            openStaffModal();
            const stationTitle = document.getElementById("modalStationTitle");
            const loadingSectionStaff = document.getElementById("modalStaffLoading");
            const contentSectionStaff = document.getElementById("modalStaffContent");
            const staffContainer = document.getElementById("staffListContainer");

            if (stationTitle) stationTitle.textContent = `Lokasi Kerja: ${stationName}`;
            if (loadingSectionStaff) loadingSectionStaff.classList.remove("hidden");
            if (contentSectionStaff) contentSectionStaff.classList.add("hidden");
            if (staffContainer) staffContainer.innerHTML = "";

            fetch(`/admin/stations/${stationId}/karyawan`)
                .then(response => {
                    if (!response.ok) throw new Error(`Gagal mengambil data staf (Status: ${response.status})`);
                    return response.json();
                })
                .then(karyawanList => {
                    if (loadingSectionStaff) loadingSectionStaff.classList.add("hidden");
                    if (contentSectionStaff) contentSectionStaff.classList.remove("hidden");

                    if (!karyawanList || karyawanList.length === 0) {
                        if (staffContainer) {
                            staffContainer.innerHTML = `
                                <tr>
                                    <td colspan="2" class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                        Tidak ada karyawan yang aktif di lokasi ini.
                                    </td>
                                </tr>`;
                        }
                        return;
                    }

                    if (staffContainer) {
                        karyawanList.forEach(karyawan => {
                            const initials = karyawan.name ? karyawan.name.substring(0, 2).toUpperCase() : '??';
                            const photoHtml = karyawan.profile_photo
                                ? `<img src="/storage/${karyawan.profile_photo}" class="w-full h-full object-cover">`
                                : initials;

                            const roleName = karyawan.role_name || (karyawan.role ? karyawan.role.role_name : 'Staff');
                            let roleBadgeClass = 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/50 dark:border-slate-600';

                            if (roleName.toLowerCase() === 'manager') {
                                roleBadgeClass = 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800';
                            } else if (roleName.toLowerCase() === 'supervisor') {
                                roleBadgeClass = 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800';
                            } else if (roleName.toLowerCase() === 'staff') {
                                roleBadgeClass = 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800';
                            }

                            const tableRow = document.createElement("tr");
                            tableRow.className = "bg-white dark:bg-slate-800/80 hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-colors group shadow-sm border border-slate-100 dark:border-slate-700 rounded-2xl";
                            tableRow.innerHTML = `
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100 rounded-l-2xl border-y border-l border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="${karyawan.id}">
                                        <div class="w-9 h-9 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden shrink-0">
                                            ${photoHtml}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-slate-100 font-semibold text-sm group-hover:text-sky-600 dark:group-hover:text-sky-400 group-hover:underline transition-colors">${karyawan.name}</span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">NIP: ${karyawan.nip || '-'}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle rounded-r-2xl border-y border-r border-slate-100 dark:border-slate-700">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block ${roleBadgeClass}">
                                        ${roleName}
                                    </span>
                                </td>
                            `;
                            staffContainer.appendChild(tableRow);
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    if (loadingSectionStaff) loadingSectionStaff.classList.add("hidden");
                    if (staffContainer) {
                        staffContainer.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center py-8 text-rose-500 text-xs font-semibold bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                                    ⚠️ Terjadi masalah sistem: ${error.message}
                                </td>
                            </tr>`;
                    }
                    if (contentSectionStaff) contentSectionStaff.classList.remove("hidden");
                });
            return;
        }

        // KLIK DETAIL KARYAWAN
        const button = e.target.closest(".btn-detail-karyawan");
        if (button) {
            const karyawanId = button.getAttribute("data-id");

            openDetailModal();
            const loadingSectionDetail = document.getElementById("modalLoadingDetail");
            const contentSectionDetail = document.getElementById("modalDataContentDetail");
            if (loadingSectionDetail) loadingSectionDetail.classList.remove("hidden");
            if (contentSectionDetail) contentSectionDetail.classList.add("hidden");

            fetch(`/admin/karyawan/${karyawanId}/detail`)
                .then(response => {
                    if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
                    return response.json();
                })
                .then(data => {
                    if (!data || Object.keys(data).length === 0) throw new Error("Data karyawan kosong.");

                    if (loadingSectionDetail) loadingSectionDetail.classList.add("hidden");
                    if (contentSectionDetail) contentSectionDetail.classList.remove("hidden");

                    const nameEl = document.getElementById("detail_name");
                    const nipEl = document.getElementById("detail_nip");
                    const roleEl = document.getElementById("detail_role");
                    const stationEl = document.getElementById("detail_station");
                    if (nameEl) nameEl.textContent = data.name || '-';
                    if (nipEl) nipEl.textContent = data.nip ? data.nip : '-';
                    if (roleEl) roleEl.textContent = data.role_name ? data.role_name : 'Tidak Ada Role';
                    if (stationEl) stationEl.textContent = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                    const emailSpan = document.getElementById("detail_email");
                    const emailLink = document.getElementById("detail_email_link");

                    if (data.email) {
                        if (emailSpan) emailSpan.textContent = data.email;
                        if (emailLink) {
                            emailLink.href = `mailto:${data.email}`;
                            emailLink.classList.remove("hidden");
                        }
                    } else {
                        if (emailSpan) emailSpan.textContent = '-';
                        if (emailLink) emailLink.classList.add("hidden");
                    }

                    let jobTitleText = 'Belum Memilih';
                    if(data.job_title == 'Operator' || data.job_title == '1') jobTitleText = 'Operator';
                    else if(data.job_title == 'Maintenance' || data.job_title == '2') jobTitleText = 'Maintenance';
                    else if(data.job_title == 'HSE' || data.job_title == '3') jobTitleText = 'Safety (HSE)';
                    else if(data.job_title == 'Dokumentasi' || data.job_title == '4') jobTitleText = 'Documenter';

                    const jobEl = document.getElementById("detail_job");
                    if (jobEl) jobEl.textContent = jobTitleText;

                    const photoContainer = document.getElementById("detail_photo_container");
                    if (photoContainer) {
                        if (data.profile_photo) {
                            const img = document.createElement("img");
                            img.src = `/storage/${data.profile_photo}`;
                            img.className = "w-full h-full object-cover";
                            photoContainer.textContent = "";
                            photoContainer.appendChild(img);
                        } else {
                            const initials = data.name ? data.name.substring(0, 2).toUpperCase() : '??';
                            photoContainer.textContent = initials;
                        }
                    }
                })
                .catch(error => {
                    console.error(error);
                    if (loadingSectionDetail) loadingSectionDetail.classList.add("hidden");
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: `Masalah saat memuat data karyawan: ${error.message}`,
                        confirmColor: '#e11d48'
                    });
                    closeDetailModal();
                });
        }
    });
</script>
@endpush
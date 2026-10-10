@extends('layouts.app')
@section('title', 'Persetujuan Cuti Karyawan')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- BREADCRUMB & HEADER SECTION --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-sky-600 dark:hover:text-sky-400 transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-house text-[11px] text-slate-400 dark:text-slate-500"></i>
                    <span>Dashboard</span>
                </a>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-slate-600 dark:text-slate-400">Verifikasi & Persetujuan</span>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 dark:text-slate-600"></i>
                <span class="text-sky-600 dark:text-sky-400 font-bold">Persetujuan Cuti</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-user-check text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Persetujuan Pengajuan Cuti Staf
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Portal verifikasi berjenjang atasan untuk meninjau dan mengambil tindakan persetujuan cuti bawahan
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/80 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-sm text-rose-600 dark:text-rose-400"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200 uppercase tracking-wider">Pemberitahuan</h4>
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- STATISTIC SUMMARY CARDS --}}
    @php
        $totalPengajuan = $daftarPengajuan->count();
        $totalStep1Pending = $daftarPengajuan->where('status_tahap_1', 'pending')->count();
        $totalStep2Pending = $daftarPengajuan->where('status_tahap_2', 'pending')->where('status_tahap_1', 'approved')->count();
        $totalHariCuti = $daftarPengajuan->sum('total_hari');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        {{-- Total Antrean --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Antrean Butuh Review</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalPengajuan }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-inbox text-base text-sky-600 dark:text-sky-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-list-check text-sky-500"></i>
                <span>Menunggu keputusan Anda</span>
            </div>
        </div>

        {{-- Menunggu Step 1 --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tahap 1 (Atasan)</p>
                    <h3 class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-1">{{ $totalStep1Pending }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user-clock text-base text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-hourglass-start text-amber-500"></i>
                <span>Verifikasi tahap pertama</span>
            </div>
        </div>

        {{-- Menunggu Step 2 --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tahap 2 (Manajemen)</p>
                    <h3 class="text-2xl font-black text-cyan-600 dark:text-cyan-400 mt-1">{{ $totalStep2Pending }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-100 dark:border-cyan-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user-shield text-base text-cyan-600 dark:text-cyan-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-stamp text-cyan-500"></i>
                <span>Verifikasi tahap lanjutan</span>
            </div>
        </div>

        {{-- Total Akumulasi Hari --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Hari Cuti</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalHariCuti }} Hari</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-regular fa-calendar-days text-base text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-calculator text-emerald-500"></i>
                <span>Akumulasi durasi cuti masuk</span>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE CARD CONTAINER --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 overflow-hidden transition-all">

        {{-- TABLE CARD HEADER & CONTROLS --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50/80 via-white to-sky-50/30 dark:from-slate-800/90 dark:via-slate-800 dark:to-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clipboard-check text-sm text-sky-600 dark:text-sky-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span>Antrean Pengajuan Cuti Staf</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                            {{ $totalPengajuan }} Antrean
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Pilih untuk menyetujui atau menolak dengan catatan alasan yang jelas
                    </p>
                </div>
            </div>

            {{-- LIVE SEARCH --}}
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="text" id="liveSearchPersetujuanCuti" placeholder="Cari nama karyawan..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelPersetujuanCuti">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4">Karyawan / Pemohon</th>
                        <th class="px-6 py-4">Jenis Cuti & Berkas</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4 text-center">Durasi</th>
                        <th class="px-6 py-4 text-center">Status Berjenjang</th>
                        <th class="px-6 py-4 text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                    @forelse($daftarPengajuan as $item)
                        <tr class="row-persetujuan-cuti hover:bg-sky-50/40 dark:hover:bg-slate-700/40 transition-colors group">
                            {{-- KARYAWAN --}}
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 bg-gradient-to-tr from-sky-600 to-cyan-500 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm">
                                        {{ strtoupper(substr($item->user_name ?? 'K', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-100 text-sm target-nama-karyawan">
                                            {{ $item->user_name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">
                                            {{ $item->user->station->name ?? 'Stasiun Umbulan' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- JENIS CUTI & BERKAS --}}
                            <td class="px-6 py-4 align-middle">
                                <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                    {{ $item->name_cuti }}
                                </div>
                                @if(!empty($item->nama_sub_cuti))
                                    <span class="inline-block text-[11px] text-sky-600 dark:text-sky-400 font-semibold bg-sky-50 dark:bg-sky-950/60 px-2 py-0.5 rounded-md border border-sky-200/70 dark:border-sky-800/70 mt-1">
                                        {{ $item->nama_sub_cuti }}
                                    </span>
                                @endif

                                @if(!empty($item->dokumen_pendukung))
                                    <div class="mt-1.5">
                                        <button type="button"
                                                data-url="{{ asset('storage/' . $item->dokumen_pendukung) }}"
                                                onclick="bukaPratinjauLampiran(this.getAttribute('data-url'))"
                                                class="inline-flex items-center gap-1.5 text-[11px] text-sky-700 dark:text-sky-300 hover:text-sky-800 font-bold bg-sky-50 dark:bg-sky-950/60 px-2.5 py-1 rounded-lg border border-sky-200 dark:border-sky-800 shadow-2xs hover:bg-sky-100 dark:hover:bg-sky-900/60 transition-all cursor-pointer">
                                            <i class="fa-solid fa-paperclip text-sky-600 dark:text-sky-400"></i>
                                            <span>Berkas Lampiran</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            {{-- TANGGAL PELAKSANAAN --}}
                            <td class="px-6 py-4 align-middle text-xs font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-slate-400"></i>
                                    <span>
                                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('D MMM Y') }}
                                        <span class="text-slate-400 mx-1">-</span>
                                        {{ \Carbon\Carbon::parse($item->tanggal_selesai)->isoFormat('D MMM Y') }}
                                    </span>
                                </div>
                            </td>

                            {{-- DURASI TOTAL HARI --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-extrabold bg-slate-100 dark:bg-slate-700/70 text-slate-700 dark:text-slate-200 border border-slate-200/70 dark:border-slate-600/70">
                                    {{ $item->total_hari }} Hari
                                </span>
                            </td>

                            {{-- STATUS BERJENJANG --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <div class="inline-flex flex-col gap-1.5 text-[10px] font-bold">
                                    {{-- Tahap 1 --}}
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg border
                                        {{ $item->status_tahap_1 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : ($item->status_tahap_1 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->status_tahap_1 === 'approved' ? 'bg-emerald-500' : ($item->status_tahap_1 === 'rejected' ? 'bg-rose-500' : 'bg-amber-500 animate-pulse') }}"></span>
                                        <span>Tahap 1: {{ ucfirst($item->status_tahap_1) }}</span>
                                    </div>

                                    {{-- Tahap 2 --}}
                                    @if($item->status_tahap_2 !== 'not_required')
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg border
                                            {{ $item->status_tahap_2 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : ($item->status_tahap_2 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800') }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item->status_tahap_2 === 'approved' ? 'bg-emerald-500' : ($item->status_tahap_2 === 'rejected' ? 'bg-rose-500' : 'bg-amber-500 animate-pulse') }}"></span>
                                            <span>Tahap 2: {{ ucfirst($item->status_tahap_2) }}</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 border border-slate-200/50 dark:border-slate-700/50">
                                            <span>Tahap 2: Tidak Perlu</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- AKSI TINDAKAN --}}
                            <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- FORM APPROVE --}}
                                    <form id="form-approve-cuti-{{ $item->id }}" action="{{ route('admin.persetujuan.cuti.proses', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tindakan" value="approved">
                                        <button type="button"
                                                data-form="form-approve-cuti-{{ $item->id }}"
                                                data-nama="{{ $item->user_name }}"
                                                onclick="konfirmasiApprove(this.getAttribute('data-form'), 'Setujui Pengajuan Cuti ' + this.getAttribute('data-nama') + '?')"
                                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow inline-flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-check text-xs text-white"></i>
                                            <span class="text-white">Setujui</span>
                                        </button>
                                    </form>

                                    {{-- BUTTON REJECT --}}
                                    <button type="button"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->user_name }}"
                                            onclick="bukaModalTolak(this.getAttribute('data-id'), this.getAttribute('data-nama'))"
                                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/80 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                        <i class="fa-solid fa-xmark text-xs text-rose-600 dark:text-rose-400"></i>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRowPlaceholderPersetujuanCuti">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-solid fa-clipboard-check text-slate-400"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Semua Pengajuan Selesai</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                    Tidak ada antrean pengajuan cuti yang memerlukan persetujuan Anda saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noSearchResultRowPersetujuanCuti" class="hidden">
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass text-slate-300 dark:text-slate-600 text-2xl mb-2 block"></i>
                            Tidak ada permohonan cuti yang cocok dengan nama karyawan yang dicari.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP INPUT ALASAN PENOLAKAN CUTI --}}
<div id="modalTolakCuti" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-200/80 dark:border-slate-700/80">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/80 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/30 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-sm text-rose-600 dark:text-rose-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Alasan Penolakan Cuti</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500" id="namaKaryawanReject">Penolakan permohonan cuti</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalTolak()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formTolakCuti" action="" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="tindakan" value="rejected">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                    Catatan Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea name="catatan_penolakan" required rows="4"
                          class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 placeholder-slate-400"
                          placeholder="Tuliskan alasan penolakan secara jelas agar dapat dipahami oleh pemohon..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                <button type="button" onclick="tutupModalTolak()"
                        class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold px-4 py-2.5 rounded-xl cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="bg-rose-600 hover:bg-rose-700 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl cursor-pointer shadow-md shadow-rose-600/25 transition-all">
                    <span class="text-white">Kirim & Tolak</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'BERHASIL!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0284c7',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold'
            }
        });
    });
</script>
@endif

<script>
    // Live Search Karyawan
    const searchInput = document.getElementById('liveSearchPersetujuanCuti');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-persetujuan-cuti');
            let visibleCount = 0;

            rows.forEach(row => {
                const namaElem = row.querySelector('.target-nama-karyawan');
                const text = namaElem ? namaElem.innerText.toLowerCase() : '';

                if (text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const noResultRow = document.getElementById('noSearchResultRowPersetujuanCuti');
            if (noResultRow) {
                noResultRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }
        });
    }

    function konfirmasiApprove(formId, pesan) {
        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: pesan,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold',
                cancelButton: 'px-5 py-2.5 rounded-xl font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    function bukaModalTolak(id, namaKaryawan = '') {
        const modal = document.getElementById('modalTolakCuti');
        const form = document.getElementById('formTolakCuti');
        form.action = `/admin/persetujuan/cuti/proses/${id}`;

        const namaLabel = document.getElementById('namaKaryawanReject');
        if (namaLabel && namaKaryawan) {
            namaLabel.innerText = `Pemohon: ${namaKaryawan}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolakCuti');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

</script>
@endpush

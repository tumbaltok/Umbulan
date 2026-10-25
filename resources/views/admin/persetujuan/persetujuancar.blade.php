@extends('layouts.app')
@section('title', 'Persetujuan CAR')

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
                <span class="text-sky-600 dark:text-sky-400 font-bold">Persetujuan CAR</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Persetujuan Dana Muka Operasional (CAR)
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Portal verifikasi kasbon operasional, nota belanja material, dan persetujuan pencairan dana
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
        $totalNominalDiajukan = $daftarPengajuan->sum(fn($c) => $c->details->sum('total_harga'));
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        {{-- Total Antrean CAR --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Antrean CAR</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalPengajuan }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt text-base text-sky-600 dark:text-sky-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-list-check text-sky-500"></i>
                <span>Menunggu persetujuan Anda</span>
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

        {{-- Total Anggaran Masuk --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Nilai Kasbon</p>
                    <h3 class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 mt-1 truncate max-w-[150px]">
                        Rp {{ number_format($totalNominalDiajukan, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-coins text-base text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-calculator text-emerald-500"></i>
                <span>Akumulasi dana diajukan</span>
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
                        <span>Antrean Pengajuan Kasbon (CAR)</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                            {{ $totalPengajuan }} Antrean
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Periksa rincian pembelian, keabsahan nota/proposal, dan setujui pencairan dana
                    </p>
                </div>
            </div>

            {{-- LIVE SEARCH --}}
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="text" id="liveSearchPersetujuanCar" placeholder="Cari nama pemohon / barang..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelPersetujuanCar">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4" style="width: 20%;">Pemohon & No. CAR</th>
                        <th class="px-6 py-4" style="width: 38%;">Rincian Barang & Nota</th>
                        <th class="px-6 py-4" style="width: 14%;">Total Biaya</th>
                        <th class="px-6 py-4" style="width: 16%;">Keperluan</th>
                        <th class="px-6 py-4 text-center" style="width: 12%;">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                    @forelse($daftarPengajuan as $car)
                        <tr class="row-persetujuan-car hover:bg-sky-50/40 dark:hover:bg-slate-700/40 transition-colors group">
                            {{-- PEMOHON & NO CAR --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 bg-gradient-to-tr from-sky-600 to-cyan-500 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                                        {{ strtoupper(substr($car->user->name ?? 'K', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-100 text-sm target-search-car">
                                            {{ $car->user->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                            {{ $car->user->station->name ?? 'Stasiun Umbulan' }}
                                        </div>
                                        <span class="inline-block mt-1 font-mono text-[11px] font-extrabold text-sky-600 dark:text-sky-400 target-search-car">
                                            {{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}
                                        </span>

                                        {{-- Stepper Status Badge --}}
                                        <div class="flex items-center gap-1.5 mt-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $car->status_tahap_1 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($car->status_tahap_1 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800') }}">
                                                Step 1: {{ ucfirst($car->status_tahap_1) }}
                                            </span>
                                            @if($car->status_tahap_2 !== 'not_required')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $car->status_tahap_2 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($car->status_tahap_2 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800') }}">
                                                    Step 2: {{ ucfirst($car->status_tahap_2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- RINCIAN BARANG & NOTA --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    @foreach($car->details as $item)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70 dark:bg-slate-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 target-search-car">
                                            <div class="space-y-0.5">
                                                <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">
                                                    {{ $item->nama_barang }}
                                                </span>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1.5">
                                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Rp {{ number_format($item->estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                                    <span class="text-slate-400">x {{ $item->jumlah }} {{ $item->satuan ?? 'Qty' }}</span>
                                                    @if(($item->ongkir ?? 0) > 0)
                                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                                        <span class="text-amber-600 dark:text-amber-400 font-semibold">+ Ongkir: Rp {{ number_format($item->ongkir, 0, ',', '.') }}</span>
                                                    @endif
                                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                                    <span class="text-slate-400">Total:</span>
                                                    <span class="font-extrabold text-slate-800 dark:text-slate-200">Rp {{ number_format(($item->total_harga ?? ($item->estimasi_harga * $item->jumlah)), 0, ',', '.') }}</span>
                                                </div>
                                            </div>

                                            @if($item->dokumen_nota_or_proposal)
                                                <button type="button"
                                                        data-url="{{ asset('storage/' . $item->dokumen_nota_or_proposal) }}"
                                                        onclick="bukaPratinjauDokumen(this.dataset.url)"
                                                        class="self-start sm:self-auto inline-flex items-center gap-1.5 text-[11px] bg-white dark:bg-slate-800 hover:bg-sky-50 dark:hover:bg-slate-700 text-sky-600 dark:text-sky-400 font-bold px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 shadow-2xs transition-all cursor-pointer">
                                                    <i class="fa-solid fa-file-invoice text-xs text-sky-600 dark:text-sky-400"></i>
                                                    <span>Nota</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            {{-- TOTAL BIAYA --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="font-black text-emerald-600 dark:text-emerald-400 text-xs">
                                    Rp {{ number_format($car->details->sum('total_harga'), 0, ',', '.') }}
                                </div>
                                @php $totalOngkirCar = $car->details->sum('ongkir'); @endphp
                                @if($totalOngkirCar > 0)
                                    <span class="inline-block text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-1 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded border border-amber-200/60 dark:border-amber-800/60">
                                        Ongkir: Rp {{ number_format($totalOngkirCar, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            {{-- ALASAN KEPERLUAN --}}
                            <td class="px-6 py-4 align-top text-xs leading-relaxed text-slate-700 dark:text-slate-300">
                                <div class="bg-slate-50/70 dark:bg-slate-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 target-search-car">
                                    {{ $car->alasan_pembelian ?? '-' }}
                                </div>
                            </td>

                            {{-- AKSI TINDAKAN --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- FORM APPROVE --}}
                                    <form id="form-approve-car-{{ $car->id }}" action="{{ route('admin.persetujuan.car.process', $car->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="aksi" value="approved">
                                        <button type="button"
                                                data-form="form-approve-car-{{ $car->id }}"
                                                data-nama="{{ $car->user->name }}"
                                                onclick="konfirmasiApprove(this.getAttribute('data-form'), 'Setujui Pengajuan CAR milik ' + this.getAttribute('data-nama') + '?')"
                                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow inline-flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-check text-xs text-white"></i>
                                            <span class="text-white">Setujui</span>
                                        </button>
                                    </form>

                                    {{-- BUTTON REJECT --}}
                                    <button type="button"
                                            data-id="{{ $car->id }}"
                                            data-nama="{{ $car->user->name }}"
                                            data-nomor="{{ $car->nomor_car ?? ('CAR #' . sprintf('%03d', $car->id)) }}"
                                            onclick="bukaModalTolak(this.getAttribute('data-id'), this.getAttribute('data-nama'), this.getAttribute('data-nomor'))"
                                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/80 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                        <i class="fa-solid fa-xmark text-xs text-rose-600 dark:text-rose-400"></i>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRowPlaceholderPersetujuanCar">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-solid fa-clipboard-check text-slate-400"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Semua Pengajuan CAR Selesai</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                    Tidak ada antrean pengajuan kasbon (CAR) yang memerlukan persetujuan Anda saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noSearchResultRowPersetujuanCar" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass text-slate-300 dark:text-slate-600 text-2xl mb-2 block"></i>
                            Tidak ada permohonan CAR yang cocok dengan pencarian Anda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
{{-- MODAL POPUP INPUT ALASAN PENOLAKAN CAR (CENTER VIEWPORT GUARANTEED) --}}
<div id="modalTolak" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 overflow-y-auto">
    <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity -z-10 cursor-pointer" onclick="tutupModalTolak()" aria-hidden="true"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-200/80 dark:border-slate-700/80 my-auto max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/80 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/30 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-sm text-rose-600 dark:text-rose-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Alasan Penolakan CAR</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500" id="carRejectSubtitle">Penolakan anggaran kasbon</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalTolak()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formTolak" action="" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="aksi" value="rejected">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                    Catatan Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea name="catatan_penolakan" required rows="4"
                          class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 placeholder-slate-400"
                          placeholder="Tuliskan catatan alasan penolakan secara spesifik..."></textarea>
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
@endpush

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
    // Live Search Karyawan / Barang
    const searchInput = document.getElementById('liveSearchPersetujuanCar');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-persetujuan-car');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchTargets = row.querySelectorAll('.target-search-car');
                let rowText = '';
                searchTargets.forEach(t => rowText += ' ' + t.innerText);

                if (rowText.toLowerCase().includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const noResultRow = document.getElementById('noSearchResultRowPersetujuanCar');
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

    function bukaModalTolak(id, namaKaryawan = '', nomorCar = '') {
        const modal = document.getElementById('modalTolak');
        if (!modal) return;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const form = document.getElementById('formTolak');
        if (form) form.action = `/admin/persetujuan/car/proses/${id}`;

        const subtitleLabel = document.getElementById('carRejectSubtitle');
        if (subtitleLabel) {
            subtitleLabel.innerText = `${namaKaryawan} - ${nomorCar}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolak');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            const modal = document.getElementById('modalTolak');
            if (modal && !modal.classList.contains('hidden')) {
                tutupModalTolak();
            }
        }
    });
</script>
@endpush

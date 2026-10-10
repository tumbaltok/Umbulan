@extends('layouts.app')
@section('title', 'Riwayat Pengajuan MPR')

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
                <span class="text-sky-600 dark:text-sky-400 font-bold">Riwayat MPR</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-sky-600 bg-gradient-to-r from-sky-600 to-cyan-600 dark:from-sky-500 dark:to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-600/25 shrink-0">
                    <i class="fa-solid fa-boxes-packing text-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                        Riwayat Pengajuan MPR
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Daftar dan pemantauan permohonan Material Purchase Request (MPR) logistik dan sparepart Anda
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK ACTION BUTTON --}}
        <div class="flex items-center gap-2 self-start sm:self-center">
            <a href="{{ route('mpr.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white text-xs font-bold transition-all shadow-md shadow-sky-600/25 hover:shadow-lg hover:shadow-sky-600/35 active:scale-95 group cursor-pointer">
                <i class="fa-solid fa-plus text-xs text-white group-hover:rotate-90 transition-transform duration-200"></i>
                <span class="text-white">Ajukan MPR Baru</span>
            </a>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/80 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-sm text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-200 uppercase tracking-wider">Berhasil</h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- STATISTIC SUMMARY CARDS --}}
    @php
        $totalMpr = $riwayatMpr->count();
        $totalMprApproved = $riwayatMpr->where('status_akhir', 'approved')->count();
        $totalMprPending = $riwayatMpr->where('status_akhir', 'pending')->count();
        $totalMprRejected = $riwayatMpr->where('status_akhir', 'rejected')->count();
        $totalItems = $riwayatMpr->sum(fn($m) => $m->items->count());
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        {{-- Total Pengajuan --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total MPR</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $totalMpr }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-base text-sky-600 dark:text-sky-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-clipboard-list text-sky-500"></i>
                <span>{{ $totalItems }} total jenis barang</span>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Disetujui</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalMprApproved }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-file-circle-check text-emerald-500"></i>
                <span>Dapat dicetak sebagai PDF</span>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu</p>
                    <h3 class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-1">{{ $totalMprPending }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock text-base text-amber-600 dark:text-amber-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                <span>Menunggu persetujuan atasan</span>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/70 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ditolak</p>
                    <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $totalMprRejected }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-xmark text-base text-rose-600 dark:text-rose-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span>Membutuhkan evaluasi ulang</span>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE CARD CONTAINER --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/90 dark:border-slate-700/70 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/40 overflow-hidden transition-all">

        {{-- TABLE CARD HEADER & CONTROLS --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-r from-slate-50/80 via-white to-sky-50/30 dark:from-slate-800/90 dark:via-slate-800 dark:to-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/30 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-list-check text-sm text-sky-600 dark:text-sky-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span>Daftar Permohonan Material (MPR)</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                            {{ $totalMpr }} Dokumen
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Pantau status persetujuan berjenjang dan cetak berkas MPR
                    </p>
                </div>
            </div>

            {{-- LIVE SEARCH & FILTER CHIPS --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="text" id="liveSearchMpr" placeholder="Cari No. MPR / barang..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                </div>
                <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-900/80 rounded-xl border border-slate-200/70 dark:border-slate-700/60 text-xs">
                    <button type="button" onclick="filterStatusMpr('all')" class="chip-filter-mpr active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer" data-status="all">Semua</button>
                    <button type="button" onclick="filterStatusMpr('pending')" class="chip-filter-mpr px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="pending">Menunggu</button>
                    <button type="button" onclick="filterStatusMpr('approved')" class="chip-filter-mpr px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="approved">Disetujui</button>
                    <button type="button" onclick="filterStatusMpr('rejected')" class="chip-filter-mpr px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer" data-status="rejected">Ditolak</button>
                </div>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelRiwayatMpr">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4">No. MPR / Tanggal</th>
                        <th class="px-6 py-4">Prioritas & Lokasi</th>
                        <th class="px-6 py-4">Keperluan / Urgensi</th>
                        <th class="px-6 py-4">Rincian Material</th>
                        <th class="px-6 py-4 text-center">Status Akhir</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                    @forelse($riwayatMpr as $mpr)
                        @php
                            $statusNormalized = 'pending';
                            if ($mpr->status_akhir === 'approved') {
                                $statusNormalized = 'approved';
                            } elseif ($mpr->status_akhir === 'rejected') {
                                $statusNormalized = 'rejected';
                            }
                        @endphp
                        <tr class="row-mpr hover:bg-sky-50/40 dark:hover:bg-slate-700/40 transition-colors group" data-status="{{ $statusNormalized }}">
                            {{-- NO MPR & TANGGAL --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-500/30 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fa-solid fa-file-lines text-xs text-sky-600 dark:text-sky-400"></i>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-100 block text-xs target-search">
                                            {{ $mpr->nomor_mpr }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5 mt-0.5">
                                            <i class="fa-regular fa-calendar text-[10px]"></i>
                                            {{ \Carbon\Carbon::parse($mpr->tanggal_pengajuan)->format('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- PRIORITY & DELIVERY POINT --}}
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                @if($mpr->priority === 'Emergency')
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                        <span>Emergency</span>
                                    </span>
                                @elseif($mpr->priority === 'Urgent')
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-lg text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Urgent</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-lg text-[10px] font-semibold uppercase inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Normal</span>
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-slate-400 text-[10px]"></i>
                                    <span>{{ $mpr->delivery_point ?? 'Site Umbulan' }}</span>
                                </div>
                            </td>

                            {{-- KEPERLUAN / URGENSI --}}
                            <td class="px-6 py-4 align-top text-xs font-medium text-slate-700 dark:text-slate-300 max-w-xs leading-relaxed target-search">
                                <div class="bg-slate-50/70 dark:bg-slate-900/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60">
                                    {{ \Illuminate\Support\Str::limit($mpr->keperluan_urgensi, 110) }}
                                </div>
                            </td>

                            {{-- RINCIAN MATERIAL ITEMS --}}
                            <td class="px-6 py-4 align-top text-xs min-w-[220px]">
                                <div class="space-y-1.5">
                                    @foreach($mpr->items as $item)
                                        <div class="flex items-center justify-between gap-2 p-1.5 bg-slate-50/50 dark:bg-slate-900/30 rounded-lg border border-slate-100 dark:border-slate-700/50 target-search">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $item->nama_barang }}</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 shrink-0">
                                                {{ $item->jumlah }} {{ $item->satuan }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            {{-- STATUS AKHIR --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($mpr->status_akhir === 'approved')
                                    <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($mpr->status_akhir === 'rejected')
                                    <span class="px-3 py-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                @if($mpr->status_akhir === 'approved')
                                    <button type="button"
                                            onclick="bukaPratinjauCetak('{{ route('mpr.cetak', $mpr->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 hover:bg-sky-700 bg-gradient-to-r from-sky-600 to-cyan-600 hover:from-sky-700 hover:to-cyan-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hover:shadow cursor-pointer">
                                        <i class="fa-solid fa-print text-xs text-white"></i>
                                        <span class="text-white">Cetak PDF</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 dark:text-slate-500 text-xs italic">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Belum Ada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRowPlaceholderMpr">
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 flex items-center justify-center text-2xl mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                    <i class="fa-solid fa-boxes-packing text-slate-400"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pengajuan MPR</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                    Anda belum pernah mengajukan Material Purchase Request. Klik tombol di bawah untuk membuat pengajuan baru.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('mpr.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-plus text-white"></i>
                                        <span class="text-white">Ajukan MPR Sekarang</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noSearchResultRowMpr" class="hidden">
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-magnifying-glass text-slate-300 dark:text-slate-600 text-2xl mb-2 block"></i>
                            Tidak ada data MPR yang cocok dengan pencarian Anda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Search & Status Filtering
    function filterStatusMpr(status) {
        document.querySelectorAll('.chip-filter-mpr').forEach(btn => {
            if (btn.getAttribute('data-status') === status) {
                btn.className = "chip-filter-mpr active px-3 py-1 rounded-lg font-bold text-white bg-sky-600 shadow-sm transition-all cursor-pointer";
            } else {
                btn.className = "chip-filter-mpr px-3 py-1 rounded-lg font-semibold text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-white transition-all cursor-pointer";
            }
        });
        applyMprFilters();
    }

    const searchInputMpr = document.getElementById('liveSearchMpr');
    if (searchInputMpr) {
        searchInputMpr.addEventListener('input', applyMprFilters);
    }

    function applyMprFilters() {
        const query = (document.getElementById('liveSearchMpr')?.value || '').toLowerCase().trim();
        const activeChip = document.querySelector('.chip-filter-mpr.active');
        const selectedStatus = activeChip ? activeChip.getAttribute('data-status') : 'all';

        const rows = document.querySelectorAll('.row-mpr');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const statusMatch = (selectedStatus === 'all') || (rowStatus === selectedStatus);

            let textMatch = true;
            if (query.length > 0) {
                const searchTargets = row.querySelectorAll('.target-search');
                let rowText = '';
                searchTargets.forEach(t => rowText += ' ' + t.innerText);
                textMatch = rowText.toLowerCase().includes(query);
            }

            if (statusMatch && textMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultRow = document.getElementById('noSearchResultRowMpr');
        if (noResultRow) {
            noResultRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }

</script>
@endpush

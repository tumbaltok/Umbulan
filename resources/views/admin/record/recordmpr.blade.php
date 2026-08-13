@extends('layouts.app')
@section('title', 'Record MPR Karyawan')

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check mr-2 text-emerald-500 text-base"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Header & Panel Filter / Export --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-sky-600/10 text-sky-600 flex items-center justify-center font-bold text-xl shrink-0 border border-sky-100">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Record Material Purchase Request (MPR)</h2>
                        <p class="text-sm text-slate-500 mt-0.5">Daftar log data seluruh staf yang mengajukan pembelian material/barang.</p>
                    </div>
                </div>

                {{-- Tombol Export Data --}}
                <div class="w-full md:w-auto flex items-center justify-end">
                    <button type="button" onclick="exportExcel()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-emerald-600/20 flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-file-excel"></i>
                        Export Data
                    </button>
                </div>
            </div>

            {{-- Panel Toolbar Tambahan: Search & Dropdown Filter --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-2">
                {{-- Live Search Bar --}}
                <div class="relative w-full md:w-96">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="table-search" placeholder="Cari pemohon, nomor MPR, atau barang..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all shadow-sm">
                </div>

                {{-- Form Filter Bulan & Tahun --}}
                <form action="{{ route('admin.record.mpr') }}" method="GET" id="form-filter" class="w-full md:w-auto flex flex-wrap items-center justify-end gap-2 m-0">
                    {{-- Pilihan Bulan --}}
                    <div class="w-full sm:w-36">
                        <select name="bulan" id="filter_bulan" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-sm cursor-pointer">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ sprintf('%02d', $m) }}" {{ request('bulan') == sprintf('%02d', $m) ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilihan Tahun --}}
                    <div class="w-full sm:w-28">
                        <select name="tahun" id="filter_tahun" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 shadow-sm cursor-pointer">
                            @php $tahunSekarang = date('Y'); @endphp
                            @foreach(range($tahunSekarang, $tahunSekarang - 5) as $y)
                                <option value="{{ $y }}" {{ request('tahun', $tahunSekarang) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Record --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="mpr-table">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(0)">
                            <div class="flex items-center gap-1.5">
                                Pemohon & Stasiun
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(1)">
                            <div class="flex items-center gap-1.5">
                                No. MPR & Tanggal
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(2)">
                            <div class="flex items-center gap-1.5">
                                Detail Material
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(3)">
                            <div class="flex items-center gap-1.5">
                                Estimasi Biaya
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(4)">
                            <div class="flex items-center justify-center gap-1.5">
                                Status & Berkas
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="table-body">
                    @php $dataMpr = $daftarMpr ?? $recordMpr ?? []; @endphp
                    @forelse($dataMpr as $mpr)
                        @php 
                            $karyawan = $mpr->user; 
                            $grandTotal = $mpr->items ? $mpr->items->sum(function($i) { return $i->jumlah * $i->estimasi_harga; }) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors table-row-item">
                            {{-- Kolom Karyawan Pemohon & Stasiun --}}
                            <td class="px-6 py-4 font-medium text-slate-900 data-name" data-value="{{ strtolower($karyawan->name ?? '') }} {{ strtolower($karyawan->nip ?? '') }}">
                                <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id ?? '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-sky-600/20 overflow-hidden border border-white shrink-0">
                                        @if($karyawan && $karyawan->profile_photo)
                                            <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($karyawan->name ?? '??', 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors">
                                            {{ $karyawan->name ?? 'Nama Tidak Diketahui' }}
                                        </span>
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 text-xs text-slate-400 mt-0.5">
                                            <span>NIP: {{ $karyawan->nip ?? '-' }}</span>
                                            <span class="hidden sm:inline text-slate-300">•</span>
                                            <span class="text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md text-[10px] font-semibold border border-sky-100">
                                                📍 {{ $karyawan->station->name ?? 'Stasiun Umbulan' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Nomor MPR & Tanggal --}}
                            <td class="px-6 py-4 data-nomor" data-value="{{ strtolower($mpr->nomor_mpr ?? '') }}">
                                <div class="flex flex-col">
                                    <span class="text-sky-600 font-bold text-xs bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-100 inline-block w-fit">
                                        {{ $mpr->nomor_mpr }}
                                    </span>
                                    <span class="text-xs text-slate-400 mt-1">
                                        <i class="fa-regular fa-calendar text-[11px] mr-1"></i>
                                        {{ Carbon\Carbon::parse($mpr->tanggal_pengajuan)->isoFormat('D MMMM Y') }}
                                    </span>
                                </div>
                            </td>

                            {{-- Kolom Detail Barang / Material --}}
                            <td class="px-6 py-4 data-barang" data-value="{{ strtolower($mpr->items->pluck('nama_barang')->implode(' ')) }} {{ strtolower($mpr->keperluan_urgensi ?? '') }}">
                                <div class="flex flex-col gap-1.5 max-w-xs">
                                    <div class="bg-slate-100/80 p-2 rounded-xl border border-slate-200/60 mb-1">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Urgensi Keperluan:</span>
                                        <p class="text-xs text-slate-700 font-medium line-clamp-2" title="{{ $mpr->keperluan_urgensi }}">
                                            {{ $mpr->keperluan_urgensi }}
                                        </p>
                                    </div>
                                    
                                    <div class="space-y-1">
                                        @foreach($mpr->items as $item)
                                            <div class="flex items-center justify-between text-xs bg-white px-2.5 py-1 rounded-lg border border-slate-100 shadow-2xs">
                                                <span class="font-semibold text-slate-800 truncate">{{ $item->nama_barang }}</span>
                                                <span class="text-slate-500 font-bold ml-2 shrink-0 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ $item->jumlah }} {{ $item->satuan }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Estimasi Total Harga --}}
                            <td class="px-6 py-4 data-harga" data-value="{{ $grandTotal }}">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800">
                                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">
                                        {{ $mpr->items->count() }} Macam Item
                                    </span>
                                </div>
                            </td>

                            {{-- Kolom Status & Dokumen --}}
                            <td class="px-6 py-4 text-center data-status" data-value="{{ strtolower($mpr->status_akhir ?? 'pending') }}">
                                <div class="flex flex-col items-center gap-2">
                                    @if($mpr->status_akhir === 'approved')
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl text-xs font-bold uppercase shadow-2xs flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Disetujui
                                        </span>
                                    @elseif($mpr->status_akhir === 'rejected')
                                        <span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold uppercase shadow-2xs flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-xl text-xs font-bold uppercase shadow-2xs flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                    @endif

                                    {{-- Dokumen Lampiran --}}
                                    @if($mpr->dokumen_pendukung)
                                        <a href="{{ asset('storage/' . $mpr->dokumen_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-sky-600 hover:text-sky-700 font-bold bg-sky-50 px-2 py-1 rounded-lg border border-sky-100 transition-colors">
                                            <i class="fa-solid fa-paperclip"></i> Lihat Berkas
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-300 italic">Tidak ada berkas</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-data-row">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-boxes-packing text-4xl mb-3 block text-slate-200"></i>
                                Belum ada record data pengajuan MPR pada periode filter ini.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Row Search Tidak Ditemukan --}}
                    <tr id="search-not-found" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass-minus text-4xl mb-3 block text-slate-200"></i>
                            Data karyawan, nomor MPR, atau nama barang tidak ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP DETAIL KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-id-card text-sky-500"></i> Detail Lengkap Karyawan
            </h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        <div id="modalDataContent" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 bg-sky-50 px-3 py-1 rounded-full mt-1 border border-sky-100"></p>
            </div>

            <div class="border-t border-slate-100 pt-4 grid grid-cols-1 gap-y-3.5 text-sm">
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">NIP</span>
                    <span id="detail_nip" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">Email</span>
                    <span id="detail_email" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">No. Telepon</span>
                    <a id="detail_phone_link" href="#" target="_blank" class="col-span-2 text-slate-800 font-semibold hover:text-emerald-600 transition-colors hidden">-</a>
                    <span id="detail_phone" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">Jobdesk</span>
                    <span id="detail_job" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 pb-2">
                    <span class="text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-colors">
                 Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sortDirections = [true, true, true, true, true];

    // --- FUNGSI SORTING TABEL ---
    function sortTable(columnIndex) {
        const tableBody = document.getElementById("table-body");
        const rows = Array.from(tableBody.querySelectorAll(".table-row-item"));

        if (rows.length === 0) return;

        const isAscending = sortDirections[columnIndex];

        rows.sort((rowA, rowB) => {
            const cellA = rowA.children[columnIndex];
            const cellB = rowB.children[columnIndex];

            let valA = cellA.getAttribute("data-value") || cellA.textContent.trim();
            let valB = cellB.getAttribute("data-value") || cellB.textContent.trim();

            if (columnIndex === 3) {
                return isAscending ? Number(valA) - Number(valB) : Number(valB) - Number(valA);
            }

            return isAscending
                ? valA.localeCompare(valB, undefined, {numeric: true, sensitivity: 'base'})
                : valB.localeCompare(valA, undefined, {numeric: true, sensitivity: 'base'});
        });

        rows.forEach(row => tableBody.appendChild(row));
        sortDirections[columnIndex] = !isAscending;

        const headers = document.querySelectorAll("#mpr-table th i");
        headers.forEach((icon, index) => {
            if (index === columnIndex) {
                icon.className = isAscending ? "fa-solid fa-sort-up text-sky-600" : "fa-solid fa-sort-down text-sky-600";
            } else {
                icon.className = "fa-solid fa-sort text-[10px] text-slate-300";
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        // --- REAL-TIME LIVE SEARCH ---
        const searchInput = document.getElementById("table-search");
        const rowItems = document.querySelectorAll(".table-row-item");
        const notFoundRow = document.getElementById("search-not-found");

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const keyword = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rowItems.forEach(row => {
                    const nameCell = row.querySelector(".data-name");
                    const nomorCell = row.querySelector(".data-nomor");
                    const barangCell = row.querySelector(".data-barang");

                    const nameText = nameCell ? nameCell.getAttribute("data-value") : "";
                    const nomorText = nomorCell ? nomorCell.getAttribute("data-value") : "";
                    const barangText = barangCell ? barangCell.getAttribute("data-value") : "";

                    if (nameText.includes(keyword) || nomorText.includes(keyword) || barangText.includes(keyword)) {
                        row.classList.remove("hidden");
                        visibleCount++;
                    } else {
                        row.classList.add("hidden");
                    }
                });

                if (visibleCount === 0 && rowItems.length > 0) {
                    notFoundRow.classList.remove("hidden");
                } else {
                    notFoundRow.classList.add("hidden");
                }
            });
        }

        // --- MODAL POPUP DETAIL KARYAWAN ---
        const modal = document.getElementById("detailKaryawanModal");
        const backdrop = document.getElementById("detailModalBackdrop");
        const closeBtn = document.getElementById("closeDetailModalBtn");
        const closeBtn2 = document.getElementById("closeDetailModalBtn2");

        const loadingSection = document.getElementById("modalLoading");
        const contentSection = document.getElementById("modalDataContent");

        function showModal() {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function hideModal() {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        if (closeBtn) closeBtn.addEventListener("click", hideModal);
        if (closeBtn2) closeBtn2.addEventListener("click", hideModal);
        if (backdrop) backdrop.addEventListener("click", hideModal);

        document.querySelectorAll(".btn-detail-karyawan").forEach(button => {
            button.addEventListener("click", function () {
                const karyawanId = this.getAttribute("data-id");
                if (!karyawanId) return;

                showModal();
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");

                fetch(`/admin/karyawan/${karyawanId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (!data || Object.keys(data).length === 0) throw new Error("Kosong");

                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        document.getElementById("detail_name").textContent = data.name || '-';
                        document.getElementById("detail_nip").textContent = data.nip ? data.nip : '-';
                        document.getElementById("detail_email").textContent = data.email || '-';
                        document.getElementById("detail_phone").textContent = data.phone_number ? data.phone_number : '-';
                        document.getElementById("detail_role").textContent = data.role_name ? data.role_name : 'Tidak Ada Role';
                        document.getElementById("detail_station").textContent = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                        const phoneLink = document.getElementById("detail_phone_link");
                        const phoneSpan = document.getElementById("detail_phone");

                        if (data.phone_number) {
                            let cleanNumber = data.phone_number.replace(/[^0-9]/g, '');
                            if (cleanNumber.startsWith('0')) {
                                cleanNumber = '62' + cleanNumber.substring(1);
                            }
                            phoneLink.textContent = data.phone_number;
                            phoneLink.href = `https://wa.me/${cleanNumber}`;
                            phoneLink.classList.remove("hidden");
                            phoneSpan.classList.add("hidden");
                        } else {
                            phoneLink.classList.add("hidden");
                            phoneSpan.classList.remove("hidden");
                            phoneSpan.textContent = '-';
                        }

                        let jobTitleText = 'Belum Memilih';
                        if(data.job_title == 'Operator' || data.job_title == '1') jobTitleText = 'Operator';
                        else if(data.job_title == 'Maintenance' || data.job_title == '2') jobTitleText = 'Maintenance';
                        else if(data.job_title == 'HSE' || data.job_title == '3') jobTitleText = 'Safety (HSE)';
                        else if(data.job_title == 'Dokumentasi' || data.job_title == '4') jobTitleText = 'Documenter';

                        document.getElementById("detail_job").textContent = jobTitleText;

                        const photoContainer = document.getElementById("detail_photo_container");
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
                    })
                    .catch(error => {
                        console.error(error);
                        loadingSection.classList.add("hidden");
                        hideModal();
                    });
            });
        });

        // AUTO-SUBMIT DROPDOWN FILTER
        const formFilter = document.getElementById('form-filter');
        const filterBulan = document.getElementById('filter_bulan');
        const filterTahun = document.getElementById('filter_tahun');

        if (filterBulan) filterBulan.addEventListener('change', () => formFilter.submit());
        if (filterTahun) filterTahun.addEventListener('change', () => formFilter.submit());

        // EXPORT EXCEL
        window.exportExcel = function() {
            const bulan = document.getElementById('filter_bulan').value;
            const year = document.getElementById('filter_tahun').value;
            window.location.href = `{{ route('admin.record.mpr.export') }}?bulan=${bulan}&tahun=${year}`;
        }
    });
</script>
@endpush
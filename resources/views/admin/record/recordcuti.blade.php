@extends('layouts.app')
@section('title', 'Riwayat Cuti Karyawan')
@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
    {{-- Notifikasi Sukses --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-check mr-2 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {{-- Header & Panel Filter / Export --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Record Riwayat Cuti Karyawan</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Daftar staf yang memiliki riwayat cuti beserta detail pengajuannya.</p>
                </div>

                <div class="w-full md:w-auto flex items-center justify-end">
                    <button type="button" onclick="exportExcel()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-file-excel"></i>
                        Export Data
                    </button>
                </div>
            </div>

            {{-- Panel Toolbar Tambahan: Search & Dropdown Filter --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-2">
                <div class="relative w-full md:w-96">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="table-search" placeholder="Cari nama karyawan, NIP, atau jabatan..." class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all">
                </div>

                <form action="{{ route('admin.record.cuti') }}" method="GET" id="form-filter" class="w-full md:w-auto flex flex-wrap items-center justify-end gap-2 m-0">
                    <div class="w-full sm:w-36">
                        <select name="bulan" id="filter_bulan" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 cursor-pointer">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-28">
                        <select name="tahun" id="filter_tahun" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 cursor-pointer">
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

        @php
            // Grouping data berdasarkan User ID agar 1 Karyawan hanya muncul 1 kali
            $groupedCuti = $daftarCuti->groupBy('user_id');
        @endphp

        {{-- Tabel Record Unik per Karyawan --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="cuti-table">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(0)">
                            <div class="flex items-center gap-1.5">
                                Nama Lengkap
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors group" onclick="sortTable(1)">
                            <div class="flex items-center gap-1.5">
                                Jabatan
                                <i class="fa-solid fa-sort text-[10px] text-slate-300 group-hover:text-slate-400 transition-colors"></i>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center">Total Frekuensi Cuti</th>
                        <th class="px-6 py-4 text-center">Total Cuti Terpakai</th>
                        <th class="px-6 py-4 text-center w-36">Aksi History</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="table-body">
                    @forelse($groupedCuti as $userId => $items)
                        @php
                            $karyawan = $items->first()->user;
                            $totalFrekuensi = $items->count();
                            $totalHariTerpakai = $items->sum(function($item) {
                                return $item->total_hari ?? $item->durasi_hari ?? 0;
                            });

                            $historyData = $items->map(function($item) {
                                return [
                                    'jenis' => $item->jenisCuti->name_cuti ?? 'Cuti/Izin',
                                    'sub' => $item->subCuti->nama_sub_cuti ?? $item->alasan_cuti ?? '-',
                                    'durasi' => $item->total_hari ?? $item->durasi_hari ?? 0,
                                    'mulai' => \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('D MMMM Y'),
                                    'selesai' => \Carbon\Carbon::parse($item->tanggal_selesai)->isoFormat('D MMMM Y'),
                                ];
                            });
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors table-row-item">
                            {{-- Kolom Nama & Foto Profil --}}
                            <td class="px-6 py-4 font-medium text-slate-900 data-name" data-value="{{ strtolower($karyawan->name ?? '') }} {{ strtolower($karyawan->nip ?? '') }}">
                                <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id ?? '' }}">
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden border border-slate-100 shrink-0">
                                        @if($karyawan && $karyawan->profile_photo)
                                            <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($karyawan->name ?? '??', 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors">{{ $karyawan->name ?? '-' }}</span>
                                        <span class="text-xs text-slate-400">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Jabatan --}}
                            <td class="px-6 py-4 data-role" data-value="{{ strtolower($karyawan->role->role_name ?? '') }}">
                                @php $roleName = $karyawan->role->role_name ?? 'Tidak Ada Role'; @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block
                                    {{ strtolower($roleName) == 'manager' ? 'bg-purple-50 text-purple-700 border border-purple-100' : (strtolower($roleName) == 'supervisor' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700 border border-slate-200/50') }}">
                                    {{ $roleName }}
                                </span>
                            </td>

                            {{-- Frekuensi Pengajuan Cuti --}}
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                    {{ $totalFrekuensi }} Kali Pengajuan
                                </span>
                            </td>

                            {{-- Total Hari Terpakai --}}
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $totalHariTerpakai }} Hari
                                </span>
                            </td>

                            {{-- Tombol Lihat History --}}
                            <td class="px-6 py-4 text-center">
                                <button type="button"
                                    data-karyawan="{{ $karyawan->name ?? '' }}"
                                    data-history='@json($historyData)'
                                    onclick="bukaModalHistory(this)"
                                    class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition-colors shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-clock-rotate-left"></i> History Cuti
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-data-row">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block text-slate-200"></i>
                                Belum ada record pengambilan cuti pada periode filter ini.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="search-not-found" class="hidden">
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass-minus text-3xl mb-2 block text-slate-200"></i>
                            Data karyawan tidak ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP HISTORY CUTI SPESIFIK KARYAWAN --}}
<div id="modalHistoryCuti" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHistory()"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative z-10 transform transition-all m-4 max-h-[85vh] flex flex-col border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base" id="judulModalHistory">History Pengajuan Cuti</h3>
                <p id="subjudulModalHistory" class="text-xs text-sky-600 font-medium mt-0.5"></p>
            </div>
            <button type="button" onclick="tutupModalHistory()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 pr-1 space-y-3">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-4 py-3">Jenis Cuti</th>
                        <th class="px-4 py-3">Sub Cuti / Alasan</th>
                        <th class="px-4 py-3 text-center">Durasi</th>
                        <th class="px-4 py-3 text-center">Rentang Tanggal</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody" class="divide-y divide-slate-100 text-slate-700">
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end border-t border-slate-100 pt-4 mt-4">
            <button type="button" onclick="tutupModalHistory()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- MODAL POPUP DETAIL KARYAWAN --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="modalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        <div id="modalDataContent" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50"></div>
                <h4 id="detail_name" class="font-bold text-lg text-slate-800"></h4>
                <p id="detail_role" class="text-xs font-semibold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full mt-1 border border-sky-100"></p>
            </div>

            <div class="border-t border-slate-100 pt-4 grid grid-cols-1 gap-y-4 text-sm">
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
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors cursor-pointer">
                 Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bukaModalHistory(btn) {
        const namaKaryawan = btn.getAttribute('data-karyawan');
        const historyData = JSON.parse(btn.getAttribute('data-history'));

        document.getElementById('subjudulModalHistory').textContent = `Karyawan: ${namaKaryawan}`;

        const tableBody = document.getElementById('historyTableBody');
        let html = '';

        if (historyData && historyData.length > 0) {
            historyData.forEach(item => {
                html += `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-800">
                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200">${item.jenis}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 font-medium">${item.sub}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md font-bold">${item.durasi} Hari</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-500 font-mono text-[11px]">
                            ${item.mulai} s/d ${item.selesai}
                        </td>
                    </tr>
                `;
            });
        } else {
            html = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">Tidak ada catatan history cuti.</td>
                </tr>
            `;
        }

        tableBody.innerHTML = html;

        const modal = document.getElementById('modalHistoryCuti');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalHistory() {
        const modal = document.getElementById('modalHistoryCuti');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("table-search");
        const rowItems = document.querySelectorAll(".table-row-item");
        const notFoundRow = document.getElementById("search-not-found");

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const keyword = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rowItems.forEach(row => {
                    const nameCell = row.querySelector(".data-name");
                    const roleCell = row.querySelector(".data-role");

                    const nameText = nameCell ? nameCell.getAttribute("data-value") : "";
                    const roleText = roleCell ? roleCell.getAttribute("data-value") : "";

                    if (nameText.includes(keyword) || roleText.includes(keyword)) {
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

        const formFilter = document.getElementById('form-filter');
        const filterBulan = document.getElementById('filter_bulan');
        const filterTahun = document.getElementById('filter_tahun');

        if (filterBulan) filterBulan.addEventListener('change', () => formFilter.submit());
        if (filterTahun) filterTahun.addEventListener('change', () => formFilter.submit());

        window.exportExcel = function() {
            const bulan = document.getElementById('filter_bulan').value;
            const year = document.getElementById('filter_tahun').value;
            window.location.href = `{{ route('admin.record.cuti.export') }}?bulan=${bulan}&tahun=${year}`;
        }
    });
</script>
@endpush

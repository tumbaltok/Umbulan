@extends('layouts.app')
@section('title', 'Daftar Karyawan')
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
        {{-- Header & Fitur Cari --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Manajemen Karyawan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data seluruh staf, hak akses role, penempatan stasiun kerja, dan informasi akun.</p>
            </div>
            {{-- Input Pencarian Nama --}}
            <div class="relative w-full md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" id="searchKaryawanInput" placeholder="Cari nama karyawan..."
                    class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all text-slate-700 placeholder-slate-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="karyawanTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="0">
                            Nama Lengkap <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="1">
                            Jabatan <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="2">
                            Jobdesk <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                        </th>
                        <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="3">
                            Penempatan Stasiun <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                        </th>
                        <th class="px-6 py-4 text-center cursor-pointer hover:bg-slate-100/70 hover:text-slate-600 transition-colors" data-sort="4">
                            Status Operasional <i class="fa-solid fa-sort ml-1.5 text-slate-300"></i>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" id="karyawanTableBody">
                    @forelse($daftarKaryawan as $karyawan)
                        <tr class="hover:bg-slate-50/80 transition-colors table-row-item">
                            {{-- Kolom Nama & Foto Profil --}}
                            <td class="px-6 py-4 font-medium text-slate-900" data-search-value="{{ strtolower($karyawan->name) }}">
                                <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id }}">
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden border border-slate-100 shrink-0">
                                        @if($karyawan->profile_photo)
                                            <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($karyawan->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors target-search-name">{{ $karyawan->name }}</span>
                                        <span class="text-xs text-slate-400">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Jabatan (Role) --}}
                            <td class="px-6 py-4">
                                @php
                                    $roleName = $karyawan->role->role_name ?? 'Tidak Ada Role';
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block
                                    {{ strtolower($roleName) == 'manager' ? 'bg-purple-50 text-purple-700 border border-purple-100' : (strtolower($roleName) == 'supervisor' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700 border border-slate-200/50') }}">
                                    {{ $roleName }}
                                </span>
                            </td>

                            {{-- Kolom Jobdesk --}}
                            <td class="px-6 py-4 text-sm font-medium">
                                @if($karyawan->job_title == 'Operator' || $karyawan->job_title == '1')
                                    <span class="text-sky-600 bg-sky-50/50 px-2 py-0.5 rounded-md text-xs border border-sky-100">Operator</span>
                                @elseif($karyawan->job_title == 'Maintenance' || $karyawan->job_title == '2')
                                    <span class="text-amber-600 bg-amber-50/50 px-2 py-0.5 rounded-md text-xs border border-amber-100">Maintenance</span>
                                @elseif($karyawan->job_title == 'HSE' || $karyawan->job_title == '3')
                                    <span class="text-rose-600 bg-rose-50/50 px-2 py-0.5 rounded-md text-xs border border-rose-100">Safety (HSE)</span>
                                @elseif($karyawan->job_title == 'Dokumentasi' || $karyawan->job_title == '4')
                                    <span class="text-teal-600 bg-teal-50/50 px-2 py-0.5 rounded-md text-xs border border-teal-100">Documenter</span>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum Memilih</span>
                                @endif
                            </td>

                            {{-- Kolom Penempatan Stasiun --}}
                            <td class="px-6 py-4 text-center">
                                @if(($karyawan->station && !empty($karyawan->station->name)))
                                    <span class="inline-flex items-center text-xs text-slate-700 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/60">
                                        <i class="fa-solid fa-location-dot mr-1.5 text-rose-500 text-xs"></i>
                                        {{ $karyawan->station->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-rose-500 font-medium bg-rose-50 px-2 py-1 rounded-xl italic border border-rose-100">
                                        ⚠️ Stasiun Belum Diatur
                                    </span>
                                @endif
                            </td>

                            {{-- Status Operasional --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($karyawan->cuti_aktif && $karyawan->cuti_aktif->count() > 0)
                                    @php $cuti = $karyawan->cuti_aktif->first(); @endphp
                                    <span class="inline-flex items-center text-xs text-rose-600 bg-rose-50 border border-rose-100 px-2.5 py-1 rounded-full font-bold" title="{{ $cuti->alasan_cuti }}">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5 animate-pulse"></span>
                                        On Leave (Cuti)
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs {{ $karyawan->status_detail['badge_class'] }} border px-2.5 py-1 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 {{ $karyawan->status_detail['dot_class'] }} rounded-full mr-1.5 {{ $karyawan->status_detail['is_on'] ? 'animate-pulse' : '' }}"></span>
                                        {{ $karyawan->status_detail['label'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2 block text-slate-200"></i>
                                Belum ada data karyawan terdaftar di database.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultRow" class="hidden">
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-3xl mb-2 block text-slate-200"></i>
                            Karyawan dengan nama tersebut tidak ditemukan.
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

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto border border-slate-100">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
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
                
                {{-- EMAIL --}}
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">Email</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_email" class="text-slate-800 font-semibold truncate">-</span>
                        <a id="detail_email_link" href="#" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-sky-500 hover:bg-sky-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Email</span>
                        </a>
                    </div>
                </div>

                {{-- NO. TELEPON --}}
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2 items-center">
                    <span class="text-slate-400 font-medium">No. Telepon</span>
                    <div class="col-span-2 flex items-center space-x-2">
                        <span id="detail_phone" class="text-slate-800 font-semibold">-</span>
                        <a id="detail_phone_link" href="#" target="_blank" class="hidden inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all shrink-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>Chat WA</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">Jobdesk</span>
                    <span id="detail_job" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                
                <div class="grid grid-cols-3 border-b border-slate-50 pb-2">
                    <span class="text-slate-400 font-medium">Stasiun</span>
                    <span id="detail_station" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>

                {{-- SEKSI DETIL JADWAL KERJA & SHIFT --}}
                <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sistem Jadwal Kerja</span>
                        <span id="detail_schedule_badge" class="px-2 py-0.5 text-xs font-bold rounded-md"></span>
                    </div>

                    {{-- DETAIL JADWAL NORMAL --}}
                    <div id="detail_normal_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Kerja Masuk:</span>
                            <span id="detail_normal_days" class="font-semibold text-slate-700"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Hari Libur Kerja:</span>
                            <span class="font-semibold text-rose-600">Sabtu & Minggu</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jam Operasional:</span>
                            <span id="detail_normal_hours" class="font-semibold text-emerald-600"></span>
                        </div>
                    </div>

                    {{-- DETAIL JADWAL ROSTER --}}
                    <div id="detail_roster_schedule_box" class="hidden space-y-1.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Jadwal Shift Hari Ini:</span>
                            <span id="detail_roster_today_badge" class="px-2 py-0.5 rounded font-bold text-[11px]"></span>
                        </div>
                        <div id="detail_roster_hours_container" class="flex justify-between">
                            <span class="text-slate-400">Jam Shift Kerja:</span>
                            <span id="detail_roster_hours" class="font-semibold text-slate-700"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeDetailModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- SEKTOR REAL-TIME FILTER SEARCH ---
        const searchInput = document.getElementById("searchKaryawanInput");
        const noResultRow = document.getElementById("noResultRow");

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const filter = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll("#karyawanTableBody .table-row-item");
                let hasVisibleRow = false;

                rows.forEach(row => {
                    const nameCell = row.querySelector(".target-search-name");
                    if (nameCell) {
                        const nameText = nameCell.textContent.toLowerCase();
                        if (nameText.includes(filter)) {
                            row.style.setProperty('display', '', 'important');
                            hasVisibleRow = true;
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    }
                });

                if (rows.length > 0) {
                    if (!hasVisibleRow) {
                        noResultRow.classList.remove("hidden");
                    } else {
                        noResultRow.classList.add("hidden");
                    }
                }
            });
        }

        // --- SEKTOR SORTING UTK SEMUA HEADER ---
        const headers = document.querySelectorAll("#karyawanTable th[data-sort]");
        const tableBody = document.getElementById("karyawanTableBody");
        let currentSortColumn = -1;
        let isAscending = true;

        headers.forEach(header => {
            header.addEventListener("click", function () {
                const columnIndex = parseInt(this.getAttribute("data-sort"));
                const rowsArray = Array.from(tableBody.querySelectorAll(".table-row-item"));

                if (currentSortColumn === columnIndex) {
                    isAscending = !isAscending;
                } else {
                    isAscending = true;
                    currentSortColumn = columnIndex;
                }

                headers.forEach(h => {
                    const icon = h.querySelector("i");
                    if (icon) {
                        icon.className = "fa-solid fa-sort ml-1.5 text-slate-300";
                    }
                });

                const currentIcon = this.querySelector("i");
                if (currentIcon) {
                    currentIcon.className = isAscending
                        ? "fa-solid fa-sort-up ml-1.5 text-sky-600"
                        : "fa-solid fa-sort-down ml-1.5 text-sky-600";
                }

                rowsArray.sort((rowA, rowB) => {
                    let cellA = rowA.children[columnIndex].textContent.trim();
                    let cellB = rowB.children[columnIndex].textContent.trim();

                    cellA = cellA.replace(/[^\x20-\x7E]/g, '').trim();
                    cellB = cellB.replace(/[^\x20-\x7E]/g, '').trim();

                    return isAscending
                        ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' })
                        : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
                });

                rowsArray.forEach(row => tableBody.appendChild(row));
                if (noResultRow) tableBody.appendChild(noResultRow);
            });
        });

        // --- LOGIKA MODAL POPUP & FETCH DETAIL KARYAWAN ---
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

        document.addEventListener("click", function(e) {
            const button = e.target.closest(".btn-detail-karyawan");
            if (button) {
                const karyawanId = button.getAttribute("data-id");

                showModal();
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");

                fetch(`/admin/karyawan/${karyawanId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Gagal mengambil data (Status: ${response.status})`);
                        return response.json();
                    })
                    .then(data => {
                        if (!data || Object.keys(data).length === 0) throw new Error("Data karyawan kosong.");

                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        document.getElementById("detail_name").textContent = data.name || '-';
                        document.getElementById("detail_nip").textContent = data.nip ? data.nip : '-';
                        document.getElementById("detail_role").textContent = data.role_name ? data.role_name : 'Tidak Ada Role';
                        document.getElementById("detail_station").textContent = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                        // LOGIKA ELEMEN EMAIL
                        const emailSpan = document.getElementById("detail_email");
                        const emailLink = document.getElementById("detail_email_link");

                        if (data.email) {
                            emailSpan.textContent = data.email;
                            emailLink.href = `mailto:${data.email}`;
                            emailLink.classList.remove("hidden");
                        } else {
                            emailSpan.textContent = '-';
                            emailLink.classList.add("hidden");
                        }

                        // LOGIKA ELEMEN TELEPON (WHATSAPP)
                        const phoneSpan = document.getElementById("detail_phone");
                        const phoneLink = document.getElementById("detail_phone_link");

                        if (data.phone_number) {
                            phoneSpan.textContent = data.phone_number;

                            let cleanNumber = data.phone_number.replace(/[^0-9]/g, '');
                            if (cleanNumber.startsWith('0')) {
                                cleanNumber = '62' + cleanNumber.substring(1);
                            }

                            phoneLink.href = `https://wa.me/${cleanNumber}`;
                            phoneLink.classList.remove("hidden");
                        } else {
                            phoneSpan.textContent = '-';
                            phoneLink.classList.add("hidden");
                        }

                        let jobTitleText = 'Belum Memilih';
                        if(data.job_title == 'Operator' || data.job_title == '1') jobTitleText = 'Operator';
                        else if(data.job_title == 'Maintenance' || data.job_title == '2') jobTitleText = 'Maintenance';
                        else if(data.job_title == 'HSE' || data.job_title == '3') jobTitleText = 'Safety (HSE)';
                        else if(data.job_title == 'Dokumentasi' || data.job_title == '4') jobTitleText = 'Documenter';

                        document.getElementById("detail_job").textContent = jobTitleText;

                        // LOGIKA PENAMPILAN JADWAL KERJA & SHIFT
                        const scheduleBadge = document.getElementById("detail_schedule_badge");
                        const normalBox = document.getElementById("detail_normal_schedule_box");
                        const rosterBox = document.getElementById("detail_roster_schedule_box");

                        if (data.schedule_type === 'normal') {
                            scheduleBadge.textContent = 'Jadwal Normal';
                            scheduleBadge.className = 'px-2 py-0.5 text-xs font-bold rounded-md bg-sky-100 text-sky-700 border border-sky-200';
                            
                            normalBox.classList.remove('hidden');
                            rosterBox.classList.add('hidden');

                            document.getElementById("detail_normal_days").textContent = data.normal_work_days;
                            document.getElementById("detail_normal_hours").textContent = `${data.normal_check_in} - ${data.normal_check_out} WIB`;
                        } else {
                            scheduleBadge.textContent = 'Jadwal Roster';
                            scheduleBadge.className = 'px-2 py-0.5 text-xs font-bold rounded-md bg-purple-100 text-purple-700 border border-purple-200';

                            rosterBox.classList.remove('hidden');
                            normalBox.classList.add('hidden');

                            const rosterTodayBadge = document.getElementById("detail_roster_today_badge");
                            const rosterHoursContainer = document.getElementById("detail_roster_hours_container");
                            const rosterHours = document.getElementById("detail_roster_hours");

                            if (data.today_shift_type === 'pagi') {
                                rosterTodayBadge.textContent = 'Shift Pagi';
                                rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-emerald-100 text-emerald-700 border border-emerald-200';
                                rosterHoursContainer.classList.remove('hidden');
                                rosterHours.textContent = `${data.today_scheduled_in} - ${data.today_scheduled_out} WIB`;
                            } else if (data.today_shift_type === 'malam') {
                                rosterTodayBadge.textContent = 'Shift Malam';
                                rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-indigo-100 text-indigo-700 border border-indigo-200';
                                rosterHoursContainer.classList.remove('hidden');
                                rosterHours.textContent = `${data.today_scheduled_in} - ${data.today_scheduled_out} WIB`;
                            } else {
                                rosterTodayBadge.textContent = 'OFF / Libur Roster';
                                rosterTodayBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-rose-100 text-rose-700 border border-rose-200';
                                rosterHoursContainer.classList.add('hidden');
                            }
                        }

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
                        alert(`Terjadi kesalahan saat memuat data karyawan: ${error.message}`);
                        hideModal();
                    });
            }
        });
    });
</script>
@endpush
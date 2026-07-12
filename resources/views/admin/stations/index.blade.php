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
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4 w-20 text-center">ID</th>
                        <th class="px-6 py-4">Nama Sektor / Stasiun</th>
                        <th class="px-6 py-4 text-center">Total Penempatan Staf</th>
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
                                <span data-id="{{ $stasiun->id }}" data-name="{{ $stasiun->name }}"
                                    class="btn-view-staff px-3 py-1 rounded-full text-xs font-bold font-mono transition-all duration-200
                                    {{ $stasiun->total_karyawan > 0 ? 'bg-sky-50 text-sky-700 border border-sky-100 hover:bg-sky-100 hover:text-sky-800 cursor-pointer shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                    {{ $stasiun->total_karyawan }} Orang
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-slate-400">
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

{{-- MODAL POPUP VIEW DAFTAR KARYAWAN --}}
<div id="staffStationModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="staffModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative z-10 transform transition-all m-4 max-h-[85vh] flex flex-col">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Daftar Anggota Staf</h3>
                <p id="modalStationTitle" class="text-xs text-sky-600 font-medium mt-0.5"></p>
            </div>
            <button type="button" id="closeStaffModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Konten Loading --}}
        <div id="modalStaffLoading" class="py-12 text-center my-auto">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Menarik data staf...</p>
        </div>

        {{-- Konten Utama (Tabel dengan style sesuai gambar contoh) --}}
        <div id="modalStaffContent" class="hidden overflow-y-auto my-4 flex-1 pr-1">
            <table class="w-full text-left border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                        <th class="px-6 pb-1">Nama Lengkap</th>
                        <th class="px-6 pb-1">Jabatan</th>
                    </tr>
                </thead>
                <tbody id="staffListContainer">
                    {{-- Data Karyawan di-inject lewat JavaScript dengan template style mirip gambar --}}
                </tbody>
            </table>
        </div>

        {{-- Footer Modal --}}
        <div class="flex items-center justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeStaffModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("staffStationModal");
        const backdrop = document.getElementById("staffModalBackdrop");
        const closeBtn = document.getElementById("closeStaffModalBtn");
        const closeBtn2 = document.getElementById("closeStaffModalBtn2");

        const loadingSection = document.getElementById("modalStaffLoading");
        const contentSection = document.getElementById("modalStaffContent");
        const stationTitle = document.getElementById("modalStationTitle");
        const staffContainer = document.getElementById("staffListContainer");

        function openModal() {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        function closeModal() {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (closeBtn2) closeBtn2.addEventListener("click", closeModal);
        if (backdrop) backdrop.addEventListener("click", closeModal);

        document.querySelectorAll(".btn-view-staff").forEach(badge => {
            badge.addEventListener("click", function () {
                const stationId = this.getAttribute("data-id");
                const stationName = this.getAttribute("data-name");

                if (!stationId || this.classList.contains('cursor-not-allowed')) return;

                openModal();
                stationTitle.textContent = `Stasiun Kerja: ${stationName}`;
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");
                staffContainer.innerHTML = "";

                fetch(`/admin/stations/${stationId}/karyawan`)
                    .then(response => {
                        if (!response.ok) throw new Error(`Gagal mengambil data staf (Status: ${response.status})`);
                        return response.json();
                    })
                    .then(karyawanList => {
                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        if (!karyawanList || karyawanList.length === 0) {
                            staffContainer.innerHTML = `
                                <tr>
                                    <td colspan="2" class="text-center py-8 text-slate-400 text-sm bg-white rounded-2xl border border-slate-100 shadow-sm">
                                        Tidak ada karyawan yang aktif di stasiun ini.
                                    </td>
                                </tr>`;
                            return;
                        }

                        karyawanList.forEach(karyawan => {
                            // 1. Logika Foto Profil / Inisial Lingkaran Bulat Biru
                            const initials = karyawan.name ? karyawan.name.substring(0, 2).toUpperCase() : '??';
                            const photoHtml = karyawan.profile_photo
                                ? `<img src="/storage/${karyawan.profile_photo}" class="w-full h-full object-cover">`
                                : initials;

                            // 2. Klasifikasi Badge Jabatan/Role disamakan dengan warna screenshot
                            const roleName = karyawan.role_name || (karyawan.role ? karyawan.role.role_name : 'Staff');
                            let roleBadgeClass = 'bg-slate-100 text-slate-700 border border-slate-200/50';

                            if (roleName.toLowerCase() === 'manager') {
                                roleBadgeClass = 'bg-purple-50 text-purple-700 border border-purple-100';
                            } else if (roleName.toLowerCase() === 'supervisor') {
                                roleBadgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-100';
                            } else if (roleName.toLowerCase() === 'admin') {
                                roleBadgeClass = 'bg-slate-100 text-slate-700 border border-slate-200/50';
                            } else if (roleName.toLowerCase() === 'staff') {
                                roleBadgeClass = 'bg-sky-50 text-sky-700 border border-sky-100';
                            }

                            // 3. Render Baris dengan struktur shadow-sm terpisah (borderless spacing) mirip gambar
                            const tableRow = document.createElement("tr");
                            tableRow.className = "bg-white hover:bg-slate-50/50 transition-colors group shadow-sm border border-slate-100 rounded-2xl";
                            tableRow.innerHTML = `
                                {{-- Kolom Nama Lengkap (Sisi Kiri) --}}
                                <td class="px-6 py-4 font-medium text-slate-900 rounded-l-2xl border-y border-l border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        {{-- Avatar Bulat Sempurna (Sesuai Gambar) --}}
                                        <div class="w-9 h-9 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden shrink-0">
                                            ${photoHtml}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 transition-colors">${karyawan.name}</span>
                                            <span class="text-xs text-slate-400 mt-0.5">NIP: ${karyawan.nip || '-'}</span>
                                        </div>
                                    </div>
                                </td>
                                {{-- Kolom Jabatan/Role (Sisi Kanan) --}}
                                <td class="px-6 py-4 align-middle rounded-r-2xl border-y border-r border-slate-100">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block ${roleBadgeClass}">
                                        ${roleName}
                                    </span>
                                </td>
                            `;
                            staffContainer.appendChild(tableRow);
                        });
                    })
                    .catch(error => {
                        console.error(error);
                        loadingSection.classList.add("hidden");
                        staffContainer.innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center py-8 text-rose-500 text-xs font-semibold bg-white rounded-2xl border border-slate-100 shadow-sm">
                                    ⚠️ Terjadi masalah sistem: ${error.message}
                                </td>
                            </tr>`;
                        contentSection.classList.remove("hidden");
                    });
            });
        });
    });
</script>
@endpush

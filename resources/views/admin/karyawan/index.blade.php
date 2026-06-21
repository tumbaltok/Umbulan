@extends('layouts.app')

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
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Manajemen Karyawan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data seluruh staf, hak akses role, penempatan stasiun kerja, dan informasi akun.</p>
            </div>
            {{-- <a href="#" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Karyawan
            </a> --}}
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Nama Lengkap</th>
                        {{-- <th class="px-6 py-4">Email</th> --}}
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">Jobdesk</th>
                        <th class="px-6 py-4 text-center">Penempatan Stasiun</th>
                        <th class="px-6 py-4 text-center">Status Operasional</th>
                        {{-- <th class="px-6 py-4 text-center">Status Data</th> --}}
                        {{-- <th class="px-6 py-4 text-center">Aksi</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarKaryawan as $karyawan)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- Kolom Nama & Foto Profil --}}
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div class="flex items-center space-x-3 btn-detail-karyawan cursor-pointer group" data-id="{{ $karyawan->id }}">
                                    {{-- Foto Profil --}}
                                    <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shadow-sm overflow-hidden border border-slate-100 shrink-0">
                                        @if($karyawan->profile_photo)
                                            <img src="{{ asset('storage/' . $karyawan->profile_photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($karyawan->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 font-semibold text-sm group-hover:text-sky-600 group-hover:underline transition-colors">{{ $karyawan->name }}</span>
                                        <span class="text-xs text-slate-400">NIP: {{ $karyawan->nip ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Email --}}
                            {{-- <td class="px-6 py-4 text-slate-600 text-sm">{{ $karyawan->email }}</td> --}}

                            {{-- Kolom Jabatan (Role) --}}
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold inline-block
                                    {{ strtolower($karyawan->role_name) == 'manager' ? 'bg-purple-50 text-purple-700 border border-purple-100' : (strtolower($karyawan->role_name) == 'supervisor' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700 border border-slate-200/50') }}">
                                    {{ $karyawan->role_name ?? 'Tidak Ada Role' }}
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
                                @if(!empty($karyawan->nama_stasiun))
                                    <span class="inline-flex items-center text-xs text-slate-700 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/60">
                                        <i class="fa-solid fa-location-dot mr-1.5 text-rose-500 text-xs"></i>
                                        {{ $karyawan->nama_stasiun }}
                                    </span>
                                @else
                                    <span class="text-xs text-rose-500 font-medium bg-rose-50 px-2 py-1 rounded-xl italic border border-rose-100">
                                        ⚠️ Stasiun Belum Diatur
                                    </span>
                                @endif
                            </td>

                            {{-- Status Operasional --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(!empty($karyawan->sedang_cuti))
                                    {{-- KONDISI 1: SEDANG CUTI --}}
                                    <span class="inline-flex items-center text-xs text-rose-600 bg-rose-50 border border-rose-100 px-2.5 py-1 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5 animate-pulse"></span>
                                        On Leave (Cuti)
                                    </span>

                                @elseif(now()->isSunday())
                                {{-- @elseif($karyawan->status_jadwal == 'OFF') --}}
                                    {{-- KONDISI 2: LIBUR / OFF (Contoh jika otomatis hari Minggu libur) --}}
                                    {{-- Anda juga bisa menggantinya dengan mengecek kolom jadwal dari database jika ada --}}
                                    <span class="inline-flex items-center text-xs text-amber-600 bg-amber-50 border border-amber-100 px-2.5 py-1 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5"></span>
                                        OFF (Libur)
                                    </span>

                                @else
                                    {{-- KONDISI 3: MASUK KERJA / AKTIF --}}
                                    <span class="inline-flex items-center text-xs text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                                        Ready (Kerja)
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom Status Data --}}
                            {{-- <td class="px-6 py-4 text-center">
                                @if(!empty($karyawan->station_id))
                                    <span class="text-xs text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full font-semibold">Ready</span>
                                @else
                                    <span class="text-xs text-amber-600 bg-amber-50 border border-amber-100 px-2.5 py-1 rounded-full font-semibold cursor-help" title="Karyawan ini tidak akan muncul di SPV manapun saat ajukan cuti">Filter Terkunci</span>
                                @endif
                            </td> --}}

                            {{-- Kolom Aksi --}}
                            {{-- <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="#" class="px-3 py-1.5 bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 rounded-lg text-xs font-semibold transition-colors inline-block border border-slate-200/40">
                                    <i class="fa-solid fa-user-gear mr-1"></i> Atur Sektor
                                </a>
                            </td> --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2 block text-slate-200"></i>
                                Belum ada data karyawan terdaftar di database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL POPUP DETAIL KARYAWAN                               --}}
{{-- ========================================================= --}}
<div id="detailKaryawanModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    {{-- Backdrop --}}
    <div id="detailModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Konten Box --}}
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Detail Lengkap Karyawan</h3>
            <button type="button" id="closeDetailModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Tempat Loading Spinner --}}
        <div id="modalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Memuat data...</p>
        </div>

        {{-- Tempat Menampilkan Data Karyawan (Awalnya tersembunyi) --}}
        <div id="modalDataContent" class="hidden space-y-6">
            <div class="flex flex-col items-center justify-center text-center">
                {{-- Foto Karyawan di Modal --}}
                <div id="detail_photo_container" class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-2xl shadow-md overflow-hidden mb-3 border-2 border-white ring-4 ring-sky-50">
                    {{-- Diisi via JS --}}
                </div>
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
        const modal = document.getElementById("detailKaryawanModal");
        const backdrop = document.getElementById("detailModalBackdrop");
        const closeBtn = document.getElementById("closeDetailModalBtn");
        const closeBtn2 = document.getElementById("closeDetailModalBtn2");

        const loadingSection = document.getElementById("modalLoading");
        const contentSection = document.getElementById("modalDataContent");

        // Fungsi Membuka Modal
        function showModal() {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.classList.add("overflow-hidden");
        }

        // Fungsi Menutup Modal
        function hideModal() {
            modal.classList.remove("flex");
            modal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
        }

        // Event Listener untuk Tombol Tutup
        if (closeBtn) closeBtn.addEventListener("click", hideModal);
        if (closeBtn2) closeBtn2.addEventListener("click", hideModal);
        if (backdrop) backdrop.addEventListener("click", hideModal);

        // Menangkap Event Klik pada Nama Karyawan
        document.querySelectorAll(".btn-detail-karyawan").forEach(button => {
            button.addEventListener("click", function () {
                const karyawanId = this.getAttribute("data-id");

                // Tampilkan modal dan setel ke keadaan 'Loading' terlebih dahulu
                showModal();
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");

                // Melakukan AJAX Request ke route Laravel
                fetch(`/admin/karyawan/${karyawanId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error("Gagal mengambil data");
                        return response.json();
                    })
                    .then(data => {
                        // Sembunyikan loading, tampilkan konten
                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        // Isi Text Data Karyawan ke Modal
                        document.getElementById("detail_name").innerText = data.name;
                        document.getElementById("detail_nip").innerText = data.nip ? data.nip : '-';
                        document.getElementById("detail_email").innerText = data.email;
                        document.getElementById("detail_phone").innerText = data.phone_number ? data.phone_number : '-';
                        document.getElementById("detail_role").innerText = data.role_name ? data.role_name : 'Tidak Ada Role';
                        document.getElementById("detail_station").innerText = data.nama_stasiun ? `📍 ${data.nama_stasiun}` : '⚠️ Belum Diatur';

                        // Format tampilan untuk Jobdesk
                        let jobTitleText = 'Belum Memilih';
                        if(data.job_title == 'Operator' || data.job_title == '1') jobTitleText = 'Operator';
                        else if(data.job_title == 'Maintenance' || data.job_title == '2') jobTitleText = 'Maintenance';
                        else if(data.job_title == 'HSE' || data.job_title == '3') jobTitleText = 'Safety (HSE)';
                        else if(data.job_title == 'Dokumentasi' || data.job_title == '4') jobTitleText = 'Documenter';
                        document.getElementById("detail_job").innerText = jobTitleText;

                        // Mengatur Tampilan Foto Profil di Dalam Modal
                        const photoContainer = document.getElementById("detail_photo_container");
                        if (data.profile_photo) {
                            photoContainer.innerHTML = `<img src="/storage/${data.profile_photo}" class="w-full h-full object-cover">`;
                        } else {
                            // Jika tidak ada foto, tampilkan inisial 2 huruf pertama dari nama
                            const initials = data.name.substring(0, 2).toUpperCase();
                            photoContainer.innerHTML = initials;
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        loadingSection.classList.add("hidden");
                        alert("Terjadi kesalahan saat memuat data karyawan.");
                        hideModal();
                    });
            });
        });
    });
</script>
@endpush

@extends('layouts.app')
@section('title', 'Riwayat Cuti')
@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Riwayat Pengajuan Cuti Anda</h2>
                <p class="text-sm text-slate-500 mt-0.5">Daftar seluruh permohonan cuti yang pernah Anda ajukan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Jenis Cuti / Detail</th>
                        <th class="px-6 py-4">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4">Durasi</th>
                        <th class="px-6 py-4">Catatan Alasan</th>
                        <th class="px-6 py-4">Status Persetujuan</th>
                        <th class="px-6 py-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($pengajuanCuti as $cuti)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- JENIS CUTI & DETAIL SUB-CUTI --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $cuti->name_cuti }}</div>

                                @if(!empty($cuti->nama_sub_cuti))
                                    <div class="text-xs text-sky-600 font-medium mt-0.5 bg-sky-50 px-2 py-0.5 rounded w-fit border border-sky-100">
                                        {{ $cuti->nama_sub_cuti }}
                                    </div>
                                @endif
                            </td>

                            {{-- TANGGAL --}}
                            <td class="px-6 py-4 text-slate-500 text-sm">
                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            </td>

                            {{-- DURASI --}}
                            <td class="px-6 py-4 font-medium text-sm">{{ $cuti->total_hari }} Hari</td>

                            {{-- CATATAN TAMBAHAN / ALASAN --}}
                            <td class="px-6 py-4 text-slate-500 text-sm max-w-xs truncate" title="{{ blank($cuti->alasan_cuti) ? '-' : $cuti->alasan_cuti }}">
                                {{ blank($cuti->alasan_cuti) ? '-' : $cuti->alasan_cuti }}
                            </td>

                            {{-- STATUS MANAGEMENT --}}
                            <td class="px-6 py-4">
                                @if($cuti->status_supervisor == 'approved' && $cuti->status_manager == 'approved' && $cuti->status_akhir == 'approved')
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Disetujui</span>
                                        </span>
                                        {{-- MODIFIKASI: Menggunakan button yang memicu fungsi bukaPratinjauCetak ala halaman CAR --}}
                                        <button type="button" onclick="bukaPratinjauCetak('{{ route('cuti.cetak', $cuti->id) }}')" class="px-2 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded-md text-[11px] font-semibold inline-flex items-center space-x-1 transition-colors shadow-sm w-fit cursor-pointer">
                                            <span>Cetak</span>
                                        </button>
                                    </div>
                                @elseif($cuti->status_supervisor == 'rejected' || $cuti->status_manager == 'rejected')
                                    <div class="space-y-1.5">
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Ditolak</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button type="button" data-id="{{ $cuti->id }}" class="btn-detail-cuti px-3 py-1.5 bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 border border-slate-200/60 rounded-xl text-xs font-semibold transition-all inline-flex items-center space-x-1 shadow-sm">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Lihat</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Belum ada riwayat pengajuan cuti yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 1: DETAILED INFORMATION POPUP (AJAX DATA)          --}}
{{-- ========================================================= --}}
<div id="detailCutiModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div id="cutiModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 transform transition-all m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base flex items-center space-x-2">
                <i class="fa-solid fa-file-invoice text-sky-600"></i>
                <span>Detail Informasi Pengajuan Cuti</span>
            </h3>
            <button type="button" id="closeCutiModalBtn" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="cutiModalLoading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-sky-600 mb-2"></div>
            <p class="text-xs text-slate-400">Sedang menyinkronkan data...</p>
        </div>

        <div id="cutiModalContent" class="hidden space-y-4">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block font-medium uppercase tracking-wider">Kategori Utama</span>
                    <span id="txt_jenis_cuti" class="font-bold text-slate-800 mt-0.5 block">-</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium uppercase tracking-wider">Detail Keperluan</span>
                    <span id="txt_sub_cuti" class="font-bold text-slate-600 mt-0.5 block">-</span>
                </div>
            </div>

            <div class="border border-slate-100 rounded-xl divide-y divide-slate-50 text-sm">
                <div class="grid grid-cols-3 p-3">
                    <span class="text-slate-400 font-medium">Rentang Waktu</span>
                    <span id="txt_rentang_tanggal" class="col-span-2 text-slate-800 font-semibold">-</span>
                </div>
                <div class="grid grid-cols-3 p-3">
                    <span class="text-slate-400 font-medium">Total Akumulasi</span>
                    <span id="txt_total_hari" class="col-span-2 text-slate-800 font-bold">-</span>
                </div>
                <div class="grid grid-cols-3 p-3">
                    <span class="text-slate-400 font-medium">Alasan Pengiriman</span>
                    <span id="txt_alasan_cuti" class="col-span-2 text-slate-600 whitespace-pre-line leading-relaxed">-</span>
                </div>
                <div class="grid grid-cols-3 p-3 items-center">
                    <span class="text-slate-400 font-medium">Status Pengajuan</span>
                    <div id="wrapper_status" class="col-span-2"></div>
                </div>
            </div>

            <div id="wrapper_dokumen" class="border border-slate-100 rounded-xl p-3.5 space-y-2 bg-sky-50/10">
                <span class="text-xs font-bold text-slate-700 block uppercase tracking-wider">Berkas Lampiran Pendukung</span>
                <div id="dokumen_render_area"></div>
            </div>
        </div>

        <div class="flex items-center mt-6 justify-end border-t border-slate-100 pt-4">
            <button type="button" id="closeCutiModalBtn2" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-colors">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 2: PRATINJAU INTEGRASI DOKUMEN CETAK & LAMPIRAN      --}}
{{-- ========================================================= --}}
<div id="modalPreviewLampiran" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-3xl h-[85vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-sky-600 text-base"></i>
                <h3 id="judulModalLampiran" class="text-sm font-bold text-slate-800">Pratinjau Lampiran Dokumen</h3>
            </div>
            <button type="button" onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Tempat Render File (Iframe / Image) --}}
        <div id="containerKontenLampiran" class="flex-1 bg-slate-100 flex items-center justify-center p-2 sm:p-4 overflow-auto">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- INTEGRASI FUNGSI POPUP PRATINJAU DOKUMEN (SAMA SEPERTI KODE CAR) ---
    function bukaPratinjauLampiran(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Lampiran Dokumen';
        tampilkanModalPratinjau(urlFile);
    }

    function bukaPratinjauCetak(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Dokumen Cetak Dokumen Cuti';
        tampilkanModalPratinjau(urlFile, true);
    }

    function tampilkanModalPratinjau(urlFile, isPdfFormated = false) {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');

        container.innerHTML = '<div class="text-xs text-slate-400 font-medium animate-pulse">Memuat dokumen...</div>';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const ekstensi = urlFile.split('.').pop().toLowerCase();

        if (isPdfFormated || ekstensi === 'pdf') {
            container.innerHTML = `<iframe src="${urlFile}" class="w-full h-full rounded-xl border-0 shadow-inner" allow="autoplay"></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ekstensi)) {
            container.innerHTML = `<img src="${urlFile}" class="max-w-full max-h-full rounded-xl shadow-md object-contain" alt="Pratinjau Lampiran">`;
        } else {
            container.innerHTML = `
                <div class="text-center p-6 bg-white rounded-xl shadow-sm border border-slate-200 max-w-xs">
                    <i class="fa-solid fa-file-arrow-down text-amber-500 text-3xl mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700 mb-3">Format file tidak mendukung pratinjau langsung.</p>
                    <a href="${urlFile}" download class="inline-flex items-center gap-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-download"></i> Unduh File
                    </a>
                </div>
            `;
        }
    }

    function tutupPratinjauLampiran() {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');

        modal.classList.remove('flex');
        modal.classList.add('hidden');
        container.innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    // Menutup modal jika area luar diklik
    document.getElementById('modalPreviewLampiran').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupPratinjauLampiran();
        }
    });


    // --- SCRIPT MODAL DETAIL JENDELA CUTI (EXISTING AJAX) ---
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("detailCutiModal");
        const backdrop = document.getElementById("cutiModalBackdrop");
        const closeBtn = document.getElementById("closeCutiModalBtn");
        const closeBtn2 = document.getElementById("closeCutiModalBtn2");

        const loadingSection = document.getElementById("cutiModalLoading");
        const contentSection = document.getElementById("cutiModalContent");

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

        document.querySelectorAll(".btn-detail-cuti").forEach(btn => {
            btn.addEventListener("click", function () {
                const cutiId = this.getAttribute("data-id");

                openModal();
                loadingSection.classList.remove("hidden");
                contentSection.classList.add("hidden");

                fetch(`/cuti/riwayat/${cutiId}/detail`)
                    .then(response => {
                        if (!response.ok) throw new Error("Jaringan bermasalah");
                        return response.json();
                    })
                    .then(data => {
                        loadingSection.classList.add("hidden");
                        contentSection.classList.remove("hidden");

                        document.getElementById("txt_jenis_cuti").innerText = data.name_cuti;
                        document.getElementById("txt_sub_cuti").innerText = data.nama_sub_cuti ? data.nama_sub_cuti : '-';
                        document.getElementById("txt_rentang_tanggal").innerText = `${data.tanggal_mulai_formatted} s/d ${data.tanggal_selesai_formatted}`;
                        document.getElementById("txt_total_hari").innerText = `${data.total_hari} Hari`;
                        document.getElementById("txt_alasan_cuti").innerText = data.alasan_cuti ? data.alasan_cuti : '-';

                        const wrapperStatus = document.getElementById("wrapper_status");
                        if (data.status_manager === 'approved') {
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold inline-flex items-center border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Disetujui</span>`;
                        } else if (data.status_supervisor === 'rejected' || data.status_manager === 'rejected') {
                            let note = data.catatan_penolakan ? `<p class="text-xs text-rose-600 mt-1 italic font-medium">"${data.catatan_penolakan}"</p>` : '';
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold inline-flex items-center border border-rose-100"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>Ditolak</span> ${note}`;
                        } else {
                            wrapperStatus.innerHTML = `<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold inline-flex items-center border border-amber-100"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>Menunggu Review</span>`;
                        }

                        const docArea = document.getElementById("dokumen_render_area");
                        if (data.dokumen_pendukung) {
                            const fileUrl = `/storage/${data.dokumen_pendukung}`;

                            // Diubah agar tombol unduh/lihat di dalam detail modal AJAX juga memicu Modal Preview CAR baru ini
                            docArea.innerHTML = `
                                <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                                    <div class="flex items-center space-x-2.5 overflow-hidden">
                                        <div class="p-2 bg-sky-50 text-sky-600 rounded-lg text-lg"><i class="fa-solid fa-file"></i></div>
                                        <div class="flex flex-col truncate">
                                            <span class="text-xs font-semibold text-slate-700 truncate">${data.dokumen_pendukung.split('/').pop()}</span>
                                        </div>
                                    </div>
                                    <button type="button" onclick="bukaPratinjauLampiran('${fileUrl}')" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors shrink-0 cursor-pointer">
                                        Lihat Lampiran
                                    </button>
                                </div>`;
                        } else {
                            docArea.innerHTML = `<span class="text-xs italic text-slate-400 bg-white border border-dashed rounded-xl p-3 block text-center">Tidak melampirkan berkas dokumen apapun.</span>`;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Gagal memuat data detail pengajuan cuti.");
                        closeModal();
                    });
            });
        });
    });
</script>
@endpush

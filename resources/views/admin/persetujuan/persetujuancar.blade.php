@extends('layouts.app')
@section('title', 'Persetujuan CAR')
@section('content')
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 transition-colors">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Persetujuan Pengajuan CAR (Uang Material)</h2>
        <p class="text-xs text-slate-400 dark:text-slate-400">Daftar pengajuan masuk dari staf di lingkungan stasiun kerja Anda</p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2 text-rose-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-700">
                    <th class="p-4 text-center">Nama Pemohon</th>
                    <th class="p-4 text-center">Rincian Barang & Nota</th>
                    <th class="p-4 text-center">Total Biaya</th>
                    <th class="p-4 text-center">Alasan Keperluan</th>
                    <th class="p-4 text-center">Aksi Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                @forelse($daftarPengajuan as $car)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                    <td class="p-4 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100 align-center text-center">
                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ $car->user->name }}</div>
                        <div class="flex items-center justify-center gap-1.5 mt-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $car->status_tahap_1 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' : ($car->status_tahap_1 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400') }}">
                                Step 1: {{ ucfirst($car->status_tahap_1) }}
                            </span>
                            @if($car->status_tahap_2 !== 'not_required')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $car->status_tahap_2 === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' : ($car->status_tahap_2 === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400') }}">
                                    Step 2: {{ ucfirst($car->status_tahap_2) }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                    -
                                </span>
                            @endif
                        </div>
                    </td>

                    <td class="p-4 align-top min-w-[320px]">
                        <ul class="space-y-2">
                            @foreach($car->details as $item)
                                <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50 dark:bg-slate-700/40 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                    <div class="space-y-0.5">
                                        <span class="font-medium text-slate-900 dark:text-slate-100 block">{{ $item->nama_barang }}</span>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-1.5">
                                            <span class="font-semibold text-slate-700 dark:text-slate-300">Rp {{ number_format($item->estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-slate-400">x {{ $item->jumlah }} Qty</span>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <span class="text-slate-400">Total:</span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format(($item->total_harga ?? ($item->estimasi_harga * $item->jumlah)), 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @if($item->dokumen_nota_or_proposal)
                                        @if(Str::endsWith($item->dokumen_nota_or_proposal, '.pdf'))
                                            <a href="{{ asset('storage/' . $item->dokumen_nota_or_proposal) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 font-semibold bg-sky-50 dark:bg-sky-950/50 px-2.5 py-1 rounded-lg border border-sky-100 dark:border-sky-800">
                                                <i class="fa-solid fa-file-pdf"></i> Lihat PDF
                                            </a>
                                        @else
                                            <button type="button"
                                                    data-url="{{ asset('storage/' . $item->dokumen_nota_or_proposal) }}"
                                                    onclick="openImageModal(this.getAttribute('data-url'))"
                                                    class="inline-flex items-center gap-1 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 font-semibold bg-sky-50 dark:bg-sky-950/50 px-2.5 py-1 rounded-lg border border-sky-100 dark:border-sky-800 cursor-pointer">
                                                <i class="fa-solid fa-file-invoice"></i> Lampiran Gambar
                                            </button>
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>

                    <td class="p-4 font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap align-center text-center">
                        Rp {{ number_format($car->details->sum('total_harga'), 0, ',', '.') }}
                    </td>

                    <td class="p-4 align-center max-w-xs leading-relaxed text-center text-slate-700 dark:text-slate-300">
                        {{ $car->alasan_pembelian ?? '-' }}
                    </td>

                    <td class="p-4 text-center whitespace-nowrap align-center">
                        <div class="flex items-center justify-center space-x-2">
                            <form id="form-approve-car-{{ $car->id }}" action="{{ route('admin.persetujuan.car.process', $car->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="aksi" value="approved">
                                <button type="button"
                                        data-form="form-approve-car-{{ $car->id }}"
                                        onclick="konfirmasiApprove(this.getAttribute('data-form'), 'Setujui Pengajuan CAR?')"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-1.5 rounded-lg shadow-sm transition-colors">
                                    <i class="fa-solid fa-check mr-1"></i> Setujui
                                </button>
                            </form>

                            <button type="button"
                                    data-id="{{ $car->id }}"
                                    onclick="bukaModalTolak(this.getAttribute('data-id'))"
                                    class="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs px-3 py-1.5 rounded-lg shadow-sm transition-colors">
                                <i class="fa-solid fa-xmark mr-1"></i> Tolak
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400">Tidak ada antrean pengajuan CAR yang memerlukan persetujuan Anda saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TOLAK --}}
<div id="modalTolak" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 dark:border-slate-700">
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-2">Alasan Penolakan CAR</h3>
        <p class="text-xs text-slate-400 mb-4">Berikan catatan alasan mengapa pengajuan anggaran material ini ditolak.</p>

        <form id="formTolak" action="" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="aksi" value="rejected">
            <div>
                <textarea name="catatan_penolakan" required rows="3" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:border-sky-500" placeholder="Tulis alasan di sini..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="tutupModalTolak()" class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-semibold px-4 py-2 rounded-xl cursor-pointer">Batal</button>
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2 rounded-xl cursor-pointer">Kirim & Tolak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL PREVIEW GAMBAR --}}
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-70 p-4 transition-opacity duration-300">
    <div class="relative max-w-3xl w-full bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-2xl max-h-[90vh] flex flex-col border border-slate-100 dark:border-slate-700">
        <div class="flex justify-between items-center px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Preview Lampiran</span>
            <button onclick="closeImageModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold p-1 cursor-pointer">
                &times;
            </button>
        </div>
        <div class="p-4 flex justify-center items-center overflow-auto bg-slate-900">
            <img id="modalImage" src="" alt="Preview Lampiran" class="max-h-[70vh] object-contain rounded">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Masukkan session success ke variabel JS aman --}}
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
                popup: 'rounded-2xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold'
            }
        });
    });
</script>
@endif

<script>
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
                popup: 'rounded-2xl',
                confirmButton: 'px-4 py-2 rounded-xl font-semibold',
                cancelButton: 'px-4 py-2 rounded-xl font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    function bukaModalTolak(id) {
        const modal = document.getElementById('modalTolak');
        const form = document.getElementById('formTolak');
        form.action = `/admin/persetujuan/car/proses/${id}`;
        modal.classList.remove('hidden');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolak');
        modal.classList.add('hidden');
    }

    function openImageModal(imageUrl) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = imageUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            closeImageModal();
        }
    }
</script>
@endpush

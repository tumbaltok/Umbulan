@extends('layouts.app')
@section('title', 'Persetujuan CUTI')
@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4">
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2 text-rose-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Daftar Pengajuan Cuti Karyawan</h2>
            <p class="text-sm text-slate-500 mt-0.5">Halaman khusus Atasan untuk meninjau dan memproses permohonan cuti staf</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4 text-center">Karyawan</th>
                        <th class="px-6 py-4 text-center">Jenis Cuti</th>
                        <th class="px-6 py-4 text-center">Tanggal Pelaksanaan</th>
                        <th class="px-6 py-4 text-center">Total Hari</th>
                        <th class="px-6 py-4 text-center">Status SPV</th>
                        <th class="px-6 py-4 text-center">Status Manager</th>
                        <th class="px-6 py-4 text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($daftarPengajuan as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 text-center">{{ $item->user_name }}</td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800 text-center">{{ $item->nama_sub_cuti }}</div>
                                @if(!empty($item->dokumen_pendukung))
                                    <div class="mt-1">
                                        <button type="button"
                                                data-url="{{ asset('storage/' . $item->dokumen_pendukung) }}"
                                                onclick="bukaPratinjauLampiran(this.getAttribute('data-url'))"
                                                class="inline-flex items-center gap-1 text-xs text-sky-600 hover:text-sky-700 font-semibold bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-100 w-fit cursor-pointer self-start sm:self-center shrink-0">
                                            <i class="fa-solid fa-file-invoice"></i> Berkas Cuti
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-normal italic">Tidak ada berkas</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('D MMMM Y') }} -
                                {{ \Carbon\Carbon::parse($item->tanggal_selesai)->isoFormat('D MMMM Y') }}
                            </td>

                            <td class="px-6 py-4 font-mono font-bold text-center">{{ $item->total_hari }} Hari</td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $item->status_tahap_1 === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($item->status_tahap_1 === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ $item->status_tahap_1 === 'pending' ? 'Pending' : ucfirst($item->status_tahap_1) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $item->status_tahap_2 === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($item->status_tahap_2 === 'rejected' ? 'bg-rose-50 text-rose-700' : ($item->status_tahap_2 === 'not_required' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-700')) }}">
                                    {{ $item->status_tahap_2 === 'pending' ? 'Pending' : ucfirst($item->status_tahap_2) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    {{-- FORM KHUSUS APPROVE --}}
                                    <form id="form-approve-cuti-{{ $item->id }}" action="{{ route('admin.persetujuan.cuti.proses', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="tindakan" value="approved">
                                        <button type="button"
                                                data-form="form-approve-cuti-{{ $item->id }}"
                                                onclick="konfirmasiApprove(this.getAttribute('data-form'), 'Setujui Pengajuan Cuti?')"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition-colors">
                                            Approve
                                        </button>
                                    </form>

                                    {{-- BUTTON KHUSUS MEMICU MODAL REJECT --}}
                                    <button type="button"
                                            data-id="{{ $item->id }}"
                                            onclick="bukaModalTolak(this.getAttribute('data-id'))"
                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-bold transition-colors">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-check text-3xl mb-2 block text-slate-200"></i>
                                Tidak ada pengajuan cuti masuk yang perlu ditinjau.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL POPUP INPUT ALASAN PENOLAKAN CUTI --}}
<div id="modalTolakCuti" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100">
        <h3 class="text-base font-bold text-slate-800 mb-2">Alasan Penolakan Cuti</h3>
        <p class="text-xs text-slate-400 mb-4">Berikan catatan alasan mengapa permohonan pengajuan cuti karyawan ini ditolak.</p>

        <form id="formTolakCuti" action="" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="tindakan" value="rejected">
            <div>
                <textarea name="catatan_penolakan" required rows="3" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-sky-500" placeholder="Tulis alasan penolakan di sini..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="tutupModalTolak()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold px-4 py-2 rounded-xl">Batal</button>
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2 rounded-xl">Kirim & Tolak</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL POPUP PRATINJAU LAMPIRAN BERKAS CUTI --}}
<div id="modalPreviewLampiran" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-3xl h-[85vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-sky-600 text-base"></i>
                <h3 id="judulModalLampiran" class="text-sm font-bold text-slate-800">Pratinjau Lampiran Dokumen</h3>
            </div>
            <button type="button" onclick="tutupPratinjauLampiran()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div id="containerKontenLampiran" class="flex-1 bg-slate-100 flex items-center justify-center p-2 sm:p-4 overflow-auto">
        </div>
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
        const modal = document.getElementById('modalTolakCuti');
        const form = document.getElementById('formTolakCuti');
        form.action = `/admin/persetujuan/cuti/proses/${id}`;
        modal.classList.remove('hidden');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolakCuti');
        modal.classList.add('hidden');
    }

    function bukaPratinjauLampiran(urlFile) {
        document.getElementById('judulModalLampiran').innerText = 'Pratinjau Berkas Lampiran Cuti';
        tampilkanModal(urlFile);
    }

    function tampilkanModal(urlFile) {
        const modal = document.getElementById('modalPreviewLampiran');
        const container = document.getElementById('containerKontenLampiran');

        container.innerHTML = '<div class="text-xs text-slate-400 font-medium animate-pulse">Memuat dokumen...</div>';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const linkClean = urlFile.split('?')[0];
        const ekstensi = linkClean.split('.').pop().toLowerCase();

        if (ekstensi === 'pdf') {
            container.innerHTML = `<iframe src="${urlFile}" class="w-full h-full rounded-xl border-0 shadow-inner" allow="autoplay"></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ekstensi)) {
            container.innerHTML = `<img src="${urlFile}" class="max-w-full max-h-full rounded-xl shadow-md object-contain" alt="Pratinjau Berkas">`;
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

    document.getElementById('modalPreviewLampiran').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupPratinjauLampiran();
        }
    });
</script>
@endpush

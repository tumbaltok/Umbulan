@extends('layouts.app')
@section('title', 'Manajemen Role Jabatan')

@push('styles')
<!-- SweetAlert2 CDN CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4 space-y-8">

    {{-- TABEL 1: DAFTAR ROLE & DIVISI JABATAN --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Role & Divisi Jabatan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola tingkat hak akses dan hirarki wewenang jabatan.</p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative min-w-[150px]">
                    <select id="filterSektor" onchange="filterTabelSektor()" class="w-full pl-3 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-sky-500 appearance-none cursor-pointer shadow-xs">
                        <option value="ALL">Semua Sektor</option>
                        <option value="MANAJEMEN">Manajemen</option>
                        <option value="OPERASIONAL">Operasional</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                    </div>
                </div>

                @if(Auth::user()->role && Auth::user()->role->level == 1)
                <button type="button" onclick="bukaModalTambahRole()" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-sm shrink-0">
                    <i class="fa-solid fa-plus"></i> Tambah Role Baru
                </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="tabelRole">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-6 py-4 text-center">Divisi</th>
                        <th class="px-6 py-4 text-center">Role / Jabatan</th>
                        <th class="px-6 py-4 text-center">Hak Akses</th>
                        <th class="px-6 py-4 text-center">Wewenang / Deskripsi</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap min-w-[130px]">Total Staf</th>
                        @if(Auth::user()->role && Auth::user()->role->level == 1)
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                    @forelse($daftarRole as $role)
                        @php 
                            $divisiRaw = trim($role->divisi ?? 'Operasional');
                            $divisiFormatted = strtoupper($divisiRaw); 
                        @endphp
                        <tr class="role-row hover:bg-slate-50/80 transition-colors" data-divisi="{{ $divisiFormatted }}">
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($divisiFormatted === 'MANAJEMEN')
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-xs inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        {{ $divisiRaw }}
                                    </span>
                                @elseif($divisiFormatted === 'OPERASIONAL')
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-xs inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $divisiRaw }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200 shadow-xs inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        {{ $divisiRaw }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center font-bold text-slate-800 whitespace-nowrap">{{ $role->role_name }}</td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(($role->level ?? 3) == 1)
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved text-[10px]"></i> Full Akses
                                    </span>
                                @elseif(($role->level ?? 3) == 2)
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i> Only Read
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-user text-[10px]"></i> User
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center text-xs leading-relaxed max-w-xs text-slate-500">
                                {{ $role->description ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100/80">
                                    <i class="fa-solid fa-users text-[10px] mr-1.5 text-sky-500"></i>
                                    {{ $role->users_count }} Orang
                                </span>
                            </td>

                            @if(Auth::user()->role && Auth::user()->role->level == 1)
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-role='@json($role)'
                                            onclick="bukaModalEditRole(this)"
                                            class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs transition-colors" 
                                            title="Edit Role">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form id="form-delete-role-{{ $role->id }}" action="{{ route('admin.role.destroy', $role->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="konfirmasiHapus('form-delete-role-{{ $role->id }}', 'Role Jabatan: {{ $role->role_name }}')"
                                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs transition-colors" 
                                                title="Hapus Role">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada data role yang tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL 2: DAFTAR JOBDESK --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-indigo-50/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-800">Daftar Jobdesk</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200/80">
                        {{ isset($daftarJobdesk) ? count($daftarJobdesk) : 0 }} Jobdesk
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Daftar bidang tugas yang tersedia untuk dipilih karyawan saat registrasi.</p>
            </div>
            @if(Auth::user()->role && Auth::user()->role->level == 1)
            <button type="button" onclick="bukaModalTambahJobdesk()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-sm shrink-0">
                <i class="fa-solid fa-plus"></i> Tambah Jobdesk Baru
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-100 select-none">
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4">Nama Jobdesk / Bidang Tugas</th>
                        <th class="px-6 py-4">Deskripsi Rincian Tugas</th>
                        @if(Auth::user()->role && Auth::user()->role->level == 1)
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                    @if(isset($daftarJobdesk) && count($daftarJobdesk) > 0)
                        @php
                            $colorPalettes = [
                                ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100', 'icon' => 'text-indigo-500'],
                                ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'text-emerald-500'],
                                ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-100', 'icon' => 'text-sky-500'],
                                ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'text-amber-500'],
                                ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-100', 'icon' => 'text-purple-500'],
                                ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'text-rose-500'],
                            ];
                        @endphp
                        @foreach($daftarJobdesk as $index => $jd)
                            @php
                                $color = $colorPalettes[$index % count($colorPalettes)];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-center font-bold text-slate-400 text-xs">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $color['bg'] }} {{ $color['text'] }} border {{ $color['border'] }} inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-briefcase {{ $color['icon'] }}"></i>
                                        {{ $jd->job_title }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 leading-relaxed">
                                    {{ $jd->description ?? 'Tidak ada rincian keterangan tugas.' }}
                                </td>
                                @if(Auth::user()->role && Auth::user()->role->level == 1)
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button type="button" 
                                                data-jobdesk='@json($jd)'
                                                onclick="bukaModalEditJobdesk(this)"
                                                class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs transition-colors" 
                                                title="Edit Jobdesk">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form id="form-delete-jobdesk-{{ $jd->id }}" action="{{ route('admin.jobdesk.destroy', $jd->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="konfirmasiHapus('form-delete-jobdesk-{{ $jd->id }}', 'Jobdesk: {{ $jd->job_title }}')"
                                                    class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-xs transition-colors" 
                                                    title="Hapus Jobdesk">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-list-check text-3xl mb-2 block text-slate-200"></i>
                                Belum ada kategori jobdesk yang terdaftar di database.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL FORM TAMBAH / EDIT ROLE --}}
<div id="modalFormRole" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalFormRole()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-base" id="judulModalFormRole">Tambah Role Baru</h3>
            <button type="button" onclick="tutupModalFormRole()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formRoleAction" action="{{ route('admin.role.store') }}" method="POST" class="space-y-4 overflow-y-auto pr-1 flex-1">
            @csrf
            <input type="hidden" name="_method" id="methodFormRole" value="POST">

            <div id="roleRowsContainer" class="space-y-4"></div>

            <div id="btnTambahRoleContainer" class="pt-2">
                <button type="button" onclick="tambahBarisRole()" class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-sky-600 border border-dashed border-sky-300 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah Baris Role Lain
                </button>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 shrink-0">
                <button type="button" onclick="tutupModalFormRole()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-xs">Simpan Data Role</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL FORM TAMBAH JOBDESK BARU --}}
<div id="modalFormJobdesk" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalFormJobdesk()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-base"><i class="fa-solid fa-list-check text-indigo-600 mr-2"></i>Tambah Kategori Jobdesk</h3>
            <button type="button" onclick="tutupModalFormJobdesk()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.jobdesk.store') }}" method="POST" class="space-y-4 overflow-y-auto pr-1 flex-1">
            @csrf
            
            <div id="jobdeskRowsContainer" class="space-y-4"></div>

            <div class="pt-2">
                <button type="button" onclick="tambahBarisJobdesk()" class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-indigo-600 border border-dashed border-indigo-300 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah Baris Jobdesk Lain
                </button>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 shrink-0">
                <button type="button" onclick="tutupModalFormJobdesk()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs">Simpan Semua Jobdesk</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT JOBDESK --}}
<div id="modalEditJobdesk" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalEditJobdesk()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-base"><i class="fa-solid fa-pen-to-square text-amber-600 mr-2"></i>Edit Kategori Jobdesk</h3>
            <button type="button" onclick="tutupModalEditJobdesk()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditJobdeskAction" method="POST" class="space-y-4 flex-1">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jobdesk / Bidang Tugas</label>
                <input type="text" name="job_title" id="edit_job_title" required class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Deskripsi Tugas</label>
                <textarea name="description" id="edit_job_description" rows="3" class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 shrink-0">
                <button type="button" onclick="tutupModalEditJobdesk()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs">Perbarui Jobdesk</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let roleIndex = 0;
    let jobdeskIndex = 0;

    const sessionSuccess   = JSON.parse(`{!! json_encode(session('success')) !!}`);
    const sessionError     = JSON.parse(`{!! json_encode(session('error')) !!}`);
    const validationErrors = JSON.parse(`{!! json_encode($errors->all()) !!}`);

    // SweetAlert2 Toast Handler
    document.addEventListener('DOMContentLoaded', function () {
        if (sessionSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: sessionSuccess,
                confirmColor: '#0284c7',
                timer: 3000,
                timerProgressBar: true
            });
        }

        if (sessionError) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                text: sessionError,
                confirmColor: '#e11d48'
            });
        }

        if (validationErrors && validationErrors.length > 0) {
            let errorListHtml = validationErrors.map(err => `• ${err}`).join('<br>');

            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                html: `<div class="text-left text-xs font-medium text-slate-600 leading-relaxed">${errorListHtml}</div>`,
                confirmColor: '#f59e0b'
            });
        }
    });

    // POPUP KONFIRMASI HAPUS
    function konfirmasiHapus(formId, itemLabel) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: `Data "${itemLabel}" yang dihapus tidak dapat dikembalikan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl text-xs font-bold px-4 py-2',
                cancelButton: 'rounded-xl text-xs font-bold px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    function filterTabelSektor() {
        const selectedSektor = document.getElementById('filterSektor').value.toUpperCase().trim();
        const rows = document.querySelectorAll('.role-row');

        rows.forEach(row => {
            const rowDivisi = (row.getAttribute('data-divisi') || '').toUpperCase().trim();
            if (selectedSektor === 'ALL' || rowDivisi === selectedSektor) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function tambahBarisRole(roleData = null) {
        const container = document.getElementById('roleRowsContainer');
        const showDelete = container.children.length > 0;
        
        const divisiVal = roleData ? (roleData.divisi || 'Operasional') : 'Operasional';
        const roleNameVal = roleData ? (roleData.role_name || '') : '';
        const levelVal = roleData ? (roleData.level || 2) : 2;
        const descVal = roleData ? (roleData.description || '') : '';

        const rowHtml = `
            <div class="role-item-row bg-slate-50/70 border border-slate-200 rounded-2xl p-4 relative space-y-3">
                ${showDelete ? `
                    <button type="button" onclick="hapusBaris(this)" class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 p-1 rounded-lg transition-colors" title="Hapus Baris Ini">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                ` : ''}

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Sektor Kerja</label>
                        <select name="roles[${roleIndex}][divisi]" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                            <option value="Manajemen" ${divisiVal === 'Manajemen' ? 'selected' : ''}>Manajemen</option>
                            <option value="Operasional" ${divisiVal === 'Operasional' ? 'selected' : ''}>Operasional</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Role / Jabatan</label>
                        <input type="text" name="roles[${roleIndex}][role_name]" value="${roleNameVal}" required placeholder="Contoh: Supervisor Operasional" class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-sky-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Hak Akses</label>
                    <select name="roles[${roleIndex}][level]" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                        <option value="1" ${levelVal == 1 ? 'selected' : ''}>Level 1: Full Akses (Dapat Mengelola & Mengedit Data Admin)</option>
                        <option value="2" ${levelVal == 2 ? 'selected' : ''}>Level 2: Only Read (Monitoring Fitur Admin, Penggunaan App Normal)</option>
                        <option value="3" ${levelVal == 3 ? 'selected' : ''}>Level 3: User (Staff Khusus Antarmuka Karyawan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Wewenang</label>
                    <textarea name="roles[${roleIndex}][description]" rows="2" placeholder="Penjelasan wewenang role..." class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-sky-500">${descVal}</textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        roleIndex++;
    }

    function tambahBarisJobdesk() {
        const container = document.getElementById('jobdeskRowsContainer');
        const showDelete = container.children.length > 0;

        const rowHtml = `
            <div class="jobdesk-item-row bg-indigo-50/30 border border-indigo-100 rounded-2xl p-4 relative space-y-3">
                ${showDelete ? `
                    <button type="button" onclick="hapusBaris(this)" class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 p-1 rounded-lg transition-colors" title="Hapus Baris Ini">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                ` : ''}

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jobdesk / Bidang Tugas Baru</label>
                    <input type="text" name="jobdesks[${jobdeskIndex}][job_title]" required placeholder="Contoh: Operator, Maintenance, HSE" class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Deskripsi Tugas</label>
                    <textarea name="jobdesks[${jobdeskIndex}][description]" rows="2" placeholder="Penjelasan rincian tugas jobdesk..." class="w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-xs focus:outline-none focus:border-indigo-500"></textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        jobdeskIndex++;
    }

    function hapusBaris(btn) {
        const parentRow = btn.closest('.role-item-row, .jobdesk-item-row');
        if (parentRow) parentRow.remove();
    }

    function bukaModalTambahRole() {
        document.getElementById('judulModalFormRole').innerText = 'Tambah Role Baru';
        document.getElementById('formRoleAction').action = "{{ route('admin.role.store') }}";
        document.getElementById('methodFormRole').value = 'POST';

        document.getElementById('roleRowsContainer').innerHTML = '';
        document.getElementById('btnTambahRoleContainer').classList.remove('hidden');
        roleIndex = 0;
        
        tambahBarisRole();

        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function bukaModalEditRole(button) {
        const role = JSON.parse(button.getAttribute('data-role'));

        document.getElementById('judulModalFormRole').innerText = 'Edit Role Jabatan';
        document.getElementById('formRoleAction').action = `/admin/role/${role.id}`;
        document.getElementById('methodFormRole').value = 'PUT';

        document.getElementById('roleRowsContainer').innerHTML = '';
        document.getElementById('btnTambahRoleContainer').classList.add('hidden');
        roleIndex = 0;

        tambahBarisRole(role);

        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalFormRole() {
        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function bukaModalTambahJobdesk() {
        document.getElementById('jobdeskRowsContainer').innerHTML = '';
        jobdeskIndex = 0;

        tambahBarisJobdesk();

        const modal = document.getElementById('modalFormJobdesk');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalFormJobdesk() {
        const modal = document.getElementById('modalFormJobdesk');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function bukaModalEditJobdesk(button) {
        const jobdesk = JSON.parse(button.getAttribute('data-jobdesk'));

        document.getElementById('formEditJobdeskAction').action = `{{ url('admin/jobdesk') }}/${jobdesk.id}`;
        document.getElementById('edit_job_title').value = jobdesk.job_title || '';
        document.getElementById('edit_job_description').value = jobdesk.description || '';

        const modal = document.getElementById('modalEditJobdesk');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalEditJobdesk() {
        const modal = document.getElementById('modalEditJobdesk');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
@endpush
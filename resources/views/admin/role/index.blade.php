@extends('layouts.app')
@section('title', 'Manajemen Role Jabatan')

@section('content')
<div class="max-w-5xl mx-auto mt-8 px-4">

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center">
            <i class="fa-solid fa-circle-xmark mr-2 text-rose-600"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Role & Divisi Jabatan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola tingkat hak akses dan jobdesk berdasarkan divisi kerja.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative min-w-[170px]">
                    <select id="filterSektor" onchange="filterTabelSektor()" class="w-full pl-3 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-sky-500 appearance-none cursor-pointer shadow-sm">
                        <option value="ALL">Semua Sektor</option>
                        <option value="MANAJEMEN">Manajemen</option>
                        <option value="OPERASIONAL">Operasional</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                    </div>
                </div>

                @if(Auth::user()->role->level == 1)
                <button type="button" onclick="bukaModalTambahRole()" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors flex items-center gap-2 shadow-sm shrink-0">
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
                        <th class="px-6 py-4 text-center">Jobdesk / Keterangan</th>
                        <th class="px-6 py-4 text-center">Total Staf</th>
                        @if(Auth::user()->role->level == 1)
                        <th class="px-6 py-4 text-center w-32">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-sm">
                    @forelse($daftarRole as $role)
                        @php $divisiFormatted = strtoupper(trim($role->divisi ?? 'OPERASIONAL')); @endphp
                        <tr class="role-row hover:bg-slate-50/80 transition-colors" data-divisi="{{ $divisiFormatted }}">
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $role->divisi ?? 'Operasional' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center font-bold text-slate-800">{{ $role->role_name }}</td>

                            <td class="px-6 py-4 text-center">
                                @if(($role->level ?? 3) == 1)
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-100 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved text-[10px]"></i> Full Akses
                                    </span>
                                @elseif(($role->level ?? 3) == 2)
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i> Only Read (Monitoring)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-user text-[10px]"></i> User Biasa
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center text-slate-500 text-xs">{{ $role->description ?? '-' }}</td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $role->users_count }} Orang
                                </span>
                            </td>

                            @if(Auth::user()->role->level == 1)
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            data-role='@json($role)'
                                            onclick="bukaModalEditRole(this)"
                                            class="p-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs transition-colors" title="Edit Role">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
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
</div>

{{-- MODAL FORM TAMBAH / EDIT ROLE --}}
<div id="modalFormRole" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalFormRole()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-base" id="judulModalForm">Tambah Role Baru</h3>
            <button type="button" onclick="tutupModalFormRole()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formRoleAction" action="{{ route('admin.role.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="methodFormRole" value="POST">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi / Sektor Kerja</label>
                <select name="divisi" id="input_divisi" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                    <option value="Manajemen">Manajemen</option>
                    <option value="Operasional">Operasional</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Role / Jabatan</label>
                <input type="text" name="role_name" id="input_role_name" required placeholder="Contoh: Supervisor Operasional" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Hak Akses</label>
                <select name="level" id="input_level" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                    <option value="1">Level 1: Full Akses (Dapat Mengelola & Mengedit Data Admin)</option>
                    <option value="2">Level 2: Only Read (Monitoring Fitur Admin, Penggunaan App Normal)</option>
                    <option value="3">Level 3: User Biasa (Staff Khusus Antarmuka Karyawan)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jobdesk / Deskripsi</label>
                <textarea name="description" id="input_description" rows="3" placeholder="Penjelasan wewenang role..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-sky-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalFormRole()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm">Simpan Role</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    function bukaModalTambahRole() {
        document.getElementById('judulModalForm').innerText = 'Tambah Role Baru';
        document.getElementById('formRoleAction').action = "{{ route('admin.role.store') }}";
        document.getElementById('methodFormRole').value = 'POST';

        document.getElementById('input_role_name').value = '';
        document.getElementById('input_level').value = '2';
        document.getElementById('input_description').value = '';

        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function bukaModalEditRole(button) {
        const role = JSON.parse(button.getAttribute('data-role'));

        document.getElementById('judulModalForm').innerText = 'Edit Role Jabatan';
        document.getElementById('formRoleAction').action = `/admin/role/${role.id}`;
        document.getElementById('methodFormRole').value = 'PUT';

        document.getElementById('input_role_name').value = role.role_name;
        document.getElementById('input_divisi').value = role.divisi || 'Operasional';
        document.getElementById('input_level').value = role.level || 2;
        document.getElementById('input_description').value = role.description || '';

        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalFormRole() {
        const modal = document.getElementById('modalFormRole');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
@endpush
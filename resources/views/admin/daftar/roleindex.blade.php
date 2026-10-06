@extends('layouts.app')
@section('title', 'Manajemen Role & Skema Hirarki Jabatan')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .mermaid-container {
        background: #f8fafc;
        border-radius: 1rem;
        padding: 1.5rem;
        width: 100%;
        overflow-x: auto;
        min-height: 220px;
    }
    #mermaidDiagram svg {
        max-width: 100% !important;
        height: auto !important;
    }

    /* ==========================================================================
       DARK MODE STYLING KHUSUS DIAGRAM PETA STRUKTUR ORGANISASI
       (Hanya aktif pada Dark Mode - Mode Terang / Light Mode 100% Tidak Berubah)
       ========================================================================== */
    html.dark .mermaid-container {
        background: radial-gradient(ellipse at top, #111e38 0%, #080d1a 100%) !important;
        border: 1px solid rgba(56, 189, 248, 0.22) !important;
        box-shadow: inset 0 2px 25px rgba(0, 0, 0, 0.55), 0 10px 25px -5px rgba(2, 6, 23, 0.7) !important;
    }

    /* Garis Penghubung / Relasi Alur Hirarki (Edges) */
    html.dark .mermaid-container svg .edgePath path,
    html.dark .mermaid-container svg .edgePath .path,
    html.dark .mermaid-container svg .edgePaths path,
    html.dark .mermaid-container svg path.flowchart-link,
    html.dark .mermaid-container svg g.edgePath path,
    html.dark .mermaid-container svg path.edge-thickness-normal {
        stroke: #38bdf8 !important; /* Sky-400 cerah, kontras tinggi & menyala di latar gelap */
        stroke-width: 2.2px !important;
        stroke-linecap: round !important;
        stroke-linejoin: round !important;
        opacity: 0.95 !important;
        filter: drop-shadow(0 0 5px rgba(56, 189, 248, 0.45)) !important;
    }

    /* Ujung Mata Panah (Markers / Arrowheads) */
    html.dark .mermaid-container svg marker path,
    html.dark .mermaid-container svg defs marker path,
    html.dark .mermaid-container svg marker[id*="flowchart"] path,
    html.dark .mermaid-container svg marker[id*="point"] path,
    html.dark .mermaid-container svg .arrowheadPath,
    html.dark .mermaid-container svg marker * {
        fill: #38bdf8 !important;
        stroke: #38bdf8 !important;
        stroke-width: 1px !important;
        opacity: 1 !important;
    }

    /* Kotak Jabatan / Role Card Standar */
    html.dark .mermaid-container svg .node rect,
    html.dark .mermaid-container svg .node polygon,
    html.dark .mermaid-container svg .node circle,
    html.dark .mermaid-container svg .node .label-container,
    html.dark .mermaid-container svg g.node > rect {
        fill: #1e293b !important; /* Slate 800 dark metallic card */
        stroke: #38bdf8 !important; /* Border Sky-400 tegas */
        stroke-width: 1.5px !important;
        rx: 10px !important;
        ry: 10px !important;
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.6)) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Efek Interaktif / Hover pada Kotak Jabatan */
    html.dark .mermaid-container svg .node:hover rect,
    html.dark .mermaid-container svg .node:hover polygon,
    html.dark .mermaid-container svg .node:hover .label-container,
    html.dark .mermaid-container svg g.node:hover > rect {
        fill: #0c4a6e !important; /* Sky 900 */
        stroke: #67e8f9 !important; /* Cyan 300 Glow */
        stroke-width: 2.2px !important;
        filter: drop-shadow(0 0 16px rgba(56, 189, 248, 0.7)) !important;
        cursor: pointer !important;
    }

    /* Kotak Puncak Pimpinan (B O D / Root Company) */
    html.dark .mermaid-container svg .node[id*="COMPANY"] rect,
    html.dark .mermaid-container svg .node#flowchart-COMPANY rect,
    html.dark .mermaid-container svg g[id*="COMPANY"] > rect,
    html.dark .mermaid-container svg g[id*="COMPANY"] .label-container {
        fill: #0369a1 !important; /* Sky 700 Sapphire Accent */
        stroke: #7dd3fc !important; /* Sky 300 Glow */
        stroke-width: 2.5px !important;
        filter: drop-shadow(0 0 16px rgba(14, 165, 233, 0.6)) !important;
    }

    /* Tipografi & Teks Jabatan (Putih Bersih, Tebal & Sangat Jelas) */
    html.dark .mermaid-container svg .node .label,
    html.dark .mermaid-container svg .node .label text,
    html.dark .mermaid-container svg .node text,
    html.dark .mermaid-container svg .node .nodeLabel,
    html.dark .mermaid-container svg .node span,
    html.dark .mermaid-container svg .node div,
    html.dark .mermaid-container svg .node b,
    html.dark .mermaid-container svg .node strong {
        color: #ffffff !important;
        fill: #ffffff !important;
        font-family: inherit !important;
        font-weight: 700 !important;
        letter-spacing: 0.03em !important;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8) !important;
    }

    /* Subteks / Divider Tambahan jika ada */
    html.dark .mermaid-container svg .node i,
    html.dark .mermaid-container svg .node span i,
    html.dark .mermaid-container svg .node em {
        color: #94a3b8 !important;
        fill: #94a3b8 !important;
        font-weight: 500 !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4 space-y-6">

    {{-- NAVIGASI TAB UTAMA --}}
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl shadow-xs transition-colors">
        <div class="flex space-x-2">
            <button type="button" onclick="switchRoleTab('tab-hierarchy')" id="btn-tab-hierarchy" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-sky-600 text-white shadow-xs cursor-pointer">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Skema Pohon
            </button>
            <button type="button" onclick="switchRoleTab('tab-roles')" id="btn-tab-roles" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <i class="fa-solid fa-user-shield mr-1.5"></i> Daftar Role
            </button>
        </div>
    </div>

    {{-- TAB 1: SKEMA POHON HIRARKI --}}
    <div id="tab-hierarchy" class="tab-content space-y-6">
        {{-- DIAGRAM VISUAL POHON ORGANISASI JABATAN --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 space-y-4 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-sitemap text-indigo-600 dark:text-indigo-400"></i> Visualisasi Skema Struktur Organisasi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Diagram hirarki struktur komando yang dirender otomatis dari relasi atasan langsung di database.</p>
                </div>
                <button type="button" onclick="renderMermaidDiagram()" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh Diagram
                </button>
            </div>

            <div class="mermaid-container flex justify-center py-4 bg-slate-50 dark:bg-slate-900 rounded-2xl">
                <div id="mermaidDiagram" class="w-full flex justify-center min-h-[180px]"></div>
            </div>
        </div>
    </div>

    {{-- TAB 2: DAFTAR ROLE JABATAN --}}
    <div id="tab-roles" class="tab-content hidden space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden transition-colors">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Daftar Role Jabatan</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            <i class="fa-solid fa-user-shield text-[10px] mr-1.5 text-sky-600 dark:text-sky-400"></i>
                            {{ isset($daftarRole) ? count($daftarRole) : 0 }} Role
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola nama jabatan, hierarki struktur atasan langsung, dan alur approver modul.</p>
                </div>

                @if(Auth::user()?->isLevel1())
                <button type="button" onclick="bukaModalTambahRole()" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-sm shrink-0 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Tambah Role Baru
                </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tabelRole">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 select-none">
                            <th class="px-4 py-3.5">Role / Jabatan</th>
                            <th class="px-4 py-3.5">Atasan Langsung</th>
                            <th class="px-4 py-3.5">Alur & Approver Modul</th>
                            <th class="px-4 py-3.5">Deskripsi Wewenang</th>
                            <th class="px-4 py-3.5 text-center">Total Staf</th>
                            @if(Auth::user()?->isLevel1())
                            <th class="px-4 py-3.5 text-center w-24">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                        @forelse($daftarRole as $role)
                            @php
                                $rules = $role->approval_rules ?? [];
                                
                                // Cuti
                                $cutiRules = $rules['cuti'] ?? [];
                                $cutiLevels = $cutiRules['levels'] ?? ($rules['approval_levels'] ?? 1);
                                $cutiLvl1RoleId = $cutiRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
                                $cutiLvl2RoleId = $cutiRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);
                                $cutiLvl1Role = $daftarRole->firstWhere('id', $cutiLvl1RoleId);
                                $cutiLvl2Role = $daftarRole->firstWhere('id', $cutiLvl2RoleId);

                                // MPR
                                $mprRules = $rules['mpr'] ?? [];
                                $mprLevels = $mprRules['levels'] ?? 1;
                                $mprLvl1RoleId = $mprRules['approver_1_role_id'] ?? null;
                                $mprLvl2RoleId = $mprRules['approver_2_role_id'] ?? null;
                                $mprLvl1Role = $daftarRole->firstWhere('id', $mprLvl1RoleId);
                                $mprLvl2Role = $daftarRole->firstWhere('id', $mprLvl2RoleId);

                                // CAR
                                $carRules = $rules['car'] ?? [];
                                $carLevels = $carRules['levels'] ?? 1;
                                $carLvl1RoleId = $carRules['approver_1_role_id'] ?? null;
                                $carLvl2RoleId = $carRules['approver_2_role_id'] ?? null;
                                $carLvl1Role = $daftarRole->firstWhere('id', $carLvl1RoleId);
                                $carLvl2Role = $daftarRole->firstWhere('id', $carLvl2RoleId);
                            @endphp
                            <tr class="role-row hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                                        <span>{{ $role->role_name }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($role->parentRole)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800 inline-flex items-center gap-1.5 shadow-2xs">
                                            <i class="fa-solid fa-arrow-turn-up text-[9px] text-sky-500"></i> {{ $role->parentRole->role_name }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200/60 dark:border-slate-700 uppercase tracking-wider">
                                            Top Level (Puncak)
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1.5 text-[11px] min-w-[220px]">
                                        {{-- Cuti --}}
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/50 dark:border-sky-800 shrink-0">
                                                Cuti
                                            </span>
                                            @if($cutiLvl1Role)
                                                <span class="text-slate-700 dark:text-slate-300 font-medium">
                                                    {{ $cutiLevels }} Step: {{ $cutiLvl1Role->role_name }}{{ $cutiLevels == 2 && $cutiLvl2Role ? ' → ' . $cutiLvl2Role->role_name : '' }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[10px]">Belum diatur</span>
                                            @endif
                                        </div>

                                        {{-- MPR --}}
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/50 dark:border-purple-800 shrink-0">
                                                MPR
                                            </span>
                                            @if($mprLvl1Role)
                                                <span class="text-slate-700 dark:text-slate-300 font-medium">
                                                    {{ $mprLevels }} Step: {{ $mprLvl1Role->role_name }}{{ $mprLevels == 2 && $mprLvl2Role ? ' → ' . $mprLvl2Role->role_name : '' }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[10px]">Belum diatur</span>
                                            @endif
                                        </div>

                                        {{-- CAR --}}
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800 shrink-0">
                                                CAR
                                            </span>
                                            @if($carLvl1Role)
                                                <span class="text-slate-700 dark:text-slate-300 font-medium">
                                                    {{ $carLevels }} Step: {{ $carLvl1Role->role_name }}{{ $carLevels == 2 && $carLvl2Role ? ' → ' . $carLvl2Role->role_name : '' }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[10px]">Belum diatur</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ $role->description ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-100/80 dark:border-sky-800">
                                        <i class="fa-solid fa-users text-[9px] mr-1 text-sky-500"></i>
                                        {{ $role->users_count }} Orang
                                    </span>
                                </td>

                                @if(Auth::user()?->isLevel1())
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button"
                                                onclick='bukaModalEditRoleDetailed({{ $role->id }}, "{{ addslashes($role->role_name) }}", {{ $role->parent_role_id ?? "null" }}, "{{ addslashes($role->description ?? "") }}", {{ json_encode($role->approval_rules ?? []) }})'
                                                class="px-2.5 py-1.5 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-xl text-xs font-bold transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs"
                                                title="Edit Role & Hierarki / Approver">
                                            <i class="fa-solid fa-pen-to-square text-[11px]"></i> Edit
                                        </button>

                                        <form id="form-delete-role-{{ $role->id }}" action="{{ route('admin.role.destroy', $role->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="konfirmasiHapus('form-delete-role-{{ $role->id }}', 'Role Jabatan: {{ addslashes($role->role_name) }}')"
                                                    class="p-1.5 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-xl text-xs transition-colors cursor-pointer shadow-2xs"
                                                    title="Hapus Role">
                                                <i class="fa-solid fa-trash-can text-[11px]"></i>
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
    </div>

</div>
@endsection

@push('modals')
{{-- MODAL FORM TAMBAH ROLE --}}
<div id="modalTambahRole" class="fixed inset-0 z-50 items-center justify-center hidden p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200 overflow-y-auto" onclick="if(event.target === this) tutupModalTambahRole()">
    <div id="modalTambahRoleCard" class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-xl p-5 sm:p-6 my-auto text-left border border-slate-100 dark:border-slate-700/80 transition-all duration-200 transform scale-95 opacity-0 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4 shrink-0">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-sky-600 dark:text-sky-400"></i> Tambah Role Baru
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tambah jabatan baru dan tentukan relasi atasan langsungnya.</p>
            </div>
            <button type="button" onclick="tutupModalTambahRole()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formTambahRoleAction" action="{{ route('admin.role.store') }}" method="POST" class="space-y-4 overflow-y-auto pr-1 flex-1 flex flex-col justify-between">
            @csrf
            <div id="tambahRoleRowsContainer" class="space-y-4"></div>

            <div id="btnTambahRoleRowContainer" class="pt-2">
                <button type="button" onclick="tambahBarisRoleBaru()" class="w-full py-2 bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-sky-600 dark:text-sky-400 border border-dashed border-sky-300 dark:border-sky-700 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah Baris Role Lain
                </button>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700 shrink-0 mt-4">
                <button type="button" onclick="tutupModalTambahRole()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer">Simpan Data Role</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT ROLE, HIERARKI & PENYETUJU MODUL --}}
<div id="modalEditRole" class="fixed inset-0 z-50 items-center justify-center hidden p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200 overflow-y-auto" onclick="if(event.target === this) tutupModalEditRole()">
    <div id="modalEditRoleCard" class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl p-5 sm:p-6 my-auto text-left border border-slate-100 dark:border-slate-700/80 transition-all duration-200 transform scale-95 opacity-0 max-h-[92vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4 shrink-0">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-500"></i> Edit Role & Alur Persetujuan
                </h3>
                <p id="labelEditRoleNama" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-semibold"></p>
            </div>
            <button type="button" onclick="tutupModalEditRole()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditRoleAction" method="POST" class="space-y-4 overflow-y-auto pr-1 flex-1 flex flex-col justify-between">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- SECTION 1: INFORMASI ROLE & HIERARKI --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-sitemap text-sky-600 dark:text-sky-400"></i> Identitas Jabatan & Atasan Langsung
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Nama Role / Jabatan <span class="text-rose-500">*</span></label>
                            <input type="text" id="edit_role_name" name="role_name" required placeholder="Nama role / jabatan" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Atasan Langsung (Struktur)</label>
                            <select id="edit_parent_role_id" name="parent_role_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs focus:outline-none focus:border-sky-500 cursor-pointer">
                                <option value="">-- Top Level (Puncak / Tanpa Atasan) --</option>
                                @foreach($daftarRole as $p)
                                    <option value="{{ $p->id }}" id="opt_parent_role_{{ $p->id }}">{{ $p->role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Deskripsi Wewenang / Catatan</label>
                        <textarea id="edit_description" name="description" rows="2" placeholder="Penjelasan wewenang atau cakupan tugas..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs focus:outline-none focus:border-sky-500"></textarea>
                    </div>
                </div>

                {{-- SECTION 2: ALUR PERSETUJUAN MODUL (CUTI, MPR, CAR) --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-stamp text-sky-600 dark:text-sky-400"></i> Alur Persetujuan Modul
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">Atur tahapan & role penyetuju tiap modul</span>
                    </div>

                    {{-- 1. MODUL CUTI --}}
                    <div class="p-3.5 bg-sky-50/40 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/50 rounded-2xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-sky-900 dark:text-sky-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-umbrella-beach text-sky-600 dark:text-sky-400"></i> Modul Pengajuan Cuti
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">Tingkatan:</span>
                                <select id="edit_cuti_approval_levels" name="cuti_approval_levels" onchange="toggleModalCutiStep(this.value)" class="px-2.5 py-1 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-sky-200 dark:border-sky-800 rounded-lg text-xs font-bold focus:border-sky-500 cursor-pointer">
                                    <option value="1">1 Step</option>
                                    <option value="2">2 Step</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 1</label>
                                <select id="edit_cuti_approver_1_role_id" name="cuti_approver_1_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:border-sky-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 1) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="modal_box_cuti_step2" class="hidden">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 2</label>
                                <select id="edit_cuti_approver_2_role_id" name="cuti_approver_2_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-sky-300 dark:border-sky-700 rounded-xl text-xs focus:border-sky-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 2) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MODUL MPR --}}
                    <div class="p-3.5 bg-purple-50/40 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/50 rounded-2xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-purple-900 dark:text-purple-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-boxes-packing text-purple-600 dark:text-purple-400"></i> Modul Pengajuan MPR
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">Tingkatan:</span>
                                <select id="edit_mpr_approval_levels" name="mpr_approval_levels" onchange="toggleModalMprStep(this.value)" class="px-2.5 py-1 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-purple-200 dark:border-purple-800 rounded-lg text-xs font-bold focus:border-purple-500 cursor-pointer">
                                    <option value="1">1 Step</option>
                                    <option value="2">2 Step</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 1</label>
                                <select id="edit_mpr_approver_1_role_id" name="mpr_approver_1_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:border-purple-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 1) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="modal_box_mpr_step2" class="hidden">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 2</label>
                                <select id="edit_mpr_approver_2_role_id" name="mpr_approver_2_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-purple-300 dark:border-purple-700 rounded-xl text-xs focus:border-purple-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 2) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 3. MODUL CAR --}}
                    <div class="p-3.5 bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-file-invoice-dollar text-emerald-600 dark:text-emerald-400"></i> Modul Pengajuan CAR
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">Tingkatan:</span>
                                <select id="edit_car_approval_levels" name="car_approval_levels" onchange="toggleModalCarStep(this.value)" class="px-2.5 py-1 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-bold focus:border-emerald-500 cursor-pointer">
                                    <option value="1">1 Step</option>
                                    <option value="2">2 Step</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 1</label>
                                <select id="edit_car_approver_1_role_id" name="car_approver_1_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:border-emerald-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 1) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="modal_box_car_step2" class="hidden">
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Approver Step 2</label>
                                <select id="edit_car_approver_2_role_id" name="car_approver_2_role_id" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs focus:border-emerald-500 cursor-pointer">
                                    <option value="">-- Pilih Role Penyetuju (Step 2) --</option>
                                    @foreach($daftarRole as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700 shrink-0 mt-4">
                <button type="button" onclick="tutupModalEditRole()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Role
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var roleTambahIndex = 0;
    var sessionSuccess   = {!! json_encode(session('success')) !!};
    var sessionError     = {!! json_encode(session('error')) !!};
    var validationErrors = {!! json_encode((isset($errors) && method_exists($errors, 'all')) ? $errors->all() : []) !!};
    var rawRolesData     = {!! json_encode($daftarRole ?? []) !!};

    function toggleModalCutiStep(levels) {
        const box = document.getElementById('modal_box_cuti_step2');
        if (box) {
            if (parseInt(levels) === 2) {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                const sel = document.getElementById('edit_cuti_approver_2_role_id');
                if (sel) sel.value = '';
            }
        }
    }

    function toggleModalMprStep(levels) {
        const box = document.getElementById('modal_box_mpr_step2');
        if (box) {
            if (parseInt(levels) === 2) {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                const sel = document.getElementById('edit_mpr_approver_2_role_id');
                if (sel) sel.value = '';
            }
        }
    }

    function toggleModalCarStep(levels) {
        const box = document.getElementById('modal_box_car_step2');
        if (box) {
            if (parseInt(levels) === 2) {
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                const sel = document.getElementById('edit_car_approver_2_role_id');
                if (sel) sel.value = '';
            }
        }
    }

    function ensureMermaidReady(callback) {
        if (typeof window.mermaid !== 'undefined') {
            if (!window.__mermaidInitialized) {
                window.mermaid.initialize({
                    startOnLoad: false,
                    theme: 'default',
                    securityLevel: 'loose'
                });
                window.__mermaidInitialized = true;
            }
            callback();
            return;
        }

        let attempts = 0;
        const interval = setInterval(() => {
            attempts++;
            if (typeof window.mermaid !== 'undefined') {
                clearInterval(interval);
                if (!window.__mermaidInitialized) {
                    window.mermaid.initialize({
                        startOnLoad: false,
                        theme: 'default',
                        securityLevel: 'loose'
                    });
                    window.__mermaidInitialized = true;
                }
                callback();
            } else if (attempts > 60) {
                clearInterval(interval);
                console.error('Mermaid library load timeout');
                const diagramContainer = document.getElementById('mermaidDiagram');
                if (diagramContainer) {
                    diagramContainer.innerHTML = `
                        <div class="text-center py-6 text-rose-500 text-xs font-semibold">
                            <i class="fa-solid fa-triangle-exclamation text-2xl mb-2 block"></i>
                            Gagal memuat pustaka diagram. Periksa koneksi internet lalu klik tombol "Refresh Diagram".
                        </div>
                    `;
                }
            }
        }, 50);
    }

    var renderCounter = 0;

    async function renderMermaidDiagram() {
        const diagramContainer = document.getElementById('mermaidDiagram');
        if (!diagramContainer) return;

        diagramContainer.innerHTML = `
            <div class="flex items-center justify-center py-10 text-slate-400 text-xs">
                <i class="fa-solid fa-circle-notch fa-spin text-lg mr-2 text-sky-500"></i> Memuat struktur organisasi...
            </div>
        `;

        ensureMermaidReady(async () => {
            let graphDefinition = 'graph TD\n';
            graphDefinition += '    COMPANY["B O D"]\n';

            if (rawRolesData && rawRolesData.length > 0) {
                rawRolesData.forEach(r => {
                    const cleanRoleName = (r.role_name || '').replace(/["'()\\<>{}]/g, ' ').trim();
                    const nodeId = `R${r.id}`;
                    const nodeLabel = `"${cleanRoleName}"`;

                    if (!r.parent_role_id || !rawRolesData.some(p => p.id == r.parent_role_id)) {
                        graphDefinition += `    COMPANY --> ${nodeId}[${nodeLabel}]\n`;
                    } else {
                        const parentNodeId = `R${r.parent_role_id}`;
                        graphDefinition += `    ${parentNodeId} --> ${nodeId}[${nodeLabel}]\n`;
                    }
                });
            }

            try {
                renderCounter++;
                const elementId = `mermaidSvg_${Date.now()}_${renderCounter}`;
                const { svg } = await window.mermaid.render(elementId, graphDefinition);
                diagramContainer.innerHTML = svg;
            } catch (error) {
                console.error('Mermaid Render Error:', error);
                diagramContainer.innerHTML = `
                    <div class="text-center py-6 text-rose-500 text-xs font-semibold">
                        <i class="fa-solid fa-triangle-exclamation text-2xl mb-2 block"></i>
                        Gagal merender skema. Klik tombol "Refresh Diagram" di atas.
                    </div>
                `;
            }
        });
    }

    function switchRoleTab(tabId) {
        if (!document.getElementById('tab-hierarchy')) return;

        let targetTab = document.getElementById(tabId);
        let activeBtn = document.getElementById('btn-' + tabId);

        if (!targetTab || !activeBtn) {
            tabId = 'tab-hierarchy';
            targetTab = document.getElementById('tab-hierarchy');
            activeBtn = document.getElementById('btn-tab-hierarchy');
        }

        try {
            localStorage.setItem('active_tab_role', tabId);
        } catch (e) {}

        const container = targetTab ? targetTab.parentElement : document;
        container.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-sky-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        });

        if (targetTab) {
            targetTab.classList.remove('hidden');
        }

        if (activeBtn) {
            activeBtn.classList.add('bg-sky-600', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        }

        if (tabId === 'tab-hierarchy') {
            setTimeout(() => {
                renderMermaidDiagram();
            }, 60);
        }
    }
    window.switchRoleTab = switchRoleTab;
    window.switchTab = switchRoleTab;

    function initRoleIndexPage() {
        if (!document.getElementById('tab-hierarchy')) return;

        window.switchTab = switchRoleTab;

        const sessionTab = {!! json_encode(session('active_tab')) !!};
        let savedTab = null;
        try {
            savedTab = localStorage.getItem('active_tab_role');
        } catch (e) {}

        const defaultTab = sessionTab || (savedTab && document.getElementById(savedTab) ? savedTab : 'tab-hierarchy');
        switchRoleTab(defaultTab);

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
    }

    document.addEventListener('DOMContentLoaded', initRoleIndexPage);
    document.addEventListener('turbo:load', initRoleIndexPage);
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initRoleIndexPage();
    }

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

    // HANDLER MODAL TAMBAH ROLE
    function tambahBarisRoleBaru() {
        const container = document.getElementById('tambahRoleRowsContainer');
        const showDelete = container.children.length > 0;

        let parentOptionsHtml = '<option value="">-- Top Level (Puncak / Tanpa Atasan) --</option>';
        if (rawRolesData && rawRolesData.length > 0) {
            rawRolesData.forEach(r => {
                parentOptionsHtml += `<option value="${r.id}">${r.role_name}</option>`;
            });
        }

        const rowHtml = `
            <div class="role-item-row bg-slate-50/70 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 relative space-y-3">
                ${showDelete ? `
                    <button type="button" onclick="hapusBarisTambah(this)" class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 p-1 rounded-lg transition-colors cursor-pointer" title="Hapus Baris Ini">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                ` : ''}

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Nama Role / Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="roles[${roleTambahIndex}][role_name]" required placeholder="Contoh: Supervisor Operasional" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:outline-none focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Atasan Langsung (Struktur)</label>
                    <select name="roles[${roleTambahIndex}][parent_role_id]" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                        ${parentOptionsHtml}
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Deskripsi Wewenang / Catatan</label>
                    <textarea name="roles[${roleTambahIndex}][description]" rows="2" placeholder="Penjelasan wewenang role..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs focus:outline-none focus:border-sky-500"></textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        roleTambahIndex++;
    }

    function hapusBarisTambah(btn) {
        const parentRow = btn.closest('.role-item-row');
        if (parentRow) parentRow.remove();
    }

    function bukaModalTambahRole() {
        document.getElementById('tambahRoleRowsContainer').innerHTML = '';
        roleTambahIndex = 0;
        tambahBarisRoleBaru();

        const modal = document.getElementById('modalTambahRole');
        const modalCard = document.getElementById('modalTambahRoleCard');

        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            document.body.classList.add('overflow-hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (modalCard) {
                setTimeout(() => {
                    modalCard.classList.remove('scale-95', 'opacity-0');
                    modalCard.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }
    }

    function tutupModalTambahRole() {
        const modal = document.getElementById('modalTambahRole');
        const modalCard = document.getElementById('modalTambahRoleCard');

        if (modalCard) {
            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            document.body.classList.remove('overflow-hidden');
        }, 150);
    }

    // HANDLER MODAL EDIT ROLE & ALUR APPROVER
    function bukaModalEditRoleDetailed(roleId, roleName, parentRoleId, description, approvalRules) {
        document.getElementById('labelEditRoleNama').innerText = 'Role: ' + roleName;
        document.getElementById('formEditRoleAction').action = `/admin/role/${roleId}`;

        document.getElementById('edit_role_name').value = roleName || '';
        document.getElementById('edit_description').value = description || '';

        // Atur opsi parent role (sembunyikan opsi diri sendiri)
        const parentSelect = document.getElementById('edit_parent_role_id');
        Array.from(parentSelect.options).forEach(opt => {
            opt.hidden = (parseInt(opt.value) === parseInt(roleId));
        });
        parentSelect.value = parentRoleId ? parentRoleId : '';

        // Parse approvalRules
        const rules = (approvalRules && typeof approvalRules === 'object') ? approvalRules : {};

        // Cuti
        const cutiRules = rules.cuti || {};
        const cutiLvl = cutiRules.levels || rules.approval_levels || 1;
        const cutiApp1 = cutiRules.approver_1_role_id || rules.approver_level_1_role_id || '';
        const cutiApp2 = cutiRules.approver_2_role_id || rules.approver_level_2_role_id || '';
        document.getElementById('edit_cuti_approval_levels').value = cutiLvl;
        document.getElementById('edit_cuti_approver_1_role_id').value = cutiApp1;
        document.getElementById('edit_cuti_approver_2_role_id').value = cutiApp2;
        toggleModalCutiStep(cutiLvl);

        // MPR
        const mprRules = rules.mpr || {};
        const mprLvl = mprRules.levels || 1;
        const mprApp1 = mprRules.approver_1_role_id || '';
        const mprApp2 = mprRules.approver_2_role_id || '';
        document.getElementById('edit_mpr_approval_levels').value = mprLvl;
        document.getElementById('edit_mpr_approver_1_role_id').value = mprApp1;
        document.getElementById('edit_mpr_approver_2_role_id').value = mprApp2;
        toggleModalMprStep(mprLvl);

        // CAR
        const carRules = rules.car || {};
        const carLvl = carRules.levels || 1;
        const carApp1 = carRules.approver_1_role_id || '';
        const carApp2 = carRules.approver_2_role_id || '';
        document.getElementById('edit_car_approval_levels').value = carLvl;
        document.getElementById('edit_car_approver_1_role_id').value = carApp1;
        document.getElementById('edit_car_approver_2_role_id').value = carApp2;
        toggleModalCarStep(carLvl);

        const modal = document.getElementById('modalEditRole');
        const modalCard = document.getElementById('modalEditRoleCard');

        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            document.body.classList.add('overflow-hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (modalCard) {
                setTimeout(() => {
                    modalCard.classList.remove('scale-95', 'opacity-0');
                    modalCard.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }
    }

    function tutupModalEditRole() {
        const modal = document.getElementById('modalEditRole');
        const modalCard = document.getElementById('modalEditRoleCard');

        if (modalCard) {
            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            document.body.classList.remove('overflow-hidden');
        }, 150);
    }

    // Listener tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modalTambah = document.getElementById('modalTambahRole');
            if (modalTambah && !modalTambah.classList.contains('hidden')) {
                tutupModalTambahRole();
            }
            const modalEdit = document.getElementById('modalEditRole');
            if (modalEdit && !modalEdit.classList.contains('hidden')) {
                tutupModalEditRole();
            }
        }
    });
</script>
@endpush

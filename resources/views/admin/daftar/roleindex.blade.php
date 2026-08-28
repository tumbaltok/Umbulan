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
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4 space-y-6">

    {{-- NAVIGASI TAB UTAMA --}}
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl shadow-xs transition-colors">
        <div class="flex space-x-2">
            <button type="button" onclick="switchTab('tab-hierarchy')" id="btn-tab-hierarchy" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-sky-600 text-white shadow-xs cursor-pointer">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Skema Pohon & Matriks Atasan
            </button>
            <button type="button" onclick="switchTab('tab-roles')" id="btn-tab-roles" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <i class="fa-solid fa-user-shield mr-1.5"></i> Daftar Role
            </button>
        </div>
    </div>

    {{-- TAB 1: SKEMA POHON HIRARKI & MATRIKS RELASI ATASAN --}}
    <div id="tab-hierarchy" class="tab-content space-y-6">

        {{-- DIAGRAM VISUAL POHON ORGANISASI JABATAN --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 space-y-4 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-sitemap text-indigo-600 dark:text-indigo-400"></i> Visualisasi Skema Struktur Organisasi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Diagram hirarki struktur komando yang dirender otomatis dari database.</p>
                </div>
                <button type="button" onclick="renderMermaidDiagram()" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh Diagram
                </button>
            </div>

            <div class="mermaid-container flex justify-center py-4 bg-slate-50 dark:bg-slate-900 rounded-2xl">
                <div id="mermaidDiagram" class="w-full flex justify-center min-h-[180px]"></div>
            </div>
        </div>

        {{-- FORM MATRIKS CUSTOM RELASI ATASAN & DYNAMIC APPROVAL RULES --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 space-y-6 transition-colors">
            <div class="border-b border-slate-100 dark:border-slate-700 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-sky-600 dark:text-sky-400"></i> Matriks Hierarki & Penyetuju Dinamis
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur struktur atasan langsung serta alur persetujuan (1 Step / 2 Step) untuk Modul CUTI, MPR, dan CAR.</p>
                </div>

                {{-- SUB-TAB SWITCHER MODUL --}}
                <div class="flex items-center bg-slate-100 dark:bg-slate-900 p-1 rounded-xl gap-1 self-start">
                    <button type="button" onclick="switchMatrixTab('matrix-cuti')" id="btn-matrix-cuti" class="matrix-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-sky-700 dark:text-sky-400 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-umbrella-beach text-[11px]"></i> Modul Cuti
                    </button>
                    <button type="button" onclick="switchMatrixTab('matrix-mpr')" id="btn-matrix-mpr" class="matrix-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-boxes-packing text-[11px]"></i> Modul MPR
                    </button>
                    <button type="button" onclick="switchMatrixTab('matrix-car')" id="btn-matrix-car" class="matrix-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-file-invoice-dollar text-[11px]"></i> Modul CAR
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.role.hierarchy.update') }}" method="POST">
                @csrf
                <div class="overflow-x-auto border border-slate-100 dark:border-slate-700 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase border-b border-slate-100 dark:border-slate-700">
                                <th class="p-3.5">Role / Jabatan</th>
                                <th class="p-3.5">Atasan Langsung (Struktur)</th>
                                
                                {{-- HEADER CUTI --}}
                                <th class="p-3.5 text-center col-matrix-cuti">Alur Cuti</th>
                                <th class="p-3.5 col-matrix-cuti">Approver Cuti (Step 1)</th>
                                <th class="p-3.5 col-matrix-cuti">Approver Cuti (Step 2)</th>

                                {{-- HEADER CAR --}}
                                <th class="p-3.5 text-center col-matrix-car hidden">Alur CAR</th>
                                <th class="p-3.5 col-matrix-car hidden">Approver CAR (Step 1)</th>
                                <th class="p-3.5 col-matrix-car hidden">Approver CAR (Step 2)</th>

                                {{-- HEADER MPR --}}
                                <th class="p-3.5 text-center col-matrix-mpr hidden">Alur MPR</th>
                                <th class="p-3.5 col-matrix-mpr hidden">Approver MPR (Step 1)</th>
                                <th class="p-3.5 col-matrix-mpr hidden">Approver MPR (Step 2)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300">
                            @foreach($daftarRole as $idx => $r)
                                @php
                                    $rules = $r->approval_rules ?? [];
                                    
                                    // Rule Cuti
                                    $cutiRules = $rules['cuti'] ?? [];
                                    $cutiLevels = $cutiRules['levels'] ?? ($rules['approval_levels'] ?? 1);
                                    $cutiLvl1RoleId = $cutiRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
                                    $cutiLvl2RoleId = $cutiRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);

                                    // Rule CAR
                                    $carRules = $rules['car'] ?? [];
                                    $carLevels = $carRules['levels'] ?? ($rules['approval_levels'] ?? 1);
                                    $carLvl1RoleId = $carRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
                                    $carLvl2RoleId = $carRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);

                                    // Rule MPR
                                    $mprRules = $rules['mpr'] ?? [];
                                    $mprLevels = $mprRules['levels'] ?? ($rules['approval_levels'] ?? 1);
                                    $mprLvl1RoleId = $mprRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
                                    $mprLvl2RoleId = $mprRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/40 transition-colors">
                                    {{-- NAMA ROLE --}}
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-100 align-middle">
                                        <input type="hidden" name="hierarchy[{{ $idx }}][role_id]" value="{{ $r->id }}">
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $r->role_name }}</span>
                                        </div>
                                    </td>

                                    {{-- PARENT ROLE --}}
                                    <td class="p-3 align-middle">
                                        <select name="hierarchy[{{ $idx }}][parent_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:border-sky-500 cursor-pointer">
                                            <option value="">-- Top Level (Tidak Ada Atasan) --</option>
                                            @foreach($daftarRole as $parentCandidate)
                                                @if($parentCandidate->id != $r->id)
                                                    <option value="{{ $parentCandidate->id }}" {{ $r->parent_role_id == $parentCandidate->id ? 'selected' : '' }}>
                                                        {{ $parentCandidate->role_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- ==================== KOLOM MODUL CUTI ==================== --}}
                                    <td class="p-3 text-center align-middle col-matrix-cuti">
                                        <select name="hierarchy[{{ $idx }}][cuti_approval_levels]"
                                                onchange="toggleCutiApproverInputs(this, {{ $idx }})"
                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold focus:border-sky-500 cursor-pointer">
                                            <option value="1" {{ $cutiLevels == 1 ? 'selected' : '' }}>1 Step</option>
                                            <option value="2" {{ $cutiLevels == 2 ? 'selected' : '' }}>2 Step</option>
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-cuti">
                                        <select name="hierarchy[{{ $idx }}][cuti_approver_1_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:border-sky-500 cursor-pointer">
                                            <option value="">-- Pilih Role Penyetuju (Step 1) --</option>
                                            @foreach($daftarRole as $approverCandidate)
                                                <option value="{{ $approverCandidate->id }}" {{ $cutiLvl1RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                    {{ $approverCandidate->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-cuti">
                                        <div id="box_cuti_approver_lvl2_{{ $idx }}" class="{{ $cutiLevels == 2 ? '' : 'hidden' }}">
                                            <select name="hierarchy[{{ $idx }}][cuti_approver_2_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs focus:border-indigo-500 cursor-pointer">
                                                <option value="">-- Pilih Role Penyetuju (Step 2) --</option>
                                                @foreach($daftarRole as $approverCandidate)
                                                    <option value="{{ $approverCandidate->id }}" {{ $cutiLvl2RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                        {{ $approverCandidate->role_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    {{-- ==================== KOLOM MODUL MPR ==================== --}}
                                    <td class="p-3 text-center align-middle col-matrix-mpr hidden">
                                        <select name="hierarchy[{{ $idx }}][mpr_approval_levels]"
                                                onchange="toggleMprApproverInputs(this, {{ $idx }})"
                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold focus:border-purple-500 cursor-pointer">
                                            <option value="1" {{ $mprLevels == 1 ? 'selected' : '' }}>1 Step</option>
                                            <option value="2" {{ $mprLevels == 2 ? 'selected' : '' }}>2 Step</option>
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-mpr hidden">
                                        <select name="hierarchy[{{ $idx }}][mpr_approver_1_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:border-purple-500 cursor-pointer">
                                            <option value="">-- Pilih Role Penyetuju MPR (Step 1) --</option>
                                            @foreach($daftarRole as $approverCandidate)
                                                <option value="{{ $approverCandidate->id }}" {{ $mprLvl1RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                    {{ $approverCandidate->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-mpr hidden">
                                        <div id="box_mpr_approver_lvl2_{{ $idx }}" class="{{ $mprLevels == 2 ? '' : 'hidden' }}">
                                            <select name="hierarchy[{{ $idx }}][mpr_approver_2_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-purple-200 dark:border-purple-800 rounded-lg text-xs focus:border-purple-500 cursor-pointer">
                                                <option value="">-- Pilih Role Penyetuju MPR (Step 2) --</option>
                                                @foreach($daftarRole as $approverCandidate)
                                                    <option value="{{ $approverCandidate->id }}" {{ $mprLvl2RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                        {{ $approverCandidate->role_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    {{-- ==================== KOLOM MODUL CAR ==================== --}}
                                    <td class="p-3 text-center align-middle col-matrix-car hidden">
                                        <select name="hierarchy[{{ $idx }}][car_approval_levels]"
                                                onchange="toggleCarApproverInputs(this, {{ $idx }})"
                                                class="px-2.5 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold focus:border-emerald-500 cursor-pointer">
                                            <option value="1" {{ $carLevels == 1 ? 'selected' : '' }}>1 Step</option>
                                            <option value="2" {{ $carLevels == 2 ? 'selected' : '' }}>2 Step</option>
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-car hidden">
                                        <select name="hierarchy[{{ $idx }}][car_approver_1_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:border-emerald-500 cursor-pointer">
                                            <option value="">-- Pilih Role Penyetuju CAR (Step 1) --</option>
                                            @foreach($daftarRole as $approverCandidate)
                                                <option value="{{ $approverCandidate->id }}" {{ $carLvl1RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                    {{ $approverCandidate->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3 align-middle col-matrix-car hidden">
                                        <div id="box_car_approver_lvl2_{{ $idx }}" class="{{ $carLevels == 2 ? '' : 'hidden' }}">
                                            <select name="hierarchy[{{ $idx }}][car_approver_2_role_id]" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs focus:border-emerald-500 cursor-pointer">
                                                <option value="">-- Pilih Role Penyetuju CAR (Step 2) --</option>
                                                @foreach($daftarRole as $approverCandidate)
                                                    <option value="{{ $approverCandidate->id }}" {{ $carLvl2RoleId == $approverCandidate->id ? 'selected' : '' }}>
                                                        {{ $approverCandidate->role_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Seluruh Matriks Hirarki
                    </button>
                </div>
            </form>
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
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola tingkat hak akses dan hirarki wewenang jabatan.</p>
                </div>

                @if(Auth::user()->isLevel1())
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
                            <th class="px-4 py-3.5">Hak Akses</th>
                            <th class="px-4 py-3.5">Deskripsi Wewenang</th>
                            <th class="px-4 py-3.5 text-center">Total Staf</th>
                            @if(Auth::user()->isLevel1())
                            <th class="px-4 py-3.5 text-center w-20">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                        @forelse($daftarRole as $role)
                            <tr class="role-row hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">{{ $role->role_name }}</td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($role->parentRole)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-100 dark:border-sky-800 inline-flex items-center gap-1">
                                            <i class="fa-solid fa-turn-up text-[9px]"></i> {{ $role->parentRole->role_name }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Top Level (Puncak)</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(($role->level ?? 3) == 1)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-100 dark:border-purple-800 inline-flex items-center gap-1">
                                            <i class="fa-solid fa-shield-halved text-[9px]"></i> Full Akses
                                        </span>
                                    @elseif(($role->level ?? 3) == 2)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800 inline-flex items-center gap-1">
                                            <i class="fa-solid fa-eye text-[9px]"></i> Only Read
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 inline-flex items-center gap-1">
                                            <i class="fa-solid fa-user text-[9px]"></i> User
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                                    {{ $role->description ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-100/80 dark:border-sky-800">
                                        <i class="fa-solid fa-users text-[9px] mr-1 text-sky-500"></i>
                                        {{ $role->users_count }} Orang
                                    </span>
                                </td>

                                @if(Auth::user()->isLevel1())
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button"
                                                data-role='@json($role)'
                                                onclick="bukaModalEditRole(this)"
                                                class="p-1 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/60 rounded-md text-xs transition-colors cursor-pointer"
                                                title="Edit Role">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form id="form-delete-role-{{ $role->id }}" action="{{ route('admin.role.destroy', $role->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="konfirmasiHapus('form-delete-role-{{ $role->id }}', 'Role Jabatan: {{ $role->role_name }}')"
                                                    class="p-1 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-md text-xs transition-colors cursor-pointer"
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
    </div>

</div>

{{-- MODAL FORM TAMBAH / EDIT ROLE --}}
<div id="modalFormRole" class="fixed inset-0 z-50 items-center justify-center hidden p-4">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalFormRole()"></div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-xl p-6 relative z-10 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col border border-slate-100 dark:border-slate-700">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base" id="judulModalFormRole">Tambah Role Baru</h3>
            <button type="button" onclick="tutupModalFormRole()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg cursor-pointer">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let roleIndex = 0;

    const sessionSuccess   = JSON.parse(`{!! json_encode(session('success')) !!}`);
    const sessionError     = JSON.parse(`{!! json_encode(session('error')) !!}`);
    const validationErrors = JSON.parse(`{!! json_encode($errors->all()) !!}`);

    const rawRolesData = JSON.parse('@json($daftarRole)');

    // SWITCH SUB-TAB MODUL MATRIKS HIERARKI (CUTI / CAR / MPR)
    function switchMatrixTab(tabName) {
        // Sembunyikan semua kolom modul
        document.querySelectorAll('.col-matrix-cuti, .col-matrix-car, .col-matrix-mpr').forEach(el => {
            el.classList.add('hidden');
        });

        // Reset styling tombol sub-tab
        document.querySelectorAll('.matrix-tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-sky-700', 'dark:text-sky-400', 'shadow-xs', 'text-emerald-700', 'dark:text-emerald-400', 'text-purple-700', 'dark:text-purple-400');
            btn.classList.add('text-slate-500', 'dark:text-slate-400', 'hover:text-slate-800', 'dark:hover:text-slate-200');
        });

        // Tampilkan kolom modul yang aktif
        if (tabName === 'matrix-cuti') {
            document.querySelectorAll('.col-matrix-cuti').forEach(el => el.classList.remove('hidden'));
            const btn = document.getElementById('btn-matrix-cuti');
            if (btn) {
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-sky-700', 'dark:text-sky-400', 'shadow-xs');
                btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            }
        } else if (tabName === 'matrix-car') {
            document.querySelectorAll('.col-matrix-car').forEach(el => el.classList.remove('hidden'));
            const btn = document.getElementById('btn-matrix-car');
            if (btn) {
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-emerald-700', 'dark:text-emerald-400', 'shadow-xs');
                btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            }
        } else if (tabName === 'matrix-mpr') {
            document.querySelectorAll('.col-matrix-mpr').forEach(el => el.classList.remove('hidden'));
            const btn = document.getElementById('btn-matrix-mpr');
            if (btn) {
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-purple-700', 'dark:text-purple-400', 'shadow-xs');
                btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            }
        }
    }

    // DYNAMIC TOGGLE VISIBILITAS SELECT APPROVER CUTI LEVEL 2
    function toggleCutiApproverInputs(selectElement, index) {
        const boxLvl2 = document.getElementById(`box_cuti_approver_lvl2_${index}`);
        if (!boxLvl2) return;

        if (parseInt(selectElement.value) === 2) {
            boxLvl2.classList.remove('hidden');
        } else {
            boxLvl2.classList.add('hidden');
            const selectLvl2 = boxLvl2.querySelector('select');
            if (selectLvl2) selectLvl2.value = '';
        }
    }

    // DYNAMIC TOGGLE VISIBILITAS SELECT APPROVER CAR LEVEL 2
    function toggleCarApproverInputs(selectElement, index) {
        const boxLvl2 = document.getElementById(`box_car_approver_lvl2_${index}`);
        if (!boxLvl2) return;

        if (parseInt(selectElement.value) === 2) {
            boxLvl2.classList.remove('hidden');
        } else {
            boxLvl2.classList.add('hidden');
            const selectLvl2 = boxLvl2.querySelector('select');
            if (selectLvl2) selectLvl2.value = '';
        }
    }

    // DYNAMIC TOGGLE VISIBILITAS SELECT APPROVER MPR LEVEL 2
    function toggleMprApproverInputs(selectElement, index) {
        const boxLvl2 = document.getElementById(`box_mpr_approver_lvl2_${index}`);
        if (!boxLvl2) return;

        if (parseInt(selectElement.value) === 2) {
            boxLvl2.classList.remove('hidden');
        } else {
            boxLvl2.classList.add('hidden');
            const selectLvl2 = boxLvl2.querySelector('select');
            if (selectLvl2) selectLvl2.value = '';
        }
    }

    mermaid.initialize({
        startOnLoad: false,
        theme: 'default',
        securityLevel: 'loose'
    });

    let renderCounter = 0;

    async function renderMermaidDiagram() {
        const diagramContainer = document.getElementById('mermaidDiagram');
        if (!diagramContainer) return;

        diagramContainer.innerHTML = '';

        let graphDefinition = 'graph TD\n';
        graphDefinition += '    COMPANY["B O D"]\n';

        if (rawRolesData && rawRolesData.length > 0) {
            rawRolesData.forEach(r => {
                const cleanRoleName = (r.role_name || '').replace(/["'()]/g, '');

                const nodeId = `R${r.id}`;
                const nodeLabel = `"${cleanRoleName}"`;

                if (!r.parent_role_id) {
                    graphDefinition += `    COMPANY --> ${nodeId}[${nodeLabel}]\n`;
                } else {
                    const parentNodeId = `R${r.parent_role_id}`;
                    graphDefinition += `    ${parentNodeId} --> ${nodeId}[${nodeLabel}]\n`;
                }
            });
        }

        try {
            renderCounter++;
            const elementId = `mermaidSvg_${renderCounter}`;
            const { svg } = await mermaid.render(elementId, graphDefinition);
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
    }

    function switchTab(tabId) {
        localStorage.setItem('active_tab_role', tabId);

        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-sky-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        });

        document.getElementById(tabId).classList.remove('hidden');

        const activeBtn = document.getElementById('btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.add('bg-sky-600', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-100');
        }

        if (tabId === 'tab-hierarchy') {
            setTimeout(() => {
                renderMermaidDiagram();
            }, 50);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const activeTabSession = JSON.parse(`{!! json_encode(session('active_tab')) !!}`);
        const savedTab = localStorage.getItem('active_tab_role') || activeTabSession || 'tab-hierarchy';

        switchTab(savedTab);

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

    function tambahBarisRole(roleData = null) {
        const container = document.getElementById('roleRowsContainer');
        const showDelete = container.children.length > 0;

        const roleNameVal = roleData ? (roleData.role_name || '') : '';
        const levelVal = roleData ? (roleData.level || 2) : 2;
        const descVal = roleData ? (roleData.description || '') : '';

        const rowHtml = `
            <div class="role-item-row bg-slate-50/70 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 relative space-y-3">
                ${showDelete ? `
                    <button type="button" onclick="hapusBaris(this)" class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 p-1 rounded-lg transition-colors cursor-pointer" title="Hapus Baris Ini">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                ` : ''}

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Nama Role / Jabatan</label>
                    <input type="text" name="roles[${roleIndex}][role_name]" value="${roleNameVal}" required placeholder="Contoh: Supervisor Operasional" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs focus:outline-none focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Tipe Hak Akses</label>
                    <select name="roles[${roleIndex}][level]" required class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-sky-500 transition-colors cursor-pointer">
                        <option value="1" ${levelVal == 1 ? 'selected' : ''}>Level 1: Full Akses (Dapat Mengelola & Mengedit Data Admin)</option>
                        <option value="2" ${levelVal == 2 ? 'selected' : ''}>Level 2: Only Read (Monitoring Fitur Admin, Penggunaan App Normal)</option>
                        <option value="3" ${levelVal == 3 ? 'selected' : ''}>Level 3: User (Staff Khusus Antarmuka Karyawan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Deskripsi Wewenang</label>
                    <textarea name="roles[${roleIndex}][description]" rows="2" placeholder="Penjelasan wewenang role..." class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-xl text-xs focus:outline-none focus:border-sky-500">${descVal}</textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        roleIndex++;
    }

    function hapusBaris(btn) {
        const parentRow = btn.closest('.role-item-row');
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
</script>
@endpush

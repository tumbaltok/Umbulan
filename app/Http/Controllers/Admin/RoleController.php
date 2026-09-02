<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\Jobdesk;
use App\Models\User\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Menampilkan daftar peran (role) dan struktur organisasi
    public function index()
    {
        $daftarRole = Role::withCount('users')->get();

        $daftarJobdesk = [];

        return view('admin.daftar.roleindex', compact('daftarRole', 'daftarJobdesk'));
    }

    // Menyimpan data peran (role) baru
    public function store(Request $request)
    {
        if ($request->has('roles')) {
            foreach ($request->roles as $roleData) {
                Role::create($roleData);
            }
        } else {
            Role::create($request->all());
        }

        return redirect()->back()->with('success', 'Data Role berhasil ditambahkan!');
    }

    // Memperbarui informasi peran (role)
    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);
        $data = $request->has('roles') ? ($request->roles[0] ?? []) : $request->all();

        $role->update($data);

        return redirect()->back()->with('success', 'Data Role berhasil diperbarui!');
    }

    // Memperbarui matriks hierarki dan aturan persetujuan (approval rules) per modul
    public function updateHierarchyMatrix(Request $request)
    {
        $request->validate([
            'hierarchy' => 'required|array',
            'hierarchy.*.role_id' => 'required|exists:roles,id',
            'hierarchy.*.parent_role_id' => 'nullable|exists:roles,id',

            // Validasi Cuti
            'hierarchy.*.cuti_approval_levels' => 'nullable|integer|in:1,2',
            'hierarchy.*.cuti_approver_1_role_id' => 'nullable|exists:roles,id',
            'hierarchy.*.cuti_approver_2_role_id' => 'nullable|exists:roles,id',

            // Validasi CAR
            'hierarchy.*.car_approval_levels' => 'nullable|integer|in:1,2',
            'hierarchy.*.car_approver_1_role_id' => 'nullable|exists:roles,id',
            'hierarchy.*.car_approver_2_role_id' => 'nullable|exists:roles,id',

            // Validasi MPR
            'hierarchy.*.mpr_approval_levels' => 'nullable|integer|in:1,2',
            'hierarchy.*.mpr_approver_1_role_id' => 'nullable|exists:roles,id',
            'hierarchy.*.mpr_approver_2_role_id' => 'nullable|exists:roles,id',
        ]);

        foreach ($request->hierarchy as $item) {
            $role = Role::findOrFail($item['role_id']);

            $parentId = (!empty($item['parent_role_id']) && $item['parent_role_id'] != $item['role_id'])
                ? $item['parent_role_id']
                : null;

            $existingRules = $role->approval_rules ?? [];
            if (!is_array($existingRules)) {
                $existingRules = [];
            }

            // 1. MODUL CUTI
            if (isset($item['cuti_approval_levels'])) {
                $cutiLevels = (int) ($item['cuti_approval_levels'] ?? 1);
                $cutiApprover1 = !empty($item['cuti_approver_1_role_id']) ? (int) $item['cuti_approver_1_role_id'] : null;
                $cutiApprover2 = ($cutiLevels === 2 && !empty($item['cuti_approver_2_role_id'])) ? (int) $item['cuti_approver_2_role_id'] : null;

                $existingRules['cuti'] = [
                    'levels'             => $cutiLevels,
                    'approver_1_role_id' => $cutiApprover1,
                    'approver_2_role_id' => $cutiApprover2,
                ];

                // Fallback compatibility
                $existingRules['approval_levels'] = $cutiLevels;
                $existingRules['approver_level_1_role_id'] = $cutiApprover1;
                $existingRules['approver_level_2_role_id'] = $cutiApprover2;
            }

            // 2. MODUL CAR
            if (isset($item['car_approval_levels'])) {
                $carLevels = (int) ($item['car_approval_levels'] ?? 1);
                $carApprover1 = !empty($item['car_approver_1_role_id']) ? (int) $item['car_approver_1_role_id'] : null;
                $carApprover2 = ($carLevels === 2 && !empty($item['car_approver_2_role_id'])) ? (int) $item['car_approver_2_role_id'] : null;

                $existingRules['car'] = [
                    'levels'             => $carLevels,
                    'approver_1_role_id' => $carApprover1,
                    'approver_2_role_id' => $carApprover2,
                ];
            }

            // 3. MODUL MPR
            if (isset($item['mpr_approval_levels'])) {
                $mprLevels = (int) ($item['mpr_approval_levels'] ?? 1);
                $mprApprover1 = !empty($item['mpr_approver_1_role_id']) ? (int) $item['mpr_approver_1_role_id'] : null;
                $mprApprover2 = ($mprLevels === 2 && !empty($item['mpr_approver_2_role_id'])) ? (int) $item['mpr_approver_2_role_id'] : null;

                $existingRules['mpr'] = [
                    'levels'             => $mprLevels,
                    'approver_1_role_id' => $mprApprover1,
                    'approver_2_role_id' => $mprApprover2,
                ];
            }

            $role->update([
                'parent_role_id' => $parentId,
                'approval_rules' => $existingRules,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Skema hierarki dan aturan persetujuan modul berhasil diperbarui!')
            ->with('active_tab', 'tab-hierarchy');
    }
}

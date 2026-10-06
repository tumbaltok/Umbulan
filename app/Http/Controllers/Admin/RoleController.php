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
        $daftarRole = Role::with(['parentRole'])->withCount('users')->get();

        $daftarJobdesk = [];

        return view('admin.daftar.roleindex', compact('daftarRole', 'daftarJobdesk'));
    }

    // Menyimpan data peran (role) baru
    public function store(Request $request)
    {
        if ($request->has('roles')) {
            $request->validate([
                'roles' => 'required|array',
                'roles.*.role_name' => 'required|string|max:255|unique:roles,role_name',
                'roles.*.parent_role_id' => 'nullable|exists:roles,id',
                'roles.*.description' => 'nullable|string',
            ]);

            foreach ($request->roles as $roleData) {
                Role::create([
                    'role_name'      => $roleData['role_name'],
                    'parent_role_id' => !empty($roleData['parent_role_id']) ? $roleData['parent_role_id'] : null,
                    'description'    => $roleData['description'] ?? null,
                ]);
            }
        } else {
            $request->validate([
                'role_name' => 'required|string|max:255|unique:roles,role_name',
                'parent_role_id' => 'nullable|exists:roles,id',
                'description' => 'nullable|string',
            ]);

            Role::create([
                'role_name'      => $request->role_name,
                'parent_role_id' => !empty($request->parent_role_id) ? $request->parent_role_id : null,
                'description'    => $request->description,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Data Role berhasil ditambahkan!')
            ->with('active_tab', 'tab-roles');
    }

    // Memperbarui informasi peran (role), atasan, dan approver modul
    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $id,
            'parent_role_id' => 'nullable|exists:roles,id',
            'description' => 'nullable|string',

            // Validasi Cuti
            'cuti_approval_levels' => 'nullable|integer|in:1,2',
            'cuti_approver_1_role_id' => 'nullable|exists:roles,id',
            'cuti_approver_2_role_id' => 'nullable|exists:roles,id',

            // Validasi MPR
            'mpr_approval_levels' => 'nullable|integer|in:1,2',
            'mpr_approver_1_role_id' => 'nullable|exists:roles,id',
            'mpr_approver_2_role_id' => 'nullable|exists:roles,id',

            // Validasi CAR
            'car_approval_levels' => 'nullable|integer|in:1,2',
            'car_approver_1_role_id' => 'nullable|exists:roles,id',
            'car_approver_2_role_id' => 'nullable|exists:roles,id',
        ]);

        $parentId = (!empty($request->parent_role_id) && $request->parent_role_id != $id)
            ? $request->parent_role_id
            : null;

        $existingRules = $role->approval_rules ?? [];
        if (!is_array($existingRules)) {
            $existingRules = [];
        }

        // 1. MODUL CUTI
        if ($request->has('cuti_approval_levels')) {
            $cutiLevels = (int) ($request->cuti_approval_levels ?? 1);
            $cutiApprover1 = !empty($request->cuti_approver_1_role_id) ? (int) $request->cuti_approver_1_role_id : null;
            $cutiApprover2 = ($cutiLevels === 2 && !empty($request->cuti_approver_2_role_id)) ? (int) $request->cuti_approver_2_role_id : null;

            $existingRules['cuti'] = [
                'levels'             => $cutiLevels,
                'approver_1_role_id' => $cutiApprover1,
                'approver_2_role_id' => $cutiApprover2,
            ];

            // Fallback backward compatibility
            $existingRules['approval_levels'] = $cutiLevels;
            $existingRules['approver_level_1_role_id'] = $cutiApprover1;
            $existingRules['approver_level_2_role_id'] = $cutiApprover2;
        }

        // 2. MODUL MPR
        if ($request->has('mpr_approval_levels')) {
            $mprLevels = (int) ($request->mpr_approval_levels ?? 1);
            $mprApprover1 = !empty($request->mpr_approver_1_role_id) ? (int) $request->mpr_approver_1_role_id : null;
            $mprApprover2 = ($mprLevels === 2 && !empty($request->mpr_approver_2_role_id)) ? (int) $request->mpr_approver_2_role_id : null;

            $existingRules['mpr'] = [
                'levels'             => $mprLevels,
                'approver_1_role_id' => $mprApprover1,
                'approver_2_role_id' => $mprApprover2,
            ];
        }

        // 3. MODUL CAR
        if ($request->has('car_approval_levels')) {
            $carLevels = (int) ($request->car_approval_levels ?? 1);
            $carApprover1 = !empty($request->car_approver_1_role_id) ? (int) $request->car_approver_1_role_id : null;
            $carApprover2 = ($carLevels === 2 && !empty($request->car_approver_2_role_id)) ? (int) $request->car_approver_2_role_id : null;

            $existingRules['car'] = [
                'levels'             => $carLevels,
                'approver_1_role_id' => $carApprover1,
                'approver_2_role_id' => $carApprover2,
            ];
        }

        $role->update([
            'role_name'      => $request->role_name,
            'description'    => $request->description,
            'parent_role_id' => $parentId,
            'approval_rules' => $existingRules,
        ]);

        return redirect()->back()
            ->with('success', 'Data Role, hierarki, dan aturan persetujuan berhasil diperbarui!')
            ->with('active_tab', 'tab-roles');
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

    // Menghapus data peran (role)
    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        // 1. Proteksi role inti sistem
        if (strtoupper($role->role_name) === 'SUPER ADMIN') {
            return redirect()->back()
                ->with('error', 'Role SUPER ADMIN adalah peran inti sistem dan tidak dapat dihapus!')
                ->with('active_tab', 'tab-roles');
        }

        // 2. Proteksi jika masih ada karyawan yang menggunakan role ini
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return redirect()->back()
                ->with('error', "Role '{$role->role_name}' tidak dapat dihapus karena masih digunakan oleh {$usersCount} karyawan. Silakan alihkan jabatan karyawan terlebih dahulu.")
                ->with('active_tab', 'tab-roles');
        }

        // 3. Lepaskan keterkaitan bawahan jika role ini menjadi atasan
        Role::where('parent_role_id', $id)->update(['parent_role_id' => null]);

        // 4. Hapus role
        $roleName = $role->role_name;
        $role->delete();

        return redirect()->back()
            ->with('success', "Role '{$roleName}' berhasil dihapus!")
            ->with('active_tab', 'tab-roles');
    }
}


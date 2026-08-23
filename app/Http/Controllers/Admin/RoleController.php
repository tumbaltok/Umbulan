<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\Jobdesk;
use App\Models\User\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $daftarRole = Role::with(['parentRole', 'childRoles'])->withCount('users')->get();
        $daftarJobdesk = Jobdesk::all();

        return view('admin.daftar.roleindex', compact('daftarRole', 'daftarJobdesk'));
    }

    // Rekalkulasi seluruh tree_code dan level organisasi
    private function rebuildRoleTree()
    {
        $topRoles = Role::whereNull('parent_role_id')->orderBy('id', 'asc')->get();
        $index = 1;

        foreach ($topRoles as $role) {
            $this->assignTreeCodeRecursively($role, (string) $index, 1);
            $index++;
        }
    }

    private function assignTreeCodeRecursively(Role $role, string $codePrefix, int $currentLevel)
    {
        $role->update([
            'tree_code' => $codePrefix,
        ]);

        $childRoles = Role::where('parent_role_id', $role->id)->orderBy('id', 'asc')->get();
        $subIndex = 1;

        foreach ($childRoles as $child) {
            $newPrefix = $codePrefix . '.' . $subIndex;
            $this->assignTreeCodeRecursively($child, $newPrefix, $currentLevel + 1);
            $subIndex++;
        }
    }

    public function store(Request $request)
    {
        if ($request->has('roles')) {
            foreach ($request->roles as $roleData) {
                Role::create($roleData);
            }
        } else {
            Role::create($request->all());
        }

        $this->rebuildRoleTree();

        return redirect()->back()->with('success', 'Data Role berhasil ditambahkan!');
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);
        $data = $request->has('roles') ? ($request->roles[0] ?? []) : $request->all();

        $role->update($data);
        $this->rebuildRoleTree();

        return redirect()->back()->with('success', 'Data Role berhasil diperbarui!');
    }

    public function updateHierarchyMatrix(Request $request)
    {
        $request->validate([
            'hierarchy' => 'required|array',
            'hierarchy.*.role_id' => 'required|exists:roles,id',
            'hierarchy.*.parent_role_id' => 'nullable|exists:roles,id',
            'hierarchy.*.approval_levels' => 'required|integer|in:1,2',
            'hierarchy.*.require_same_station_level_1' => 'nullable|boolean',
            'hierarchy.*.require_same_station_level_2' => 'nullable|boolean',
        ]);

        foreach ($request->hierarchy as $item) {
            $role = Role::findOrFail($item['role_id']);

            $parentId = (!empty($item['parent_role_id']) && $item['parent_role_id'] != $item['role_id'])
                ? $item['parent_role_id']
                : null;

            $approvalLevels = (int) ($item['approval_levels'] ?? 1);

            $approvalRules = [
                'approval_levels'              => $approvalLevels,
                'require_same_station_level_1' => isset($item['require_same_station_level_1']) && $item['require_same_station_level_1'] == 1,
                'require_same_station_level_2' => ($approvalLevels === 2 && isset($item['require_same_station_level_2']) && $item['require_same_station_level_2'] == 1),
            ];

            $role->update([
                'parent_role_id' => $parentId,
                'approval_rules' => $approvalRules,
            ]);
        }

        $this->rebuildRoleTree();

        return redirect()->back()
            ->with('success', 'Skema hirarki, jalur tree, dan aturan persetujuan berhasil diperbarui!')
            ->with('active_tab', 'tab-hierarchy');
    }
}

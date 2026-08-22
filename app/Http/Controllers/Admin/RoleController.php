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
        // Ambil role lengkap dengan atasan langsung & jumlah user
        $daftarRole = Role::with(['parentRole', 'childRoles'])->withCount('users')->get();
        $daftarJobdesk = Jobdesk::all();

        return view('admin.daftar.roleindex', compact('daftarRole', 'daftarJobdesk'));
    }

    public function store(Request $request)
    {
        if ($request->has('roles')) {
            $request->validate([
                'roles.*.role_name' => 'required|string|max:255',
                'roles.*.level' => 'required|integer',
                'roles.*.description' => 'nullable|string',
                'roles.*.parent_role_id' => 'nullable|exists:roles,id',
            ]);

            foreach ($request->roles as $roleData) {
                Role::create($roleData);
            }
        } else {
            $request->validate([
                'role_name' => 'required|string|max:255',
                'level' => 'required|integer',
                'description' => 'nullable|string',
                'parent_role_id' => 'nullable|exists:roles,id',
            ]);

            Role::create($request->all());
        }

        return redirect()->back()->with('success', 'Data Role berhasil ditambahkan!');
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        if ($request->has('roles')) {
            $roleData = $request->roles[0] ?? [];
            $role->update($roleData);
        } else {
            $role->update($request->all());
        }

        return redirect()->back()->with('success', 'Data Role berhasil diperbarui!');
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal menghapus! Role masih digunakan oleh karyawan.');
        }

        // Putus hubungan bawahan jika role ini dihapus
        Role::where('parent_role_id', $id)->update(['parent_role_id' => null]);

        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus!');
    }

    // === METODE BARU: UPDATE SKEMA HIRARKI & ATURAN APPROVAL DINAMIS ===
    public function updateHierarchyMatrix(Request $request)
    {
        $request->validate([
            'hierarchy' => 'required|array',
            'hierarchy.*.role_id' => 'required|exists:roles,id',
            'hierarchy.*.parent_role_id' => 'nullable|exists:roles,id',
            'hierarchy.*.require_same_station' => 'nullable|boolean',
            'hierarchy.*.require_same_sektor' => 'nullable|boolean',
            'hierarchy.*.require_same_jobdesk' => 'nullable|boolean',
            'hierarchy.*.approval_levels' => 'required|integer|in:1,2',
        ]);

        foreach ($request->hierarchy as $item) {
            $role = Role::findOrFail($item['role_id']);

            // Mencegah siklus (Role tidak boleh menjadi parent bagi dirinya sendiri)
            $parentId = (!empty($item['parent_role_id']) && $item['parent_role_id'] != $item['role_id'])
                ? $item['parent_role_id']
                : null;

            $approvalRules = [
                'require_same_station' => isset($item['require_same_station']) && $item['require_same_station'] == 1,
                'require_same_sektor'  => isset($item['require_same_sektor']) && $item['require_same_sektor'] == 1,
                'require_same_jobdesk' => isset($item['require_same_jobdesk']) && $item['require_same_jobdesk'] == 1,
                'approval_levels'      => (int) ($item['approval_levels'] ?? 1),
            ];

            $role->update([
                'parent_role_id' => $parentId,
                'approval_rules' => $approvalRules,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Skema hirarki dan aturan persetujuan berhasil diperbarui!')
            ->with('active_tab', 'tab-hierarchy');
    }

    public function storeJobdesk(Request $request)
    {
        if ($request->has('jobdesks')) {
            $request->validate([
                'jobdesks.*.job_title' => 'required|string|max:255|unique:jobdesks,job_title',
                'jobdesks.*.description' => 'nullable|string',
            ], [
                'jobdesks.*.job_title.required' => 'Nama Jobdesk / Bidang Tugas wajib diisi.',
                'jobdesks.*.job_title.unique' => 'Nama Jobdesk tersebut sudah ada.',
            ]);

            foreach ($request->jobdesks as $jobdeskData) {
                if (! empty($jobdeskData['job_title'])) {
                    Jobdesk::create([
                        'job_title' => $jobdeskData['job_title'],
                        'description' => $jobdeskData['description'] ?? null,
                    ]);
                }
            }
        } else {
            $request->validate([
                'job_title' => 'required|string|max:255|unique:jobdesks,job_title',
                'description' => 'nullable|string',
            ]);

            Jobdesk::create([
                'job_title' => $request->job_title,
                'description' => $request->description,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Kategori Jobdesk berhasil ditambahkan!')
            ->with('active_tab', 'tab-jobdesks');
    }

    public function updateJobdesk(Request $request, int $id)
    {
        $request->validate([
            'job_title' => 'required|string|max:255|unique:jobdesks,job_title,'.$id,
            'description' => 'nullable|string',
        ], [
            'job_title.required' => 'Nama Jobdesk wajib diisi.',
            'job_title.unique' => 'Nama Jobdesk tersebut sudah digunakan.',
        ]);

        $jobdesk = Jobdesk::findOrFail($id);
        $jobdesk->update([
            'job_title' => $request->job_title,
            'description' => $request->description,
        ]);

        return redirect()->back()
            ->with('success', 'Data Jobdesk berhasil diperbarui!')
            ->with('active_tab', 'tab-jobdesks');
    }

    public function destroyJobdesk(int $id)
    {
        $jobdesk = Jobdesk::findOrFail($id);
        $jobdesk->delete();

        return redirect()->back()
            ->with('success', 'Jobdesk berhasil dihapus!')
            ->with('active_tab', 'tab-jobdesks');
    }
}

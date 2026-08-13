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
        $daftarRole = Role::withCount('users')->get();
        $daftarJobdesk = Jobdesk::all();

        return view('admin.daftar.roleindex', compact('daftarRole', 'daftarJobdesk'));
    }

    public function store(Request $request)
    {
        if ($request->has('roles')) {
            $request->validate([
                'roles.*.role_name' => 'required|string|max:255',
                'roles.*.divisi' => 'required|string|max:255',
                'roles.*.level' => 'required|integer',
                'roles.*.description' => 'nullable|string',
            ]);

            foreach ($request->roles as $roleData) {
                Role::create($roleData);
            }
        } else {
            $request->validate([
                'role_name' => 'required|string|max:255',
                'divisi' => 'required|string|max:255',
                'level' => 'required|integer',
                'description' => 'nullable|string',
            ]);

            Role::create($request->all());
        }

        return redirect()->back()->with('success', 'Data Role berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal menghapus! Role masih digunakan oleh karyawan.');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus!');
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

        return redirect()->back()->with('success', 'Kategori Jobdesk berhasil ditambahkan!');
    }

    // === METHOD PERBAIKAN (UPDATE JOBDESK) ===
    public function updateJobdesk(Request $request, $id)
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

        return redirect()->back()->with('success', 'Data Jobdesk berhasil diperbarui!');
    }

    public function destroyJobdesk($id)
    {
        $jobdesk = Jobdesk::findOrFail($id);
        $jobdesk->delete();

        return redirect()->back()->with('success', 'Jobdesk berhasil dihapus!');
    }
}

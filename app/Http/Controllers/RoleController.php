<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $daftarRole = Role::withCount('users')
            ->orderBy('divisi', 'asc')
            ->orderBy('level', 'asc')
            ->get();

        return view('admin.role.index', compact('daftarRole'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_name'   => 'required|string|max:100',
            'divisi'      => 'required|string|max:100',
            'level'       => 'required|integer|in:1,2,3',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create([
            'role_name'   => trim($request->role_name),
            'divisi'      => trim($request->divisi),
            'level'       => (int) $request->level,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Role baru berhasil ditambahkan!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'role_name'   => 'required|string|max:100',
            'divisi'      => 'required|string|max:100',
            'level'       => 'required|integer|in:1,2,3',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::findOrFail($id);
        $role->update([
            'role_name'   => trim($request->role_name),
            'divisi'      => trim($request->divisi),
            'level'       => (int) $request->level,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Data role berhasil diperbarui!');
    }

    public function destroy(int $id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->users_count > 0) {
            return redirect()->back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users_count . ' karyawan!');
        }

        $role->delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus!');
    }
}
<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.manage_master.users.index')->with('sb', 'User');
    }

    public function getall(Request $request)
    {
        $query = User::with('warehouses')->select('id', 'name', 'email', 'role', 'warehouse_id')
                ->orderBy('name', 'ASC')
                ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('warehouse_name', function($u) {
                if ($u->isSuperAdmin()) return 'Semua Cabang';
                return $u->warehouses->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn('action', function (User $user) {
                if (!auth()->user()->canEdit('access_user_management')) {
                    return '<span class="text-muted small">View Only</span>';
                }
                return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="' . url('admin/manage-master/users/edit/' . $user->id) . '" class="dropdown-item">Edit</a></li>
                        <li><a data-id="' . $user->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        if (!auth()->user()->canEdit('access_user_management')) {
            return redirect('admin/manage-master/users')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
        }
        $warehouses  = Warehouse::where('status', 'active')->orderBy('name')->get();
        $permissions = \App\Models\Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.manage_master.users.create', compact('warehouses', 'permissions'))->with('sb', 'User');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canEdit('access_user_management')) {
            return redirect('admin/manage-master/users')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
        }
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:100',
            'email'        => 'required|string|email|max:100|unique:users,email',
            'password'     => 'required|string|min:8',
            'role'         => 'required|in:super_admin,store_manager,finance,admin,sales,branch',
            'warehouse_ids'   => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'warehouse_id' => !empty($request->warehouse_ids) ? $request->warehouse_ids[0] : null,
        ]);

        if ($request->has('warehouse_ids')) {
            $user->warehouses()->sync($request->warehouse_ids);
        }

        if ($request->has('permission_ids')) {
            $user->permissions()->sync($request->permission_ids);
        }

        return redirect('admin/manage-master/users')->with('message', 'Data user berhasil disimpan');
    }

    public function get(Request $request)
    {
        return response()->json(
            User::with(['warehouses', 'permissions'])->findOrFail($request->id),
            200
        );
    }

    public function edit($id)
    {
        if (!auth()->user()->canEdit('access_user_management')) {
            return redirect('admin/manage-master/users')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
        }
        $user = User::with(['warehouses', 'permissions'])->findOrFail($id);
        $warehouses  = Warehouse::where('status', 'active')->orderBy('name')->get();
        $permissions = \App\Models\Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.manage_master.users.edit', compact('user', 'warehouses', 'permissions'))->with('sb', 'User');
    }

    public function update(Request $request)
    {
        if (!auth()->user()->canEdit('access_user_management')) {
            return redirect('admin/manage-master/users')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
        }
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with('error', 'ID user tidak ditemukan');
        }
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:100',
            'email'        => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'password'     => 'nullable|string|min:8',
            'role'         => 'required|in:super_admin,store_manager,finance,admin,sales,branch',
            'warehouse_ids'   => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'name'         => $request->name,
            'email'        => $request->email,
            'role'         => $request->role,
            'warehouse_id' => !empty($request->warehouse_ids) ? $request->warehouse_ids[0] : null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->has('warehouse_ids')) {
            $user->warehouses()->sync($request->warehouse_ids);
        } else {
            $user->warehouses()->detach();
        }

        if ($request->has('permission_ids')) {
            $user->permissions()->sync($request->permission_ids);
        } else {
            $user->permissions()->detach();
        }

        return redirect('admin/manage-master/users')->with('message', 'Data user berhasil diupdate');
    }

    public function delete(Request $request)
    {
        if (!auth()->user()->canEdit('access_user_management')) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk tindakan ini.'], 403);
        }
        $user = User::findOrFail($request->id);
        $user->delete();
        return response()->json(['message' => 'Data user berhasil dihapus'], 200);
    }
}
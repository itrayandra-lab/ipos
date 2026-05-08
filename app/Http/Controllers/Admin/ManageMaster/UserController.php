<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
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
        $query = User::select('id', 'name', 'email', 'role')
                ->orderBy('name', 'ASC')
                ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function (User $user) {
                return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a data-id="' . $user->id . '" class="dropdown-item edit">Edit</a></li>
                        <li><a data-id="' . $user->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string',
            'role' => 'required|in:super_admin,store_manager,finance,admin,sales',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('message', 'Data user berhasil disimpan');
    }

    public function get(Request $request)
    {
        return response()->json(
            User::findOrFail($request->id),
            200
        );
    }

    public function update(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with('error', 'ID user tidak ditemukan');
        }
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:super_admin,store_manager,finance,admin,sales',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('message', 'Data user berhasil diupdate');
    }

    public function delete(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->delete();
        return response()->json(['message' => 'Data user berhasil dihapus'], 200);
    }
}
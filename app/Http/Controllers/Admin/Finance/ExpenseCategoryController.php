<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return view('admin.finance.expense_categories.index')->with('sb', 'ExpenseCategory');
    }

    public function data()
    {
        $query = ExpenseCategory::query();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-info" onclick="editCategory('.$row->id.')">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory('.$row->id.')">Hapus</button>';
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ExpenseCategory::create($request->all());
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        return response()->json(ExpenseCategory::find($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ExpenseCategory::find($id)->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        ExpenseCategory::destroy($id);
        return response()->json(['success' => true]);
    }
}

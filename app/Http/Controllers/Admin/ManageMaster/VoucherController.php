<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('admin.manage_master.voucher.index')->with('products', $products)->with('sb', 'Voucher');
    }

    public function getall(Request $request)
    {
        $query = Voucher::with('products')->orderBy('name', 'ASC')->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('code', function (Voucher $voucher) {
                // Mask code if needed, but for easier management let's show it or masked
                $maskedCode = substr($voucher->code, 0, 2) . str_repeat('*', max(0, strlen($voucher->code) - 2));
                return '<span class="code-display" data-full-code="' . $voucher->code . '">' . $maskedCode . '</span>';
            })
            ->editColumn('percent', function (Voucher $voucher) {
                if ($voucher->discount_type == 'NOMINAL') {
                    return 'Rp ' . number_format($voucher->nominal, 0, ',', '.');
                }
                return $voucher->percent . '%';
            })
            ->addColumn('validity', function (Voucher $voucher) {
                if (!$voucher->start_date && !$voucher->end_date) return '-';
                $start = $voucher->start_date ? $voucher->start_date->format('d M Y') : '∞';
                $end = $voucher->end_date ? $voucher->end_date->format('d M Y') : '∞';
                return $start . ' - ' . $end;
            })
            ->addColumn('usage', function (Voucher $voucher) {
                $limit = $voucher->usage_limit ? $voucher->usage_limit : '∞';
                return $voucher->usage_count . ' / ' . $limit;
            })
            ->addColumn('status', function (Voucher $voucher) {
                $badgeClass = $voucher->status === 'ACTIVE' 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800';
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badgeClass . '">' . $voucher->status . '</span>';
            })
            ->addColumn('action', function (Voucher $voucher) {
                return '
                <div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a data-id="' . $voucher->id . '" class="dropdown-item edit">Edit</a></li>
                        <li><a data-id="' . $voucher->id . '" class="dropdown-item hapus" href="#">Hapus</a></li>
                    </ul>
                </div>
                ';
            })
            ->rawColumns(['code', 'status', 'action'])
            ->make(true);
    }

    public function create_view()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('admin.manage_master.voucher.create')->with('products', $products)->with('sb', 'Voucher');
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:vouchers,code',
            'status' => 'required|in:ACTIVE,NON ACTIVE',
            'discount_type' => 'required|in:PERCENT,NOMINAL',
            'percent' => 'nullable|numeric|min:0|max:100|required_if:discount_type,PERCENT',
            'nominal' => 'nullable|numeric|min:0|required_if:discount_type,NOMINAL',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $voucher = Voucher::create([
            'name' => $request->name,
            'code' => $request->code,
            'discount_type' => $request->discount_type,
            'percent' => $request->percent ?? 0,
            'nominal' => $request->nominal ?? 0,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'usage_limit' => $request->usage_limit,
            'applies_to_all' => empty($request->products),
        ]);

        if (!empty($request->products)) {
            $voucher->products()->sync($request->products);
        }

        return redirect('admin/manage-master/voucher')->with('message', "Data voucher berhasil disimpan");
    }

    public function get(Request $request)
    {
        return response()->json(
            Voucher::with('products')->findOrFail($request->id),
            200
        );
    }

    public function update(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with('error', 'ID voucher tidak ditemukan');
        }

        $voucher = Voucher::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:vouchers,code,' . $id,
            'status' => 'required|in:ACTIVE,NON ACTIVE',
            'discount_type' => 'required|in:PERCENT,NOMINAL',
            'percent' => 'nullable|numeric|min:0|max:100|required_if:discount_type,PERCENT',
            'nominal' => 'nullable|numeric|min:0|required_if:discount_type,NOMINAL',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $voucher->update([
            'name' => $request->name,
            'code' => $request->code,
            'discount_type' => $request->discount_type,
            'percent' => $request->percent ?? 0,
            'nominal' => $request->nominal ?? 0,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'usage_limit' => $request->usage_limit,
            'applies_to_all' => empty($request->products),
        ]);

        $voucher->products()->sync($request->products ?? []);

        return redirect()->back()->with('message', 'Data voucher berhasil diupdate');
    }

    public function delete(Request $request)
    {
        $voucher = Voucher::findOrFail($request->id);
        $voucher->delete();
        return response()->json(['message' => 'Data voucher berhasil dihapus'], 200);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use App\Models\AffiliateProductCommission;

class AffiliateController extends Controller
{
    public function commissions($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        return view('admin.affiliates.commissions', compact('affiliate'))->with('sb', 'Affiliate Users');
    }

    public function commissionsData($id)
    {
        $data = AffiliateProductCommission::with('product')
            ->where('affiliate_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                return '<button class="btn btn-sm btn-danger delete-btn" data-id="'.$row->id.'">Hapus</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'affiliate_id' => 'required|exists:affiliates,id',
            'product_id' => 'required|exists:products,id',
            'fee_method' => 'required|in:percent,nominal',
            'fee_value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        // Check if exists
        $exists = AffiliateProductCommission::where('affiliate_id', $request->affiliate_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Komisi untuk produk ini sudah ada.']);
        }

        AffiliateProductCommission::create($request->all());

        return response()->json(['status' => true, 'message' => 'Komisi produk berhasil disimpan']);
    }

    public function deleteCommission(Request $request)
    {
        $commission = AffiliateProductCommission::find($request->id);
        if ($commission) {
            $commission->delete();
            return response()->json(['status' => true, 'message' => 'Komisi produk berhasil dihapus']);
        }
        return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
    }

    public function getRates($id)
    {
        $rates = AffiliateProductCommission::where('affiliate_id', $id)
            ->get()
            ->keyBy('product_id'); // Key by product_id for easy JS lookup
            
        return response()->json($rates);
    }

    public function show($id)
    {
        $affiliate = Affiliate::with('type')->findOrFail($id);
        
        // Stats
        $totalCommissions = \App\Models\Transaction::where('affiliate_id', $id)->sum('affiliate_fee_total');
        $totalTransactions = \App\Models\Transaction::where('affiliate_id', $id)->count();
        
        // Transaction History
        $transactions = \App\Models\Transaction::where('affiliate_id', $id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.affiliates.show', compact('affiliate', 'totalCommissions', 'totalTransactions', 'transactions'))->with('sb', 'Affiliate Users');
    }

    public function index()
    {
        // Fetch affiliate types from attributes
        $group = AttributeGroup::where('code', 'AFFILIATE_TYPE')->first();
        $types = $group ? $group->attributes : collect([]);
        
        return view('admin.affiliates.index', compact('types'))->with('sb', 'Affiliate Users');
    }

    public function getall()
    {
        $data = Affiliate::with('type')->orderBy('id', 'desc')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('type_name', function($row){
                return $row->type ? $row->type->name : '-';
            })
            ->addColumn('fee_display', function($row){
                if($row->fee_method == 'percent') {
                    return $row->fee_value . '%';
                } else {
                    return 'Rp ' . number_format($row->fee_value);
                }
            })
            ->addColumn('status', function($row){
                return $row->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non Aktif</span>';
            })
            ->addColumn('action', function($row){
                return '
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="'.route('admin.affiliates.show', $row->id).'"><i class="fas fa-eye"></i> Detail</a>
                            <a class="dropdown-item has-icon" href="'.route('admin.affiliates.commissions', $row->id).'"><i class="fas fa-percentage"></i> Set Komisi Produk</a>
                            <a class="dropdown-item has-icon edit" href="#" data-id="'.$row->id.'"><i class="fas fa-edit"></i> Edit</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item has-icon text-danger delete" href="#" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Hapus</a>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:attributes,id',
            'fee_method' => 'required|in:percent,nominal',
            'fee_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        Affiliate::create($request->all());

        return response()->json(['status' => true, 'message' => 'Data Affiliate berhasil disimpan']);
    }

    public function get(Request $request)
    {
        $data = Affiliate::find($request->id);
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:affiliates,id',
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:attributes,id',
            'fee_method' => 'required|in:percent,nominal',
            'fee_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $affiliate = Affiliate::find($request->id);
        $affiliate->update($request->all());

        return response()->json(['status' => true, 'message' => 'Data Affiliate berhasil diupdate']);
    }

    public function delete(Request $request)
    {
        $affiliate = Affiliate::find($request->id);
        if ($affiliate) {
            $affiliate->delete();
            return response()->json(['status' => true, 'message' => 'Data Affiliate berhasil dihapus']);
        }
        return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
    }
}

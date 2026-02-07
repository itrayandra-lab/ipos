<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.customers.index')->with('sb', 'Customers');
    }

    public function getall(Request $request)
    {
        $query = Customer::leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->select(
                'customers.*',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_spending'),
                DB::raw('MAX(transactions.created_at) as last_transaction')
            )
            ->groupBy('customers.id');

        // Apply filters
        if ($request->filter == 'frequent') {
            $query->orderBy('total_transactions', 'desc');
        } elseif ($request->filter == 'newest') {
            $query->orderBy('last_transaction', 'desc');
        } elseif ($request->filter == 'inactive') {
            $query->having('last_transaction', '<', now()->subDays(30));
        } else {
            $query->orderBy('customers.id', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('total_spending_formatted', function($row) {
                return 'Rp ' . number_format($row->total_spending ?? 0);
            })
            ->addColumn('last_transaction_formatted', function($row) {
                return $row->last_transaction ? \Carbon\Carbon::parse($row->last_transaction)->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status', function($row) {
                if (!$row->last_transaction) {
                    return '<span class="badge badge-secondary">Baru</span>';
                }
                
                $lastTrx = \Carbon\Carbon::parse($row->last_transaction);
                $daysSinceLast = now()->diffInDays($lastTrx);
                if ($daysSinceLast > 60) {
                    return '<span class="badge badge-danger">Tidak Aktif</span>';
                }
                
                if ($row->total_transactions > 10) {
                    return '<span class="badge badge-primary">Loyal</span>';
                } elseif ($row->total_transactions > 3) {
                    return '<span class="badge badge-success">Potensial</span>';
                } else {
                    return '<span class="badge badge-info">Aktif</span>';
                }
            })
            ->addColumn('action', function($row) {
                return '
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon edit" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-edit"></i> Edit</a>
                            <a class="dropdown-item has-icon" href="'.route('admin.customers.show', $row->id).'"><i class="fas fa-eye"></i> Detail</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item has-icon text-danger delete" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Hapus</a>
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
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        Customer::create($request->all());

        return response()->json(['success' => true, 'message' => 'Customer berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $customer = Customer::findOrFail($request->id);
        return response()->json($customer);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $request->id,
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $customer = Customer::findOrFail($request->id);
        $customer->update($request->all());

        return response()->json(['success' => true, 'message' => 'Customer berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $customer = Customer::findOrFail($request->id);
        
        // When deleting a customer, we set the customer_id in transactions to null (already handled by onDelete('set null'))
        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Customer berhasil dihapus']);
    }

    public function show($id)
    {
        $customer = Customer::leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->select(
                'customers.*',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_spending'),
                DB::raw('AVG(transactions.total_amount) as avg_transaction'),
                DB::raw('MAX(transactions.created_at) as last_transaction')
            )
            ->where('customers.id', $id)
            ->groupBy('customers.id')
            ->firstOrFail();

        // Transaction History
        $transactions = Transaction::where('customer_id', $id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Favorite Products
        $favoriteProducts = TransactionItem::whereHas('transaction', function($q) use ($id) {
                $q->where('customer_id', $id);
            })
            ->select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_value'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        return view('admin.customers.show', compact('customer', 'transactions', 'favoriteProducts'))->with('sb', 'Customers');
    }

    public function checkAjax(Request $request)
    {
        $customer = Customer::where('phone', $request->phone)->first();
        if ($customer) {
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|max:255',
        ]);

        $customer = Customer::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }
}

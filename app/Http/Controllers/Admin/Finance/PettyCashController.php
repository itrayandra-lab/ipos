<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\PettyCashTransaction;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PettyCashController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('access_petty_cash') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Petty Cash.');
        }

        $latest = PettyCashTransaction::latest()->first();
        $balance = $latest ? $latest->balance_after : 0;

        $monthIn = PettyCashTransaction::where('type', 'in')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $monthOut = PettyCashTransaction::where('type', 'out')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('admin.finance.petty_cash.index', compact('balance', 'monthIn', 'monthOut', 'categories'))
            ->with('sb', 'PettyCash');
    }

    public function data(Request $request)
    {
        $query = PettyCashTransaction::with(['user', 'category'])->orderBy('id', 'desc');

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('type', function ($row) {
                return $row->type == 'in'
                    ? '<span class="badge badge-success">Kas Masuk</span>'
                    : '<span class="badge badge-danger">Kas Keluar</span>';
            })
            ->addColumn('payment_method_label', function ($row) {
                $map = ['cash' => 'Cash', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
                $icons = ['cash' => 'money-bill', 'transfer' => 'university', 'qris' => 'qrcode'];
                $colors = ['cash' => 'secondary', 'transfer' => 'info', 'qris' => 'primary'];
                $pm = $row->payment_method ?? 'cash';
                $label = $map[$pm] ?? $pm;
                $icon  = $icons[$pm] ?? 'money-bill';
                $color = $colors[$pm] ?? 'secondary';
                return '<span class="badge badge-' . $color . '"><i class="fas fa-' . $icon . ' mr-1"></i>' . $label . '</span>';
            })
            ->addColumn('category_name', function ($row) {
                return $row->category ? $row->category->name : '-';
            })
            ->editColumn('transaction_date', function ($row) {
                return $row->transaction_date
                    ? \Carbon\Carbon::parse($row->transaction_date)->format('d-m-Y')
                    : $row->created_at->format('d-m-Y');
            })
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('balance_after', function ($row) {
                return 'Rp ' . number_format($row->balance_after, 0, ',', '.');
            })
            ->addColumn('receipt', function ($row) {
                if ($row->receipt_photo) {
                    return '<a href="' . asset($row->receipt_photo) . '" target="_blank" class="btn btn-xs btn-outline-info" style="font-size:0.75rem; padding:2px 8px; border-radius:4px;">
                        <i class="fas fa-image"></i> Lihat
                    </a>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="dropdown d-inline">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item" onclick="showDetail(' . $row->id . ')">
                            <i class="fas fa-eye mr-2 text-info"></i> Detail
                        </a>
                        <a href="#" class="dropdown-item" onclick="editTransaction(' . $row->id . ')">
                            <i class="fas fa-edit mr-2 text-warning"></i> Edit
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-danger" onclick="deleteTransaction(' . $row->id . ')">
                            <i class="fas fa-trash mr-2"></i> Hapus
                        </a>
                    </div>
                </div>';
            })
            ->rawColumns(['type', 'receipt', 'payment_method_label', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $trx = PettyCashTransaction::with(['user', 'category'])->findOrFail($id);
        $pmMap = ['cash' => 'Cash', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS'];
        $trx->payment_method_label = $pmMap[$trx->payment_method] ?? $trx->payment_method;
        return response()->json(['success' => true, 'data' => $trx]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'                => 'required|in:in,out',
            'payment_method'      => 'required|in:cash,transfer,qris',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'transaction_date'    => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'required|string|max:500',
            'receipt_photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $trx = PettyCashTransaction::findOrFail($id);

                // Recalculate balance: reverse old effect, apply new
                // Get balance before this transaction
                $prevTrx = PettyCashTransaction::where('id', '<', $id)->orderBy('id', 'desc')->first();
                $balanceBefore = $prevTrx ? $prevTrx->balance_after : 0;

                $newBalance = $balanceBefore;
                if ($request->type == 'out' && $request->payment_method == 'cash') {
                    if ($balanceBefore < $request->amount) {
                        throw new \Exception('Saldo kas kecil tidak mencukupi untuk jumlah baru.');
                    }
                    $newBalance = $balanceBefore - $request->amount;
                } elseif ($request->type == 'in') {
                    $newBalance = $balanceBefore + $request->amount;
                }

                $receiptPath = $trx->receipt_photo;
                if ($request->hasFile('receipt_photo')) {
                    if ($receiptPath && file_exists(public_path($receiptPath))) {
                        unlink(public_path($receiptPath));
                    }
                    $file = $request->file('receipt_photo');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/petty_cash'), $filename);
                    $receiptPath = 'uploads/petty_cash/' . $filename;
                }

                $trx->update([
                    'type'                => $request->type,
                    'payment_method'      => $request->payment_method,
                    'expense_category_id' => $request->expense_category_id ?: null,
                    'transaction_date'    => $request->transaction_date,
                    'amount'              => $request->amount,
                    'description'         => $request->description,
                    'receipt_photo'       => $receiptPath,
                    'balance_after'       => $newBalance,
                ]);

                // Recalculate all subsequent transactions' balance_after
                $subsequent = PettyCashTransaction::where('id', '>', $id)->orderBy('id', 'asc')->get();
                $runningBalance = $newBalance;
                foreach ($subsequent as $s) {
                    if ($s->type == 'in') {
                        $runningBalance += $s->amount;
                    } elseif ($s->payment_method == 'cash') {
                        $runningBalance -= $s->amount;
                    }
                    $s->update(['balance_after' => $runningBalance]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $trx = PettyCashTransaction::findOrFail($id);

                if ($trx->receipt_photo && file_exists(public_path($trx->receipt_photo))) {
                    unlink(public_path($trx->receipt_photo));
                }

                $trx->delete();

                // Recalculate all subsequent transactions' balance_after
                $prevTrx = PettyCashTransaction::where('id', '<', $id)->orderBy('id', 'desc')->first();
                $runningBalance = $prevTrx ? $prevTrx->balance_after : 0;

                $subsequent = PettyCashTransaction::where('id', '>', $id)->orderBy('id', 'asc')->get();
                foreach ($subsequent as $s) {
                    if ($s->type == 'in') {
                        $runningBalance += $s->amount;
                    } elseif ($s->payment_method == 'cash') {
                        $runningBalance -= $s->amount;
                    }
                    $s->update(['balance_after' => $runningBalance]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'                => 'required|in:in,out',
            'payment_method'      => 'required|in:cash,transfer,qris',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'transaction_date'    => 'required|date',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'required|string|max:500',
            'receipt_photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $latest = PettyCashTransaction::latest()->first();
                $lastBalance = $latest ? $latest->balance_after : 0;

                // Hanya potong/tambah saldo jika metode Cash
                // Transfer & QRIS = pengeluaran dicatat tapi saldo kas tidak berubah
                $newBalance = $lastBalance;
                if ($request->type == 'out' && $request->payment_method == 'cash') {
                    if ($lastBalance < $request->amount) {
                        throw new \Exception('Saldo kas kecil tidak mencukupi. Saldo saat ini: Rp ' . number_format($lastBalance, 0, ',', '.'));
                    }
                    $newBalance = $lastBalance - $request->amount;
                } elseif ($request->type == 'in') {
                    $newBalance = $lastBalance + $request->amount;
                }

                $receiptPath = null;
                if ($request->hasFile('receipt_photo')) {
                    $file = $request->file('receipt_photo');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/petty_cash'), $filename);
                    $receiptPath = 'uploads/petty_cash/' . $filename;
                }

                PettyCashTransaction::create([
                    'type'                => $request->type,
                    'payment_method'      => $request->payment_method,
                    'expense_category_id' => $request->expense_category_id ?: null,
                    'transaction_date'    => $request->transaction_date,
                    'amount'              => $request->amount,
                    'description'         => $request->description,
                    'receipt_photo'       => $receiptPath,
                    'balance_after'       => $newBalance,
                    'user_id'             => Auth::id(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dicatat.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

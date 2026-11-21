<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PettyCashTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;

class ExpenseController extends Controller
{
    private function canAccess(): bool
    {
        $u = auth()->user();

        return $u->hasPermission('access_expenses') || $u->hasPermission('access_finance_menu');
    }

    private function denied()
    {
        return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses Pengeluaran.'], 403);
    }

    public function index()
    {
        if (!$this->canAccess()) {
            abort(403, 'Anda tidak memiliki akses ke Pengeluaran.');
        }
        $categories = ExpenseCategory::all();
        $bankAccounts = BankAccount::all();
        return view('admin.finance.expenses.index', compact('categories', 'bankAccounts'))->with('sb', 'Expense');
    }

    public function data(Request $request)
    {
        if (!$this->canAccess()) {
            return $this->denied();
        }
        $query = Expense::with(['category', 'user', 'bankAccount'])->orderBy('transaction_date', 'desc');
        
        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('payment_method', function ($row) {
                return $row->payment_method == 'petty_cash' ? 'Kas Kecil' : 'Transfer ('.($row->bankAccount->bank_name ?? '-').')';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-danger" onclick="deleteExpense('.$row->id.')">Hapus</button>';
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        if (!$this->canAccess()) {
            return $this->denied();
        }
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:petty_cash,bank_transfer',
            'bank_account_id' => 'required_if:payment_method,bank_transfer',
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'receipt_photo' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $receiptPath = null;
                if ($request->hasFile('receipt_photo')) {
                    $file = $request->file('receipt_photo');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/expenses'), $filename);
                    $receiptPath = 'uploads/expenses/' . $filename;
                }

                $expense = Expense::create([
                    'expense_category_id' => $request->expense_category_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'bank_account_id' => $request->bank_account_id,
                    'transaction_date' => $request->transaction_date,
                    'description' => $request->description,
                    'receipt_photo' => $receiptPath,
                    'user_id' => Auth::id(),
                ]);

                // If payment is petty cash, deduct from petty cash balance
                if ($request->payment_method == 'petty_cash') {
                    PettyCashTransaction::create([
                        'type' => 'out',
                        'payment_method' => 'cash',
                        'expense_category_id' => $request->expense_category_id,
                        'transaction_date' => $request->transaction_date,
                        'amount' => $request->amount,
                        'description' => 'Pengeluaran: ' . $request->description,
                        'reference_id' => $expense->id,
                        'receipt_photo' => $receiptPath,
                        'balance_after' => 0,
                        'user_id' => Auth::id(),
                    ]);

                    // Saldo dihitung berurutan tanggal; ditolak bila saldo menjadi minus
                    PettyCashTransaction::rebuildBalances();
                }
            });

            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dicatat.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        if (!$this->canAccess()) {
            return $this->denied();
        }
        try {
            DB::transaction(function () use ($id) {
                $expense = Expense::lockForUpdate()->findOrFail($id);

                // Hapus baris petty cash yang terhubung (bukan menambah baris "pembatalan"),
                // lalu hitung ulang saldo. Menghapus pengeluaran hanya menaikkan saldo.
                if ($expense->payment_method == 'petty_cash') {
                    PettyCashTransaction::where('reference_id', $expense->id)
                        ->where('type', 'out')
                        ->delete();
                    PettyCashTransaction::rebuildBalances(true);
                }

                if ($expense->receipt_photo && File::exists(public_path($expense->receipt_photo))) {
                    File::delete(public_path($expense->receipt_photo));
                }

                $expense->delete();
            });
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

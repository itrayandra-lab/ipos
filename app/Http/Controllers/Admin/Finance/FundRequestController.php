<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\FundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class FundRequestController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('access_fund_requests') && !auth()->user()->hasPermission('access_finance')) {
            abort(403, 'Anda tidak memiliki akses ke Pengajuan Dana.');
        }
        return view('admin.finance.fund_requests.index')->with('sb', 'FinanceFundRequest');
    }

    public function getall(Request $request)
    {
        $user = Auth::user();
        $query = FundRequest::with(['user', 'manager', 'finance', 'category'])->orderBy('id', 'desc');

        // Filter based on role
        if ($user->role === 'finance' || $user->role === 'super_admin') {
            // Finance and Super Admin see all
        } elseif ($user->role === 'store_manager') {
            // Store Manager sees all as well? Or only their own?
            // The user said "yang akses ke menu pengajuan dana hanya role finance dan sore_manager"
            // So we allow both to see everything or at least their relevant parts.
        } else {
            // Other roles (like sales or admin) can't see this menu at all based on user request
            // But if they somehow get here, only show their own
            $query->where('user_id', $user->id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('requester', function($row) {
                return $row->user->name;
            })
            ->addColumn('category', function($row) {
                return $row->category
                    ? '<span class="badge badge-light border">' . e($row->category->name) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('status', function($row) {
                $statusMap = [
                    'pending' => ['label' => 'Menunggu Persetujuan', 'class' => 'warning'],
                    'manager_approved' => ['label' => 'Disetujui Manager', 'class' => 'info'],
                    'manager_rejected' => ['label' => 'Ditolak Manager', 'class' => 'danger'],
                    'finance_approved' => ['label' => 'Disetujui Finance', 'class' => 'success'],
                    'finance_rejected' => ['label' => 'Ditolak Finance', 'class' => 'danger'],
                    'disbursed' => ['label' => 'Dana Cair', 'class' => 'dark'],
                ];
                $s = $statusMap[$row->status] ?? ['label' => $row->status, 'class' => 'secondary'];
                return '<span class="badge badge-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->addColumn('action', function($row) {
                $user = Auth::user();
                $btn = '<div class="dropdown d-inline">
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">';

                $btn .= '<a href="' . route('admin.finance.fund_requests.show', $row->id) . '" class="dropdown-item"><i class="fas fa-eye mr-2 text-primary"></i> Lihat Detail</a>';

                if ($row->status === 'pending') {
                    if ($row->user_id === $user->id || $user->role === 'super_admin') {
                        $btn .= '<a href="' . route('admin.finance.fund_requests.edit', $row->id) . '" class="dropdown-item"><i class="fas fa-edit mr-2 text-warning"></i> Edit</a>';
                        $btn .= '<div class="dropdown-divider"></div>';
                        $btn .= '<a href="#" onclick="deleteRequest(' . $row->id . '); return false;" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i> Hapus</a>';
                    }
                }

                $btn .= '</div></div>';
                return $btn;
            })
            ->rawColumns(['status', 'action', 'category'])
            ->make(true);
    }

    public function create()
    {
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();
        return view('admin.finance.fund_requests.create', compact('categories'))->with('sb', 'FinanceFundRequest');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'               => 'required|string|max:255',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'required|string',
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name'   => 'required|string|max:100',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = $request->all();
        $data['request_code'] = FundRequest::generateCode();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/fund_requests'), $filename);
            $data['attachment'] = 'uploads/fund_requests/' . $filename;
        }

        FundRequest::create($data);

        return redirect()->route('admin.finance.fund_requests.index')->with('message', 'Pengajuan dana berhasil dibuat');
    }

    public function show($id)
    {
        $fundRequest = FundRequest::with(['user', 'manager', 'finance', 'category'])->findOrFail($id);
        return view('admin.finance.fund_requests.show', compact('fundRequest'))->with('sb', 'FinanceFundRequest');
    }

    public function approveManager(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        
        if (Auth::user()->role !== 'store_manager' && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Store Manager yang dapat menyetujui pengajuan ini');
        }

        $fundRequest->update([
            'status' => 'manager_approved',
            'manager_id' => Auth::id(),
            'manager_approved_at' => Carbon::now(),
            'manager_notes' => $request->notes
        ]);

        return redirect()->back()->with('message', 'Pengajuan disetujui oleh Manager');
    }

    public function rejectManager(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        
        if (Auth::user()->role !== 'store_manager' && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Store Manager yang dapat menolak pengajuan ini');
        }

        $fundRequest->update([
            'status' => 'manager_rejected',
            'manager_id' => Auth::id(),
            'manager_approved_at' => Carbon::now(),
            'manager_notes' => $request->notes
        ]);

        return redirect()->back()->with('message', 'Pengajuan ditolak oleh Manager');
    }

    public function approveFinance(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        
        if (Auth::user()->role !== 'finance' && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Finance yang dapat menyetujui pengajuan ini');
        }

        $fundRequest->update([
            'status' => 'finance_approved',
            'finance_id' => Auth::id(),
            'finance_approved_at' => Carbon::now(),
            'finance_notes' => $request->notes
        ]);

        return redirect()->back()->with('message', 'Pengajuan disetujui oleh Finance');
    }

    public function rejectFinance(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        
        if (Auth::user()->role !== 'finance' && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Finance yang dapat menolak pengajuan ini');
        }

        $fundRequest->update([
            'status' => 'finance_rejected',
            'finance_id' => Auth::id(),
            'finance_approved_at' => Carbon::now(),
            'finance_notes' => $request->notes
        ]);

        return redirect()->back()->with('message', 'Pengajuan ditolak oleh Finance');
    }

    public function markAsDisbursed(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        
        if (Auth::user()->role !== 'finance' && Auth::user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Finance yang dapat menandai pencairan dana');
        }

        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = ['status' => 'disbursed'];

        if ($request->hasFile('transfer_proof')) {
            $file = $request->file('transfer_proof');
            $filename = time() . '_transfer_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/fund_requests/proofs'), $filename);
            $data['transfer_proof'] = 'uploads/fund_requests/proofs/' . $filename;
        }

        $fundRequest->update($data);

        return redirect()->back()->with('message', 'Dana telah ditandai sebagai Dicairkan dengan bukti transfer');
    }

    public function edit($id)
    {
        $fundRequest = FundRequest::findOrFail($id);
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();

        if ($fundRequest->status !== 'pending' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.finance.fund_requests.index')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah');
        }

        return view('admin.finance.fund_requests.edit', compact('fundRequest', 'categories'))->with('sb', 'FinanceFundRequest');
    }

    public function update(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);

        if ($fundRequest->status !== 'pending' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.finance.fund_requests.index')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah');
        }

        $request->validate([
            'title'               => 'required|string|max:255',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|min:1',
            'description'         => 'required|string',
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name'   => 'required|string|max:100',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = $request->only(['title', 'expense_category_id', 'amount', 'description', 'bank_name', 'bank_account_number', 'bank_account_name']);

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($fundRequest->attachment && file_exists(public_path($fundRequest->attachment))) {
                unlink(public_path($fundRequest->attachment));
            }

            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/fund_requests'), $filename);
            $data['attachment'] = 'uploads/fund_requests/' . $filename;
        }

        $fundRequest->update($data);

        return redirect()->route('admin.finance.fund_requests.index')->with('message', 'Pengajuan dana berhasil diperbarui');
    }

    public function destroy($id)
    {
        $fundRequest = FundRequest::findOrFail($id);

        if ($fundRequest->status !== 'pending' && Auth::user()->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus'], 403);
        }

        if ($fundRequest->attachment && file_exists(public_path($fundRequest->attachment))) {
            unlink(public_path($fundRequest->attachment));
        }

        $fundRequest->delete();

        return response()->json(['success' => true, 'message' => 'Pengajuan dana berhasil dihapus']);
    }
}

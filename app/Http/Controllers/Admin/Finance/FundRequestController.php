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
        return view('admin.finance.fund_requests.index')->with('sb', 'FinanceFundRequest');
    }

    public function getall(Request $request)
    {
        $user = Auth::user();
        $query = FundRequest::with(['user', 'manager', 'finance'])->orderBy('id', 'desc');

        // Filter based on role
        if ($user->role === 'sales' || $user->role === 'admin') {
            // Can only see their own requests
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'store_manager') {
            // Can see all but usually relevant for approval
            // For simplicity, manager sees all but action buttons differ
        } elseif ($user->role === 'finance') {
            // Finance sees all
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('requester', function($row) {
                return $row->user->name;
            })
            ->editColumn('amount', function($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('status', function($row) {
                $statusMap = [
                    'pending' => ['label' => 'Pending Manager', 'class' => 'warning'],
                    'manager_approved' => ['label' => 'Pending Finance', 'class' => 'info'],
                    'manager_rejected' => ['label' => 'Ditolak Manager', 'class' => 'danger'],
                    'finance_approved' => ['label' => 'Disetujui Finance', 'class' => 'success'],
                    'finance_rejected' => ['label' => 'Ditolak Finance', 'class' => 'danger'],
                    'disbursed' => ['label' => 'Sudah Cair', 'class' => 'dark'],
                ];
                $s = $statusMap[$row->status] ?? ['label' => $row->status, 'class' => 'secondary'];
                return '<span class="badge badge-' . $s['class'] . '">' . $s['label'] . '</span>';
            })
            ->addColumn('action', function($row) {
                $user = Auth::user();
                $btn = '<a href="' . route('admin.finance.fund_requests.show', $row->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a> ';
                
                // Only allow edit/delete if status is pending
                if ($row->status === 'pending') {
                    // Only owner or super_admin can edit/delete
                    if ($row->user_id === $user->id || $user->role === 'super_admin') {
                        $btn .= '<a href="' . route('admin.finance.fund_requests.edit', $row->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a> ';
                        $btn .= '<button type="button" onclick="deleteRequest(' . $row->id . ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    }
                }
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.finance.fund_requests.create')->with('sb', 'FinanceFundRequest');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
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
        $fundRequest = FundRequest::with(['user', 'manager', 'finance'])->findOrFail($id);
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

        $fundRequest->update([
            'status' => 'disbursed'
        ]);

        return redirect()->back()->with('message', 'Dana telah ditandai sebagai Dicairkan');
    }

    public function edit($id)
    {
        $fundRequest = FundRequest::findOrFail($id);

        if ($fundRequest->status !== 'pending' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.finance.fund_requests.index')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah');
        }

        return view('admin.finance.fund_requests.edit', compact('fundRequest'))->with('sb', 'FinanceFundRequest');
    }

    public function update(Request $request, $id)
    {
        $fundRequest = FundRequest::findOrFail($id);

        if ($fundRequest->status !== 'pending' && Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.finance.fund_requests.index')->with('error', 'Pengajuan yang sudah diproses tidak dapat diubah');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = $request->only(['title', 'amount', 'description']);

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

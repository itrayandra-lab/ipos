<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderApproval;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class PurchaseOrderApprovalController extends Controller
{
    public function show($token)
    {
        $approval = PurchaseOrderApproval::with(['purchaseOrder.supplier', 'purchaseOrder.items', 'user'])
            ->where('token', $token)
            ->firstOrFail();

        $po = $approval->purchaseOrder;
        $storeSetting = StoreSetting::find(1);

        return view('purchase-order-approval', compact('approval', 'po', 'storeSetting'));
    }

    public function approve($token)
    {
        $approval = PurchaseOrderApproval::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->route('purchase-order.approval.show', $token)
            ->with('success', 'Anda telah menyetujui Purchase Order ini.');
    }

    public function reject(Request $request, $token)
    {
        $request->validate(['rejected_reason' => 'required|string|max:500']);

        $approval = PurchaseOrderApproval::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $approval->update([
            'status' => 'rejected',
            'rejected_reason' => $request->rejected_reason,
            'approved_at' => now(),
        ]);

        return redirect()->route('purchase-order.approval.show', $token)
            ->with('success', 'Anda telah menolak Purchase Order ini.');
    }
}

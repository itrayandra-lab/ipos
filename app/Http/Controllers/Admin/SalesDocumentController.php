<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Models\StoreSetting;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\TransactionPayment;

class SalesDocumentController extends Controller
{
    public function invoices()
    {
        return view('admin.sales.invoice.index')->with('sb', 'SalesInvoices');
    }

    public function getInvoices(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaction::with(['user', 'customer', 'payments'])
                ->whereNotNull('invoice_number')
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y H:i'))
                ->editColumn('total_amount', fn($row) => 'Rp ' . number_format($row->total_amount, 0, ',', '.'))
                ->editColumn('payment_status', function ($row) {
                    $totalPaid = $row->payments()->sum('amount');
                    $labels = [
                        'paid'     => '<span class="badge badge-success">Lunas</span>',
                        'unpaid'   => '<span class="badge badge-warning">Belum Bayar</span>',
                        'credit'   => $totalPaid > 0 
                                        ? '<span class="badge badge-info text-dark">DP Terbayar</span>' 
                                        : '<span class="badge badge-warning">Menunggu DP</span>',
                        'pending'  => '<span class="badge badge-warning">Pending</span>',
                        'draft'    => '<span class="badge badge-secondary">Draft</span>',
                        'canceled' => '<span class="badge badge-danger">Batal</span>',
                    ];
                    return $labels[$row->payment_status] ?? strtoupper($row->payment_status);
                })
                ->addColumn('action', function ($row) {
                    $show  = '<a href="' . route('admin.sales.invoices.show', $row->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                    $edit  = '<a href="' . route('admin.sales.invoices.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $print = '<a href="' . route('admin.sales.invoices.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Cetak"><i class="fas fa-print"></i></a>';
                    $del   = '<button onclick="deleteInvoice(' . $row->id . ')" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>';
                    return $show . ' ' . $edit . ' ' . $print . ' ' . $del;
                })
                ->rawColumns(['payment_status', 'action'])
                ->make(true);
        }
    }

    public function createInvoice()
    {
        $customers = Customer::orderBy('name')->get();
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        
        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get()
            ->sortBy(fn($batch) => ($batch->product->merek->name ?? '') . ' ' . ($batch->product->name ?? ''));

        $batchList = [];
        foreach ($batches as $batch) {
            $product     = $batch->product;
            $merekName   = ($product && $product->merek) ? trim($product->merek->name) : '';
            $productName = trim($product->name ?? '');
            $variantName = $batch->variant ? trim($batch->variant->variant_name) : '';
            
            $originalParts = array_filter([$merekName, $productName, $variantName]);
            $finalParts = [];
            foreach ($originalParts as $p1) {
                $isSubPart = false;
                foreach ($originalParts as $p2) {
                    if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                        $isSubPart = true;
                        break;
                    }
                }
                if (!$isSubPart) {
                    $finalParts[] = $p1;
                }
            }
            $labelText = implode(' ', array_unique($finalParts));
            $batchList[] = [
                'id'        => $batch->id,
                'text'      => $labelText . ' (' . $batch->batch_no . ' - ' . $batch->qty . ')',
                'price'     => $batch->variant->price ?? ($product->price_real > 0 ? $product->price_real : $product->price),
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
            ];
        }

        $nextInvoiceNumber = InvoiceService::generate();
        return view('admin.sales.invoice.create', compact('customers', 'batchList', 'nextInvoiceNumber', 'bankAccounts'))
            ->with('sb', 'SalesInvoices');
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:50',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'transaction_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled,credit',
            'transaction_type' => 'required|in:produk,kelas',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'down_payment_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $transaction = DB::transaction(function () use ($request) {
                $totalAmount   = 0;
                $itemsToCreate = [];
                foreach ($request->items as $item) {
                    $batch    = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $product  = $batch->product;
                    $qty      = (int) $item['qty'];
                    $price    = (float) $item['price'];
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;
                    $itemsToCreate[] = [
                        'product_id'       => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price'        => $batch->buy_price ?? 0,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                    ];
                    if (in_array($request->payment_status, ['paid', 'credit'])) {
                        if ($batch->qty < $qty) {
                            throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$product->name} tidak mencukupi (tersisa {$batch->qty}).");
                        }
                        $batch->decrement('qty', $qty);
                        $product->decrement('stock', $qty);
                    }
                }
                $taxAmount    = (float) ($request->tax_amount ?? 0);
                $discountVal  = (float) ($request->discount ?? 0);
                $taxType      = $request->tax_type ?? 'none';
                $discountType = $request->discount_type ?? 'fixed';
                $grandTotal   = ($totalAmount + $taxAmount) - $discountVal;
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : Carbon::now();
                $transaction = Transaction::create([
                    'user_id'          => auth()->id(),
                    'customer_id'      => $request->customer_id,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'customer_address' => $request->customer_address,
                    'bank_account_id'  => $request->bank_account_id,
                    'source'           => 'manual-invoice',
                    'transaction_type' => $request->transaction_type,
                    'is_dp'            => $request->payment_status === 'credit',
                    'down_payment'     => $request->down_payment_amount ?? 0,
                    'notes'            => $request->notes,
                    'total_amount'     => $grandTotal,
                    'discount'         => $discountVal,
                    'discount_type'    => $discountType,
                    'tax_type'         => $taxType,
                    'tax_amount'       => $taxAmount,
                    'payment_status'   => $request->payment_status,
                    'payment_method'   => $request->payment_method,
                    'delivery_type'    => 'pickup',
                    'created_at'       => $txDate,
                    'due_date'         => $request->due_date,
                ]);
                $invNum = $request->invoice_number ?: InvoiceService::generate($txDate);
                $transaction->update(['invoice_number' => $invNum]);
                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);
                }
                return $transaction;
            });
            return redirect()->route('admin.sales.invoices.show', $transaction->id)
                ->with('message', 'Invoice berhasil dibuat: ' . $transaction->invoice_number);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showInvoice($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product.merek', 'items.batch.variant', 'payments', 'bankAccount'])
            ->findOrFail($id);
        $setting = StoreSetting::getActiveSetting();
        return view('admin.sales.invoice.show', compact('transaction', 'setting'))->with('sb', 'SalesInvoices');
    }

    public function editInvoice($id)
    {
        $transaction = Transaction::with(['customer', 'items.product.merek', 'items.batch.variant'])
            ->findOrFail($id);
        
        $customers = Customer::orderBy('name')->get();
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        
        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get()
            ->sortBy(fn($batch) => ($batch->product->merek->name ?? '') . ' ' . ($batch->product->name ?? ''));

        $batchList = [];
        foreach ($batches as $batch) {
            $product     = $batch->product;
            $merekName   = ($product && $product->merek) ? trim($product->merek->name) : '';
            $productName = trim($product->name ?? '');
            $variantName = $batch->variant ? trim($batch->variant->variant_name) : '';
            
            $originalParts = array_filter([$merekName, $productName, $variantName]);
            $finalParts = [];
            foreach ($originalParts as $p1) {
                $isSubPart = false;
                foreach ($originalParts as $p2) {
                    if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                        $isSubPart = true;
                        break;
                    }
                }
                if (!$isSubPart) {
                    $finalParts[] = $p1;
                }
            }
            $labelText = implode(' ', array_unique($finalParts));
            $batchList[] = [
                'id'        => $batch->id,
                'text'      => $labelText . ' (' . $batch->batch_no . ' - ' . $batch->qty . ')',
                'price'     => $batch->variant->price ?? ($product->price_real > 0 ? $product->price_real : $product->price),
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
            ];
        }

        return view('admin.sales.invoice.edit', compact('transaction', 'customers', 'batchList', 'bankAccounts'))
            ->with('sb', 'SalesInvoices');
    }

    public function updateInvoice(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'transaction_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction, $id) {
                $totalAmount   = 0;
                $itemsToCreate = [];
                
                foreach ($request->items as $item) {
                    $batch    = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $product  = $batch->product;
                    $qty      = (int) $item['qty'];
                    $price    = (float) $item['price'];
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;
                    $itemsToCreate[] = [
                        'product_id'       => $product->id,
                        'product_batch_id' => $batch->id,
                        'buy_price'        => $batch->buy_price ?? 0,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                    ];
                }
                
                $taxAmount    = (float) ($request->tax_amount ?? 0);
                $discountVal  = (float) ($request->discount ?? 0);
                $taxType      = $request->tax_type ?? 'none';
                $discountType = $request->discount_type ?? 'fixed';
                $grandTotal   = ($totalAmount + $taxAmount) - $discountVal;
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : $transaction->created_at;
                
                $transaction->update([
                    'customer_id'      => $request->customer_id,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'customer_address' => $request->customer_address,
                    'bank_account_id'  => $request->bank_account_id,
                    'notes'            => $request->notes,
                    'total_amount'     => $grandTotal,
                    'discount'         => $discountVal,
                    'discount_type'    => $discountType,
                    'tax_type'         => $taxType,
                    'tax_amount'       => $taxAmount,
                    'payment_status'   => $request->payment_status,
                    'payment_method'   => $request->payment_method,
                    'due_date'         => $request->due_date,
                    'created_at'       => $txDate,
                ]);
                
                // Hapus items lama
                TransactionItem::where('transaction_id', $id)->delete();
                
                // Buat items baru
                foreach ($itemsToCreate as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);
                }
            });
            
            return redirect()->route('admin.sales.invoices.show', $transaction->id)
                ->with('message', 'Invoice berhasil diperbarui: ' . $transaction->invoice_number);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function printInvoice($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product.merek', 'items.batch.variant', 'payments', 'bankAccount'])
            ->findOrFail($id);
        $setting = StoreSetting::getActiveSetting();
        return view('admin.sales.invoice.print', compact('transaction', 'setting'));
    }

    public function destroyInvoice($id)
    {
        $transaction = Transaction::with('items')->findOrFail($id);
        try {
            DB::transaction(function () use ($transaction) {
                if (in_array($transaction->payment_status, ['paid', 'credit'])) {
                    foreach ($transaction->items as $item) {
                        if ($item->product_batch_id) {
                            $batch = ProductBatch::find($item->product_batch_id);
                            if ($batch) {
                                $batch->increment('qty', $item->qty);
                                $batch->product->increment('stock', $item->qty);
                            }
                        }
                    }
                }
                $transaction->items()->delete();
                $transaction->delete();
            });
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function uploadReceipt(Request $request, $id)
    {
        try {
            $request->validate([
                'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'amount'       => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'notes'        => 'nullable|string',
            ]);
            $transaction = Transaction::findOrFail($id);
            $dbPath = null;
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $dbPath = 'uploads/receipts/' . $filename;
            }
            $payment = TransactionPayment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $request->amount,
                'payment_date'   => $request->payment_date,
                'payment_method' => $transaction->payment_method ?: 'manual', 
                'payment_receipt' => $dbPath,
                'notes'          => $request->notes,
            ]);
            $totalPaid = $transaction->payments()->sum('amount');
            if ($totalPaid >= $transaction->total_amount) {
                $transaction->update(['payment_status' => 'paid']);
            }
            return redirect()->back()->with('message', 'Pembayaran berhasil dicatat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function settlePayment(Request $request, $id)
    {
        $transaction = Transaction::with('items.batch.product')->findOrFail($id);
        if ($transaction->payment_status === 'paid') {
            return redirect()->back()->with('info', 'Invoice sudah lunas.');
        }
        try {
            DB::transaction(function () use ($transaction, $request) {
                if (!in_array($transaction->payment_status, ['paid', 'credit'])) {
                    foreach ($transaction->items as $item) {
                        if ($item->product_batch_id) {
                            $batch = $item->batch;
                            if ($batch) {
                                if ($batch->qty < $item->qty) {
                                    throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$item->product->name} tidak mencukupi (tersisa {$batch->qty}).");
                                }
                                $batch->decrement('qty', $item->qty);
                                $item->product->decrement('stock', $item->qty);
                            }
                        }
                    }
                }
                $paidSoFar = $transaction->payments()->sum('amount');
                $balance = $transaction->total_amount - $paidSoFar;
                $dbPath = null;
                if ($request->hasFile('receipt')) {
                    $file = $request->file('receipt');
                    $filename = time() . '_settle_' . $file->getClientOriginalName();
                    $fullPath = public_path('uploads/receipts');
                    if (!File::isDirectory($fullPath)) {
                        File::makeDirectory($fullPath, 0777, true, true);
                    }
                    $file->move($fullPath, $filename);
                    $dbPath = 'uploads/receipts/' . $filename;
                }
                TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount'         => $balance,
                    'payment_date'   => Carbon::now(),
                    'payment_method' => $request->payment_method ?? $transaction->payment_method,
                    'payment_receipt' => $dbPath,
                    'notes'          => 'Pelunasan',
                ]);
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $request->payment_method ?? $transaction->payment_method,
                    'is_dp' => false,
                ]);
            });
            return redirect()->back()->with('message', 'Invoice #' . $transaction->invoice_number . ' telah dilunasi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melunasi invoice: ' . $e->getMessage());
        }
    }

    public function receipts()
    {
        return view('admin.sales.receipt.index')->with('sb', 'SalesReceipts');
    }

    public function getReceipts(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaction::with(['user', 'customer', 'payments'])
                ->where('payment_status', 'paid')
                ->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y H:i'))
                ->editColumn('total_amount', fn($row) => 'Rp ' . number_format($row->total_amount, 0, ',', '.'))
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('admin.sales.receipts.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Cetak Kwitansi"><i class="fas fa-print"></i></a>';
                })
                ->make(true);
        }
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'payments'])->findOrFail($id);
        $setting = StoreSetting::getActiveSetting();
        return view('admin.sales.receipt.print', compact('transaction', 'setting'));
    }

    public function deliveryNotes()
    {
        return view('admin.sales.delivery_note.index')->with('sb', 'SalesDeliveryNotes');
    }

    public function getDeliveryNotes(Request $request)
        {
            if ($request->ajax()) {
                $data = \App\Models\DeliveryNote::with(['customer'])
                    ->latest();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('delivery_note_no', function($row) {
                        return $row->delivery_note_no ?? '-';
                    })
                    ->addColumn('customer_name', function($row) {
                        return $row->customer ? $row->customer->name : ($row->customer_name ?? '-');
                    })
                    ->addColumn('transaction_date', function($row) {
                        return \Carbon\Carbon::parse($row->transaction_date)->format('d/m/Y');
                    })
                    ->addColumn('action', function ($row) {
                        $printBtn = '<a href="' . route('admin.sales.delivery_notes.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Cetak Surat Jalan"><i class="fas fa-print"></i></a>';
                        $editBtn = '<a href="' . route('admin.sales.delivery_notes.edit', $row->id) . '" class="btn btn-sm btn-warning ml-1" title="Edit"><i class="fas fa-edit"></i></a>';
                        $deleteBtn = '<button type="button" onclick="deleteDeliveryNote(' . $row->id . ')" class="btn btn-sm btn-danger ml-1" title="Hapus"><i class="fas fa-trash"></i></button>';
                        return $printBtn . ' ' . $editBtn . ' ' . $deleteBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }


    public function createDeliveryNote()
    {
        $customers = Customer::orderBy('name')->get();
        
        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get();

        $batchList = [];
        foreach ($batches as $batch) {
            $product     = $batch->product;
            $merekName   = ($product && $product->merek) ? trim($product->merek->name) : '';
            $productName = trim($product->name ?? '');
            $variantName = $batch->variant ? trim($batch->variant->variant_name) : '';
            
            $labelText = implode(' ', array_filter([$merekName, $productName, $variantName]));
            $batchList[] = [
                'id'        => $batch->id,
                'text'      => $labelText . ' (' . $batch->batch_no . ' - ' . $batch->qty . ')',
                'stock'     => $batch->qty,
            ];
        }

        // Get Netto attribute group and its attributes
        $nettoGroup = \App\Models\AttributeGroup::where('code', 'netto')->orWhere('name', 'Netto')->first();
        $nettoAttributes = $nettoGroup ? $nettoGroup->attributes()->orderBy('name')->get() : collect();

        return view('admin.sales.delivery_note.create', compact('customers', 'batchList', 'nettoAttributes'))
            ->with('sb', 'SalesDeliveryNotes');
    }

    public function storeDeliveryNote(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'delivery_address' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
            'delivery_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : Carbon::now();
                
                // Generate nomor surat jalan: SJ/BL/[RomanMonth]/[YY]/[MonthlySequence]
                $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                $romanMonth = $romanMonths[$txDate->month - 1];
                $year = $txDate->format('y'); // 2 digit year
                
                $lastDN = \App\Models\DeliveryNote::whereYear('transaction_date', $txDate->year)
                    ->whereMonth('transaction_date', $txDate->month)
                    ->orderBy('id', 'desc')
                    ->first();
                
                $lastNumber = 0;
                if ($lastDN && $lastDN->delivery_note_no) {
                    // Extract last number from format SJ/BL/II/26/0001
                    $parts = explode('/', $lastDN->delivery_note_no);
                    if (count($parts) === 5) {
                        $lastNumber = (int) $parts[4];
                    }
                }
                
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $deliveryNoteNo = "SJ/BL/{$romanMonth}/{$year}/{$newNumber}";
                
                // Simpan ke tabel delivery_notes
                $deliveryNote = \App\Models\DeliveryNote::create([
                    'user_id'            => auth()->id(),
                    'customer_id'        => $request->customer_id,
                    'customer_name'      => $request->customer_name,
                    'customer_phone'     => $request->customer_phone,
                    'delivery_address'   => $request->delivery_address,
                    'delivery_note_no'   => $deliveryNoteNo,
                    'transaction_date'   => $txDate,
                    'delivery_type'      => $request->delivery_type,
                    'notes'              => $request->notes,
                ]);

                // Simpan items ke tabel delivery_note_items (tanpa mengurangi stok)
                foreach ($request->items as $item) {
                    $batch = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $qty = (int) $item['qty'];

                    \App\Models\DeliveryNoteItem::create([
                        'delivery_note_id' => $deliveryNote->id,
                        'product_id'       => $batch->product_id,
                        'product_batch_id' => $batch->id,
                        'qty'              => $qty,
                        'description'      => $item['description'] ?? null,
                        'satuan'           => $item['satuan'] ?? null,
                    ]);
                }
            });

            return redirect()->route('admin.sales.delivery_notes.index')
                ->with('message', 'Surat Jalan manual berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function printDeliveryNote($id)
    {
        $deliveryNote = \App\Models\DeliveryNote::with(['user', 'customer', 'items.product.merek', 'items.batch.variant.netto'])->findOrFail($id);
        $storeSetting = StoreSetting::find(1);
        return view('admin.sales.delivery_note.print', compact('deliveryNote', 'storeSetting'));
    }

    public function destroyDeliveryNote($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $deliveryNote = \App\Models\DeliveryNote::with('items.batch.product')->findOrFail($id);
                
                // Kembalikan stok
                foreach ($deliveryNote->items as $item) {
                    if ($item->batch) {
                        $item->batch->increment('qty', $item->qty);
                    }
                    if ($item->product) {
                        $item->product->increment('stock', $item->qty);
                    }
                }
                
                // Hapus items dan delivery note
                $deliveryNote->items()->delete();
                $deliveryNote->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Surat Jalan berhasil dihapus dan stok dikembalikan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editDeliveryNote($id)
    {
        $deliveryNote = \App\Models\DeliveryNote::with(['customer', 'items.product.merek', 'items.batch.variant'])
            ->findOrFail($id);
        
        $customers = Customer::orderBy('name')->get();
        
        $batches = ProductBatch::with(['product.merek', 'variant'])
            ->where('qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('status', 'Y'))
            ->get()
            ->sortBy(fn($batch) => ($batch->product->merek->name ?? '') . ' ' . ($batch->product->name ?? ''));

        $batchList = [];
        foreach ($batches as $batch) {
            $product     = $batch->product;
            $merekName   = ($product && $product->merek) ? trim($product->merek->name) : '';
            $productName = trim($product->name ?? '');
            $variantName = $batch->variant ? trim($batch->variant->variant_name) : '';
            
            $originalParts = array_filter([$merekName, $productName, $variantName]);
            $finalParts = [];
            foreach ($originalParts as $p1) {
                $isSubPart = false;
                foreach ($originalParts as $p2) {
                    if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                        $isSubPart = true;
                        break;
                    }
                }
                if (!$isSubPart) {
                    $finalParts[] = $p1;
                }
            }
            $labelText = implode(' ', array_unique($finalParts));
            $batchList[] = [
                'id'        => $batch->id,
                'text'      => $labelText . ' (' . $batch->batch_no . ' - ' . $batch->qty . ')',
                'price'     => $batch->variant->price ?? ($product->price_real > 0 ? $product->price_real : $product->price),
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
            ];
        }

        // Get Netto attribute group and its attributes
        $nettoGroup = \App\Models\AttributeGroup::where('code', 'netto')->orWhere('name', 'Netto')->first();
        $nettoAttributes = $nettoGroup ? $nettoGroup->attributes()->orderBy('name')->get() : collect();

        return view('admin.sales.delivery_note.edit', compact('deliveryNote', 'customers', 'batchList', 'nettoAttributes'))
            ->with('sb', 'SalesDeliveryNotes');
    }

    public function updateDeliveryNote(Request $request, $id)
    {
        $deliveryNote = \App\Models\DeliveryNote::findOrFail($id);
        
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'delivery_address' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
            'delivery_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request, $deliveryNote, $id) {
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : $deliveryNote->transaction_date;
                
                $deliveryNote->update([
                    'customer_id'      => $request->customer_id,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'delivery_address' => $request->delivery_address,
                    'transaction_date' => $txDate,
                    'delivery_type'    => $request->delivery_type,
                    'notes'            => $request->notes,
                ]);
                
                // Hapus items lama
                \App\Models\DeliveryNoteItem::where('delivery_note_id', $id)->delete();
                
                // Buat items baru
                foreach ($request->items as $item) {
                    $batch = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                    $qty = (int) $item['qty'];

                    \App\Models\DeliveryNoteItem::create([
                        'delivery_note_id' => $deliveryNote->id,
                        'product_id'       => $batch->product_id,
                        'product_batch_id' => $batch->id,
                        'qty'              => $qty,
                        'description'      => $item['description'] ?? null,
                        'satuan'           => $item['satuan'] ?? null,
                    ]);
                }
            });
            
            return redirect()->route('admin.sales.delivery_notes.index')
                ->with('message', 'Surat Jalan berhasil diperbarui: ' . $deliveryNote->delivery_note_no);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function labInvoices()
    {
        return view('admin.sales.lab_invoice.index')->with('sb', 'SalesLabInvoices');
    }

    public function getLabInvoices(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaction::with(['user', 'customer'])
                ->where('transaction_type', 'kelas')
                ->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y H:i'))
                ->editColumn('total_amount', fn($row) => 'Rp ' . number_format($row->total_amount, 0, ',', '.'))
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('admin.sales.lab_invoices.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice Lab"><i class="fas fa-print"></i></a>';
                })
                ->make(true);
        }
    }

    public function createLabInvoice()
    {
        $customers = Customer::orderBy('name')->get();
        return view('admin.sales.lab_invoice.create', compact('customers'))->with('sb', 'SalesLabInvoices');
    }

    public function storeLabInvoice(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'amount' => 'required|numeric',
        ]);
        return redirect()->back()->with('message', 'Fitur pembuatan invoice lab manual sedang dalam pengembangan.');
    }

    public function printLabInvoice($id)
    {
        $transaction = Transaction::with(['user', 'customer'])->findOrFail($id);
        return view('admin.sales.lab_invoice.print', compact('transaction'));
    }
}

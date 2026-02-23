<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class SalesDocumentController extends Controller
{
    public function invoices()
    {
        return view('admin.sales.invoice.index')->with('sb', 'SalesInvoices');
    }

    public function getInvoices(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaction::with(['user', 'customer'])
                ->whereNotNull('invoice_number')
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y H:i'))
                ->editColumn('total_amount', fn($row) => 'Rp ' . number_format($row->total_amount, 0, ',', '.'))
                ->editColumn('payment_status', function ($row) {
                    $labels = [
                        'paid'     => '<span class="badge badge-success">Lunas</span>',
                        'unpaid'   => '<span class="badge badge-warning">Belum Bayar</span>',
                        'credit'   => '<span class="badge badge-info text-dark">DP (Credit)</span>',
                        'pending'  => '<span class="badge badge-warning">Pending</span>',
                        'draft'    => '<span class="badge badge-secondary">Draft</span>',
                        'canceled' => '<span class="badge badge-danger">Batal</span>',
                    ];
                    return $labels[$row->payment_status] ?? strtoupper($row->payment_status);
                })
                ->addColumn('action', function ($row) {
                    $show  = '<a href="' . route('admin.sales.invoices.show', $row->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                    $print = '<a href="' . route('admin.sales.invoices.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Cetak"><i class="fas fa-print"></i></a>';
                    $del   = '<button onclick="deleteInvoice(' . $row->id . ')" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>';
                    return $show . ' ' . $print . ' ' . $del;
                })
                ->rawColumns(['payment_status', 'action'])
                ->make(true);
        }
    }

    public function createInvoice()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('status', 'Y')
            ->with([
                'batches' => fn($q) => $q->where('qty', '>', 0)->orderBy('batch_no'),
                'variants',
            ])
            ->orderBy('name')
            ->get();

        $batchList = [];
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                // Find matching variant if any
                $variantName = '';
                if ($batch->product_variant_id && $product->variants) {
                    $variant = $product->variants->firstWhere('id', $batch->product_variant_id);
                    $variantName = $variant ? ' - ' . $variant->variant_name : '';
                }

                $batchList[] = [
                    'id'        => $batch->id,
                    'text'      => $product->name . $variantName . ' (Batch: ' . $batch->batch_no . ') - Stok: ' . $batch->qty,
                    'price'     => $batch->variant->price ?? ($product->price_real > 0 ? $product->price_real : $product->price),
                    'stock'     => $batch->qty,
                    'buy_price' => $batch->buy_price,
                ];
            }
        }

        $nextInvoiceNumber = InvoiceService::generate();

        return view('admin.sales.invoice.create', compact('customers', 'products', 'batchList', 'nextInvoiceNumber'))
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
                        'buy_price'        => $batch->buy_price,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                    ];

                    // Decrement stock if paid or credit (DP)
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

                // Use manual invoice number if provided, otherwise generate
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
        $transaction = Transaction::with(['user', 'customer', 'items.product', 'items.batch'])
            ->findOrFail($id);
        return view('admin.sales.invoice.show', compact('transaction'))->with('sb', 'SalesInvoices');
    }

    public function printInvoice($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product', 'items.batch'])
            ->findOrFail($id);
        return view('admin.sales.invoice.print', compact('transaction'));
    }

    public function destroyInvoice($id)
    {
        $transaction = Transaction::with('items')->findOrFail($id);
        try {
            DB::transaction(function () use ($transaction) {
                // Return stock if it was already decremented
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

    public function indexLab() { return view('admin.sales.lab.index')->with('sb', 'SalesLabs'); }
    public function indexDelivery() { return view('admin.sales.delivery.index')->with('sb', 'SalesDeliveries'); }
    public function indexReceipt() { return view('admin.sales.receipt.index')->with('sb', 'SalesReceipts'); }
}

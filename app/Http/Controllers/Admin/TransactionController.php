<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\TransactionItem;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Warehouse;
use App\Models\ChannelSetting;
use App\Services\InvoiceService;

class TransactionController extends Controller
{
    public function generateInvoice(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->invoice_number) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah memiliki nomor invoice: ' . $transaction->invoice_number
            ], 400);
        }

        try {
            DB::transaction(function () use ($transaction) {
                $transaction->update([
                    'invoice_number' => InvoiceService::generate()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat: ' . $transaction->invoice_number,
                'invoice_number' => $transaction->invoice_number
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $user = auth()->user();

        $counts = Transaction::where('is_sample', false)->select('payment_status', DB::raw('count(*) as total'))
            ->groupBy('payment_status')
            ->get()
            ->pluck('total', 'payment_status')
            ->toArray();

        $revenue = Transaction::where('is_sample', false)->select(
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END), 0) as paid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "pending" THEN total_amount ELSE 0 END), 0) as pending'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END), 0) as unpaid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "credit" THEN total_amount ELSE 0 END), 0) as credit'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total')
        )->first();

        $warehouses = collect();
        if ($user->isSuperAdmin() || $user->isStoreManager()) {
            $warehouses = Warehouse::orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->orderBy('name')->get();
        }

        return view('admin.transaction.index')->with([
            'sb' => 'Transaction',
            'counts' => $counts,
            'revenue' => $revenue,
            'warehouses' => $warehouses,
        ]);
    }

    public function getall(Request $request)
    {
        $query = Transaction::select(
            'transactions.id',
            'transactions.transaction_code',
            'transactions.user_id',
            'transactions.total_amount',
            'transactions.payment_status',
            'transactions.delivery_type',
            'transactions.source',
            'transactions.warehouse_id',
            'transactions.invoice_number',
            'transactions.transaction_date',
            'transactions.customer_name',
            'transactions.is_sample',
            'users.name as user_name',
            'warehouses.name as warehouse_name'
        )
        ->join('users', 'transactions.user_id', '=', 'users.id')
        ->leftJoin('warehouses', 'transactions.warehouse_id', '=', 'warehouses.id')
        ->orderBy('id', 'desc');

        // Filter by warehouse affiliation
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isStoreManager()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            if (!empty($warehouseIds)) {
                $query->whereIn('transactions.warehouse_id', $warehouseIds);
            } else {
                $query->whereNull('transactions.warehouse_id');
            }
        }

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('transactions.delivery_type', $request->delivery_type);
        }

        if ($request->filled('payment_status')) {
            $statuses = is_array($request->payment_status) ? $request->payment_status : [$request->payment_status];
            $query->whereIn('transactions.payment_status', $statuses);
        }

        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : [$request->source];
            $query->whereIn('transactions.source', $sources);
        }

        if ($request->filled('warehouse_id')) {
            $warehouses = is_array($request->warehouse_id) ? $request->warehouse_id : [$request->warehouse_id];
            $query->whereIn('transactions.warehouse_id', $warehouses);
        }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transactions.transaction_date', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transactions.transaction_date', '<=', $endDate);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('transaksi', function ($transaction) {
                $code = '<span style="font-family:monospace;font-size:12px;font-weight:600;color:#475569">' . e($transaction->transaction_code ?? '#' . $transaction->id) . '</span>';
                if ($transaction->invoice_number) {
                    $code .= '<div style="font-size:11px;color:#0d9488;font-weight:600;margin-top:2px">' . e($transaction->invoice_number) . '</div>';
                }
                if ($transaction->is_sample) {
                    $code .= '<span class="badge badge-danger" style="font-size:9px;padding:2px 6px;margin-top:3px">SAMPEL</span>';
                }
                return $code;
            })
            ->addColumn('pelanggan_kasir', function ($transaction) {
                $customer = '<div class="font-weight-bold text-dark">' . e($transaction->customer_name ?: '-') . '</div>';
                $kasir = '<small class="text-muted d-block">' . e($transaction->user_name) . '</small>';
                return $customer . $kasir;
            })
            ->addColumn('total_bayar', function ($transaction) {
                if ($transaction->is_sample) {
                    return '<span class="badge badge-danger">GRATIS</span>';
                }
                return '<span class="amount-text">Rp ' . number_format($transaction->total_amount, 0, ',', '.') . '</span>';
            })
            ->addColumn('saluran_gudang', function ($transaction) {
                $source = $transaction->source;
                if (!$source || $source == 'offline') {
                    $sourceHtml = '<span class="badge badge-light">Offline</span>';
                } else {
                    $colors = [
                        'shopee' => 'badge-soft-warning',
                        'tokopedia' => 'badge-soft-success',
                        'tiktok' => 'badge-soft-secondary',
                        'whatsapp' => 'badge-soft-info',
                        'offline-store' => 'badge-soft-primary',
                        'manual-invoice' => 'badge-soft-primary'
                    ];
                    $displayName = (in_array($source, ['offline-store', 'manual-invoice'])) ? 'OFFLINE STORE' : strtoupper($source);
                    $cls = $colors[strtolower($source)] ?? 'badge-soft-info';
                    $sourceHtml = '<span class="badge-soft ' . $cls . '">' . $displayName . '</span>';
                }
                $wh = $transaction->warehouse_name ?? 'Beautylatory';
                return '<div>' . $sourceHtml . '</div><small class="text-muted d-block">' . e($wh) . '</small>';
            })
            ->addColumn('pengiriman', function ($transaction) {
                $date = $transaction->transaction_date ? $transaction->transaction_date->format('d-m-Y') : '-';
                $dt = $transaction->delivery_type;
                $cls = ($dt == 'delivery') ? 'badge-soft-secondary' : 'badge-soft-info';
                $label = ucfirst($dt ?? 'pickup');
                $badge = '<span class="badge-soft ' . $cls . '" style="margin-top:2px">' . $label . '</span>';
                return '<div>' . $date . '</div>' . $badge;
            })
            ->addColumn('action', function ($transaction) {
                $role = auth()->user()->role;
                $invoiceBtn = '';
                if ($role !== 'finance') {
                    if (!$transaction->invoice_number) {
                        $invoiceBtn = '<li><button type="button" onclick="generateInvoice(' . $transaction->id . ')" class="dropdown-item text-primary font-weight-bold"><i class="fas fa-file-invoice"></i> Buat Invoice Baru</button></li>';
                    } else {
                        $invoiceBtn = '<li><a href="' . route('admin.sales.invoices.print', $transaction->id) . '" target="_blank" class="dropdown-item text-success"><i class="fas fa-print"></i> Cetak Invoice</a></li>';
                    }
                } else {
                    if ($transaction->invoice_number) {
                        $invoiceBtn = '<li><a href="' . route('admin.sales.invoices.print', $transaction->id) . '" target="_blank" class="dropdown-item text-success"><i class="fas fa-print"></i> Cetak Invoice</a></li>';
                    }
                }

                $editBtn = '<li><a href="' . route('admin.transactions.edit', $transaction->id) . '" class="dropdown-item">Edit</a></li>';
                $deleteBtn = '<li><button type="button" onclick="deleteTransaction(' . $transaction->id . ')" class="dropdown-item text-danger">Hapus</button></li>';
                $divider = '<div class="dropdown-divider"></div>';

                if ($role == 'finance') {
                    $editBtn = '';
                    $deleteBtn = '';
                    $divider = '';
                }

                return '<div class="dropdown d-inline dropleft">
                    <button type="button" class="btn btn-action-custom btn-sm dropdown-toggle" data-toggle="dropdown">
                        Action
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="' . url('admin/transactions/show/'. $transaction->id) . '" class="dropdown-item">Detail</a></li>
                        ' . $editBtn . '
                        <li><a href="' . route('admin.transactions.print_struk', $transaction->id) . '" target="_blank" class="dropdown-item">Print Struk</a></li>
                        ' . $invoiceBtn . '
                        ' . $divider . '
                        ' . $deleteBtn . '
                    </ul>
                </div>';
            })
            ->rawColumns(['transaksi', 'pelanggan_kasir', 'total_bayar', 'saluran_gudang', 'pengiriman', 'action'])
            ->make(true);
    }

    public function getRevenue(Request $request)
    {
        $query = Transaction::where('is_sample', false);

        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isStoreManager()) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
            if (!empty($warehouseIds)) {
                $query->whereIn('warehouse_id', $warehouseIds);
            } else {
                $query->whereNull('warehouse_id');
            }
        }

        if ($request->filled('warehouse_id')) {
            $warehouses = is_array($request->warehouse_id) ? $request->warehouse_id : [$request->warehouse_id];
            $query->whereIn('warehouse_id', $warehouses);
        }

        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : [$request->source];
            $query->whereIn('source', $sources);
        }

        if ($request->filled('payment_status')) {
            $statuses = is_array($request->payment_status) ? $request->payment_status : [$request->payment_status];
            $query->whereIn('payment_status', $statuses);
        }

        if ($request->filled('start_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transaction_date', '<=', $endDate);
        }

        $revenue = $query->select(
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END), 0) as paid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "pending" THEN total_amount ELSE 0 END), 0) as pending'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END), 0) as unpaid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "credit" THEN total_amount ELSE 0 END), 0) as credit'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total')
        )->first();

        return response()->json($revenue);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product.merek', 'items.batch.variant', 'payments'])->findOrFail($id);
        return view('admin.transaction.show', compact('transaction'))->with('sb', 'Transaction');
    }

    public function print(Request $request)
    {
        $transactions = $this->getFilteredTransactions($request);
        $totalSum = $transactions->sum('total_amount');
        return view('admin.transaction.print', compact('transactions', 'totalSum'));
    }

    public function exportExcel(Request $request)
    {
        $transactions = $this->getFilteredTransactions($request);
        $totalSum = $transactions->sum('total_amount');
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TransactionExport($transactions, $totalSum), 'Laporan-Transaksi-' . date('d-m-Y') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $transactions = $this->getFilteredTransactions($request);
        $totalSum = $transactions->sum('total_amount');
        // Use container binding to avoid hard dependency on facade class alias.
        $pdf = app('dompdf.wrapper')->loadView('admin.transaction.print_pdf', compact('transactions', 'totalSum'));
        return $pdf->download('Laporan-Transaksi-' . date('d-m-Y') . '.pdf');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TransactionTemplateExport(),
            'Template-Import-Transaksi.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            'import_type' => 'required|in:shopee,template',
        ]);

        try {
            $import = new \App\Imports\TransactionOrderImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $sourceRows = $import->rows();
            if ($sourceRows->isEmpty()) {
                return redirect()->route('admin.transactions.index')
                    ->with('error', 'File tidak berisi data transaksi.');
            }

            $firstRow = $sourceRows->first()->toArray();
            $importType = $request->input('import_type');
            $marketplaceFormat = $importType === 'shopee' || (array_key_exists('no_pesanan', $firstRow)
                && array_key_exists('nama_produk', $firstRow));
            $templateFormat = $importType === 'template' || array_key_exists('product_name', $firstRow);

            if (!$marketplaceFormat && !$templateFormat) {
                return redirect()->route('admin.transactions.index')->with(
                    'error',
                    'Format file tidak dikenali. Pilih tipe import sesuai file Anda (Shopee atau Template Import Transaksi).'
                );
            }

            if ($sourceRows->count() > 5000) {
                return redirect()->route('admin.transactions.index')
                    ->with('error', 'File berisi lebih dari 5.000 baris. Pisahkan file sebelum melanjutkan.');
            }

            $rows = [];
            $skippedRows = 0;
            foreach ($sourceRows as $index => $sourceRow) {
                $row = $sourceRow->toArray();

                if ($marketplaceFormat) {
                    $status = strtolower(trim((string) ($row['status_pesanan'] ?? '')));
                    if ($status !== 'selesai') {
                        $skippedRows++;
                        continue;
                    }

                    $orderNumber = trim((string) ($row['no_pesanan'] ?? ''));
                    $productName = trim((string) ($row['nama_produk'] ?? ''));
                    $quantity = (int) $this->parseImportNumber($row['jumlah'] ?? 0);
                    $listPrice = $this->parseImportNumber($row['harga_awal'] ?? 0);
                    $discountedPrice = $this->parseImportNumber($row['harga_setelah_diskon'] ?? 0);
                    $subtotal = $this->parseImportNumber($row['subtotal_pesanan'] ?? 0);

                    if ($subtotal <= 0) {
                        $subtotal = $discountedPrice * $quantity;
                    }
                    $price = $listPrice > 0 ? $listPrice : $discountedPrice;
                    $discount = max(0, ($price * $quantity) - $subtotal);
                    $dateValue = $row['waktu_pesanan_dibuat'] ?? null;
                    $sku = trim((string) ($row['nomor_referensi_sku'] ?? $row['sku_induk'] ?? ''));
                    $variantName = trim((string) ($row['nama_variasi'] ?? ''));
                    $customerName = trim((string) ($row['nama_penerima'] ?? $row['username_pembeli'] ?? ''));
                    $customerPhone = trim((string) ($row['no_telepon'] ?? ''));
                    $paymentStatus = 'paid';
                    $paymentMethod = 'transfer';
                    $rowNotes = '';
                } else {
                    $orderNumber = trim((string) ($row['transaction_code'] ?? ''));
                    if ($orderNumber === '') {
                        $orderNumber = 'BARIS-' . ($index + 2);
                    }
                    $productName = trim((string) ($row['product_name'] ?? ''));
                    $quantity = (int) $this->parseImportNumber($row['qty'] ?? 0);
                    $price = $this->parseImportNumber($row['price'] ?? 0);
                    $discount = $this->parseImportNumber($row['discount'] ?? 0);
                    $subtotal = max(0, ($price * $quantity) - $discount);
                    $dateValue = $row['transaction_date'] ?? null;
                    $sku = trim((string) ($row['sku'] ?? $row['code'] ?? ''));
                    $variantName = '';
                    $customerName = trim((string) ($row['customer_name'] ?? ''));
                    $customerPhone = trim((string) ($row['customer_phone'] ?? ''));
                    $paymentStatus = strtolower(trim((string) ($row['payment_status'] ?? 'paid')));
                    if (!in_array($paymentStatus, ['paid', 'unpaid', 'credit', 'pending', 'canceled'], true)) {
                        $paymentStatus = 'paid';
                    }
                    $paymentMethod = trim((string) ($row['payment_method'] ?? 'transfer')) ?: 'transfer';
                    $rowNotes = trim((string) ($row['notes'] ?? ''));
                }

                if ($orderNumber === '' || $productName === '' || $quantity < 1 || $price < 0) {
                    $skippedRows++;
                    continue;
                }

                $date = $this->parseImportDate($dateValue);
                $mappingKey = sha1(strtolower(trim($productName . '|' . $sku . '|' . $variantName)));
                $rows[] = [
                    'order_number' => $orderNumber,
                    'product_name' => $productName,
                    'sku' => $sku,
                    'variant_name' => $variantName,
                    'mapping_key' => $mappingKey,
                    'qty' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                    'transaction_date' => $date->format('Y-m-d'),
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentMethod,
                    'notes' => $rowNotes,
                ];
            }

            if (empty($rows)) {
                return redirect()->route('admin.transactions.index')
                    ->with('error', 'Tidak ada baris valid untuk diimpor. Pastikan status pesanan Selesai, nomor pesanan, nama produk, jumlah, dan harga terisi.');
            }

            $groups = collect($rows)->groupBy('order_number');
            $orderMarkers = $groups->keys()->map(fn ($number) => $this->orderImportNote($number))->all();
            $existingNotes = Transaction::whereIn('notes', $orderMarkers)->pluck('notes')->all();
            $existingNoteLookup = array_fill_keys($existingNotes, true);
            $orders = $groups->map(function ($items, $number) use ($existingNoteLookup) {
                return [
                    'number' => $number,
                    'rows' => $items->count(),
                    'products' => $items->groupBy('product_name')->map(function ($productRows, $productName) {
                        return [
                            'name' => $productName,
                            'qty' => $productRows->sum('qty'),
                        ];
                    })->values()->all(),
                    'total' => $items->sum('subtotal'),
                    'date' => $items->first()['transaction_date'],
                    'already_imported' => isset($existingNoteLookup[$this->orderImportNote($number)]),
                ];
            })->values()->all();

            $products = collect($rows)->groupBy('mapping_key')->map(function ($items) {
                $row = $items->first();
                return [
                    'name' => $row['product_name'],
                    'sku' => $row['sku'],
                    'variant_name' => $row['variant_name'],
                    'rows' => $items->count(),
                ];
            });

            $autoMatches = [];
            foreach ($products as $key => $productInfo) {
                $sku = trim((string) ($productInfo['sku'] ?? ''));
                if ($sku === '') {
                    continue;
                }

                $skuLower = strtolower($sku);

                $variant = \App\Models\ProductVariant::with('product')
                    ->whereRaw('LOWER(TRIM(sku_code)) = ?', [$skuLower])
                    ->first();

                if ($variant && $variant->product) {
                    $autoMatches[$key] = $variant->product_id . ':' . $variant->id;
                    continue;
                }

                $product = Product::with('variants')
                    ->whereRaw('LOWER(TRIM(code)) = ?', [$skuLower])
                    ->first();

                if ($product) {
                    if ($product->variants->isNotEmpty()) {
                        $firstVariant = $product->variants->first();
                        if ($firstVariant) {
                            $autoMatches[$key] = $product->id . ':' . $firstVariant->id;
                        }
                        continue;
                    }

                    $autoMatches[$key] = $product->id . ':';
                }
            }

            $token = \Illuminate\Support\Str::random(40);
            $preview = [
                'token' => $token,
                'created_at' => now()->timestamp,
                'source_file' => $request->file('file')->getClientOriginalName(),
                'format' => $marketplaceFormat ? 'marketplace' : 'template',
                'rows' => $rows,
                'products' => $products->all(),
                'auto_matches' => $autoMatches,
                'orders' => $orders,
                'skipped_rows' => $skippedRows,
                'total_rows' => $sourceRows->count(),
            ];
            session()->put('transaction_order_imports.' . $token, $preview);

            return redirect()->route('admin.transactions.import.preview', $token);
        } catch (\Exception $e) {
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function previewImport($token)
    {
        $preview = session()->get('transaction_order_imports.' . $token);
        if (!$preview || now()->timestamp - $preview['created_at'] > 7200) {
            session()->forget('transaction_order_imports.' . $token);
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Data preview tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang file.');
        }

        $user = auth()->user();
        $warehouses = ($user->isSuperAdmin() || $user->isStoreManager())
            ? Warehouse::where('status', 'active')->orderBy('name')->get()
            : $user->warehouses()->where('warehouses.status', 'active')->orderBy('warehouses.name')->get();
        $channels = ChannelSetting::orderBy('name')->get();

        return view('admin.transaction.import-preview', compact('preview', 'warehouses', 'channels'))
            ->with('sb', 'Transaction');
    }

    public function searchImportProducts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $selected = trim((string) $request->query('selected', ''));
        $hasSelected = preg_match('/^(\d+):(\d*)$/', $selected, $selectedParts) === 1;
        if (!$hasSelected && mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $products = Product::with(['merek', 'variants.netto'])
            ->when($hasSelected, fn ($query) => $query->whereKey((int) $selectedParts[1]))
            ->when(!$hasSelected, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhereHas('merek', fn ($merek) => $merek->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('variants', function ($variants) use ($search) {
                            $variants->where('variant_name', 'like', '%' . $search . '%')
                                ->orWhere('sku_code', 'like', '%' . $search . '%')
                                ->orWhereHas('netto', fn ($netto) => $netto->where('netto_value', 'like', '%' . $search . '%'));
                        });
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $brand = $product->merek ? trim($product->merek->name) : '';
            $variants = $product->variants;

            if ($variants->isEmpty()) {
                $results[] = [
                    'id' => $product->id . ':',
                    'text' => implode(' ', array_filter([$brand, trim($product->name)])),
                ];
                continue;
            }

            foreach ($variants as $variant) {
                if ($hasSelected && (string) $variant->id !== $selectedParts[2]) {
                    continue;
                }
                $netto = $variant->netto;
                $results[] = [
                    'id' => $product->id . ':' . $variant->id,
                    'text' => implode(' ', array_filter([
                        $brand,
                        trim($product->name),
                        $netto ? trim($netto->netto_value) : '',
                        $netto ? trim($netto->satuan ?? '') : '',
                    ])),
                ];
                if (count($results) >= 30 || $hasSelected) {
                    break 2;
                }
            }
        }

        return response()->json(['results' => $results]);
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'token' => 'required|alpha_num',
            'source' => 'required|exists:channel_settings,slug',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'product_map' => 'nullable|array',
            'product_map.*' => 'nullable|string',
        ]);

        $token = $request->input('token');
        $preview = session()->get('transaction_order_imports.' . $token);
        if (!$preview || now()->timestamp - $preview['created_at'] > 7200) {
            session()->forget('transaction_order_imports.' . $token);
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Data preview tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang file.');
        }

        $user = auth()->user();
        $allowedWarehouseIds = ($user->isSuperAdmin() || $user->isStoreManager())
            ? Warehouse::where('status', 'active')->pluck('id')->all()
            : $user->warehouses()->where('warehouses.status', 'active')->pluck('warehouses.id')->all();
        if (!in_array((int) $request->warehouse_id, $allowedWarehouseIds)) {
            return redirect()->route('admin.transactions.import.preview', $token)
                ->with('error', 'Anda tidak memiliki akses ke gudang yang dipilih.')
                ->withInput($request->except('_token'));
        }

        $submittedMappings = $request->input('product_map') ?? [];
        $expectedKeys = array_keys($preview['products']);
        $productMap = [];
        foreach ($expectedKeys as $key) {
            $selectedProduct = trim((string) ($submittedMappings[$key] ?? ''));
            if ($selectedProduct === '') {
                continue;
            }

            if (!preg_match('/^(\d+):(\d*)$/', $selectedProduct, $matches)) {
                return redirect()->route('admin.transactions.import.preview', $token)
                    ->with('error', 'Pilihan produk tidak valid. Silakan pilih ulang produk pada preview.')
                    ->withInput($request->except('_token'));
            }

            $product = Product::with('variants')->find($matches[1]);
            $variantId = $matches[2] !== '' ? (int) $matches[2] : null;
            if (!$product) {
                return redirect()->route('admin.transactions.import.preview', $token)
                    ->with('error', 'Produk POS yang dipilih sudah tidak tersedia. Silakan pilih ulang.')
                    ->withInput($request->except('_token'));
            }

            if ($variantId && !$product->variants->contains('id', $variantId)) {
                return redirect()->route('admin.transactions.import.preview', $token)
                    ->with('error', 'Varian produk tidak sesuai dengan produk yang dipilih.')
                    ->withInput($request->except('_token'));
            }
            if (!$variantId && $product->variants->isNotEmpty()) {
                return redirect()->route('admin.transactions.import.preview', $token)
                    ->with('error', 'Produk ini memiliki varian. Pilih varian yang sesuai pada preview.')
                    ->withInput($request->except('_token'));
            }

            $productMap[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
            ];
        }

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $orderGroups = collect($preview['rows'])->groupBy('order_number');
        $imported = 0;
        $duplicates = 0;
        $skippedRows = 0;
        $skippedOrders = 0;
        $syncedProductIds = [];

        try {
            DB::transaction(function () use (
                $orderGroups,
                $productMap,
                $request,
                $warehouse,
                &$imported,
                &$duplicates,
                &$skippedRows,
                &$skippedOrders,
                &$syncedProductIds
            ) {
                $stockService = new \App\Services\StockService();
                foreach ($orderGroups as $orderNumber => $items) {
                    $referenceNote = $this->orderImportNote($orderNumber);
                    if (Transaction::where('notes', $referenceNote)->lockForUpdate()->exists()) {
                        $duplicates++;
                        continue;
                    }

                    $first = $items->first();
                    $transactionDate = Carbon::parse($first['transaction_date']);
                    $itemsToCreate = [];
                    $totalAmount = 0;

                    foreach ($items as $item) {
                        if (!isset($productMap[$item['mapping_key']])) {
                            $skippedRows++;
                            continue;
                        }

                        $mapping = $productMap[$item['mapping_key']];
                        $slices = $stockService->allocateAcrossBatches(
                            $mapping['product_id'],
                            $mapping['variant_id'],
                            $warehouse->id,
                            $item['qty']
                        );

                        $firstSlice = true;
                        foreach ($slices as $slice) {
                            $sliceDiscount = $firstSlice ? $item['discount'] : 0;
                            $firstSlice = false;
                            $subtotal = max(0, ($item['price'] * $slice['take']) - $sliceDiscount);
                            $totalAmount += $subtotal;
                            $itemsToCreate[] = [
                                'product_id' => $mapping['product_id'],
                                'product_variant_id' => $mapping['variant_id'],
                                'product_batch_id' => $slice['batch']->id,
                                'buy_price' => $slice['batch']->buy_price ?? 0,
                                'qty' => $slice['take'],
                                'price' => $item['price'],
                                'discount' => $sliceDiscount,
                                'subtotal' => $subtotal,
                            ];
                        }
                    }

                    if (empty($itemsToCreate)) {
                        $skippedOrders++;
                        continue;
                    }

                    $transaction = Transaction::create([
                        'transaction_code' => \App\Services\TransactionCodeService::generate(
                            $transactionDate,
                            $warehouse->code ?? ''
                        ),
                        'user_id' => auth()->id(),
                        'warehouse_id' => $warehouse->id,
                        'customer_name' => $first['customer_name'] ?: null,
                        'customer_phone' => $first['customer_phone'] ?: null,
                        'source' => $request->source,
                        'notes' => $referenceNote,
                        'total_amount' => $totalAmount,
                        'payment_status' => $first['payment_status'],
                        'payment_method' => $first['payment_method'],
                        'delivery_type' => 'delivery',
                        'delivery_desc' => 'Imported marketplace order',
                        'transaction_date' => $transactionDate->format('Y-m-d'),
                    ]);

                    $transaction->update([
                        'invoice_number' => InvoiceService::generate($transactionDate),
                    ]);

                    foreach ($itemsToCreate as $itemData) {
                        $itemData['transaction_id'] = $transaction->id;
                        TransactionItem::create($itemData);
                        $syncedProductIds[] = $itemData['product_id'];
                    }

                    $imported++;
                }

                Product::bulkSyncStock(array_values(array_unique($syncedProductIds)));
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.transactions.import.preview', $token)
                ->with('error', 'Import dibatalkan tanpa menyimpan transaksi: ' . $e->getMessage())
                ->withInput($request->except('_token'));
        }

        session()->forget('transaction_order_imports.' . $token);
        $message = "Proses import selesai. {$imported} transaksi berhasil diimpor.";
        if ($duplicates > 0) {
            $message .= " {$duplicates} nomor pesanan dilewati karena sudah pernah diimpor.";
        }
        if ($skippedRows > 0) {
            $message .= " {$skippedRows} baris produk tidak diimpor karena belum dicocokkan dengan produk POS.";
        }
        if ($skippedOrders > 0) {
            $message .= " {$skippedOrders} nomor pesanan tidak dibuat karena tidak memiliki produk yang sudah dicocokkan.";
        }

        return redirect()->route('admin.transactions.index')->with('message', $message);
    }

    private function orderImportNote(string $orderNumber): string
    {
        return 'Import marketplace order: ' . $orderNumber;
    }

    private function parseImportNumber($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.-]/u', '', $value);
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    private function parseImportDate($value): Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 100000) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
        }

        $value = trim((string) $value);
        if ($value === '') {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value);
            } catch (\Exception $ignored) {
                throw new \InvalidArgumentException('Tanggal transaksi tidak valid: ' . $value);
            }
        }
    }

    private function getFilteredTransactions(Request $request)
    {
        $query = Transaction::with(['user', 'customer', 'items.product.merek'])->where('is_sample', false);

        // Apply filters
        if ($request->has('delivery_type') && !empty($request->delivery_type)) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('payment_status')) {
            $statuses = is_array($request->payment_status) ? $request->payment_status : [$request->payment_status];
            $query->whereIn('payment_status', $statuses);
        }

        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : [$request->source];
            $query->whereIn('source', $sources);
        }

        if ($request->filled('warehouse_id')) {
            $warehouses = is_array($request->warehouse_id) ? $request->warehouse_id : [$request->warehouse_id];
            $query->whereIn('warehouse_id', $warehouses);
        }

        if ($request->filled('start_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function printStruk($id)
    {
        $transaction = Transaction::with(['user', 'customer', 'items.product', 'items.batch', 'payments'])->findOrFail($id);
        $storeSetting = \App\Models\StoreSetting::find(1);
        return view('admin.transaction.print_struk', compact('transaction', 'storeSetting'));
    }

    public function edit($id)
    {
        $transaction = Transaction::with(['customer', 'items.product.merek', 'items.batch.variant'])
            ->findOrFail($id);
        
        $customers = Customer::orderBy('name')->get();

        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isStoreManager()) {
            $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()
                ->where('warehouses.status', 'active')
                ->orderBy('warehouses.name')
                ->get();
        }
        
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
                'price'     => $batch->variant ? $batch->variant->getSellingPrice() : ($product->price_real > 0 ? $product->price_real : $product->price),
                'stock'     => $batch->qty,
                'buy_price' => $batch->buy_price ?? 0,
            ];
        }

        // Add bundles to batchList
        $bundles = Product::where('is_bundle', true)->where('status', 'Y')->with('merek', 'variants')->get();
        foreach ($bundles as $bundle) {
            $merekName = $bundle->merek ? trim($bundle->merek->name) : '';
            $labelText = implode(' ', array_filter([$merekName, trim($bundle->name)]));
            
            $batchList[] = [
                'id'        => 'bundle-' . $bundle->id,
                'text'      => $labelText . ' (Bundling)',
                'price'     => $bundle->price > 0 ? $bundle->price : ($bundle->variants->first()->price ?? 0),
                'stock'     => 999,
                'buy_price' => 0,
            ];
        }

        return view('admin.transaction.edit', compact('transaction', 'customers', 'batchList', 'warehouses'))
            ->with('sb', 'Transaction');
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'transaction_date' => 'nullable|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|in:draft,unpaid,paid,canceled,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'is_sample' => 'nullable|boolean',
            'other_fees' => 'nullable|json',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction, $id) {
                $totalAmount   = 0;
                $itemsToCreate = [];
                $isSample = $request->boolean('is_sample');
                
                foreach ($request->items as $item) {
                    $isBundle = str_starts_with($item['product_batch_id'], 'bundle-');
                    $qty      = (int) $item['qty'];
                    $price    = (float) $item['price'];
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;

                    if ($isBundle) {
                        $bundleId = str_replace('bundle-', '', $item['product_batch_id']);
                        $product = Product::findOrFail($bundleId);
                        $batchId = null;
                        $buyPrice = 0;
                    } else {
                        $batch    = ProductBatch::with('product')->findOrFail($item['product_batch_id']);
                        $product  = $batch->product;
                        $batchId = $batch->id;
                        $buyPrice = $batch->buy_price ?? 0;
                    }

                    $itemsToCreate[] = [
                        'product_id'       => $product->id,
                        'product_batch_id' => $batchId,
                        'buy_price'        => $buyPrice,
                        'qty'              => $qty,
                        'price'            => $price,
                        'subtotal'         => $subtotal,
                        'is_bundle_main'   => $isBundle,
                        'is_sample'        => $isSample,
                    ];
                }
                
                $otherFees = [];
                $otherFeesTotal = 0;
                if ($request->other_fees) {
                    $otherFees = json_decode($request->other_fees, true) ?? [];
                    foreach ($otherFees as $fee) {
                        $otherFeesTotal += (float)($fee['amount'] ?? 0);
                    }
                }
                $taxAmount    = (float) ($request->tax_amount ?? 0);
                $discountVal  = (float) ($request->discount ?? 0);
                $taxType      = $request->tax_type ?? 'none';
                $discountType = $request->discount_type ?? 'fixed';
                $grandTotal   = ($totalAmount + $taxAmount) - $discountVal + $otherFeesTotal;
                if ($isSample) {
                    $grandTotal = 0;
                    $request->merge(['payment_status' => 'paid']);
                }
                $txDate = $request->transaction_date ? Carbon::parse($request->transaction_date) : $transaction->transaction_date;
                
                $transaction->update([
                    'customer_id'      => $request->customer_id,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'warehouse_id'     => $request->warehouse_id,
                    'notes'            => $request->notes,
                    'other_fees'       => $otherFees,
                    'total_amount'     => $grandTotal,
                    'is_sample'        => $isSample,
                    'discount'         => $discountVal,
                    'discount_type'    => $discountType,
                    'tax_type'         => $taxType,
                    'tax_amount'       => $taxAmount,
                    'payment_status'   => $request->payment_status,
                    'payment_method'   => $request->payment_method,
                    'transaction_date' => $txDate,
                ]);
                
                // Hapus items lama
                TransactionItem::where('transaction_id', $id)->delete();
                
                // Buat items baru
                $stockService = new \App\Services\StockService();
                foreach ($itemsToCreate as $itemData) {
                    $isBundleMain = $itemData['is_bundle_main'] ?? false;
                    unset($itemData['is_bundle_main']);

                    $itemData['transaction_id'] = $transaction->id;
                    $mainItem = TransactionItem::create($itemData);

                    if (in_array($request->payment_status, ['paid', 'credit']) && $isBundleMain) {
                        $stockService->explodeBundleComponents($itemData['product_id'], $itemData['qty'], $transaction->id, $mainItem->id);
                    }
                }

                // Model Jurnal: TI berubah → segarkan stok produk dari rumus.
                Product::bulkSyncStock(collect($itemsToCreate)->pluck('product_id')->unique()->values()->all());
            });
            
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('message', 'Transaksi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::with('items.product')->findOrFail($id);
        try {
            DB::transaction(function () use ($transaction) {
                // Model Jurnal: tanpa restore M (tidak pernah dikurangi); hapus baris lalu sync rumus.
                $syncProductIds = $transaction->items->pluck('product_id')->all();

                // Hapus items (sudah cascade di DB biasanya, tapi amannya hapus manual)
                $transaction->items()->delete();
                
                // Hapus payments terkait
                if (method_exists($transaction, 'payments')) {
                    $transaction->payments()->delete();
                }

                $transaction->delete();

                Product::bulkSyncStock($syncProductIds);
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
            return redirect()->back()->with('info', 'Transaksi sudah lunas.');
        }
        try {
            DB::transaction(function () use ($transaction, $request) {
                // Deduct stock if not already deducted (for credit/unpaid status)
                // Model Jurnal: validasi pakai S; M tidak dikurangi, keluar tercatat via TI.
                if (!in_array($transaction->payment_status, ['paid', 'credit'])) {
                    foreach ($transaction->items as $item) {
                        if ($item->product_batch_id) {
                            $batch = $item->batch;
                            if ($batch && $batch->current_stock < $item->qty) {
                                throw new \Exception("Stok batch {$batch->batch_no} untuk produk {$item->product->name} tidak mencukupi (tersisa {$batch->current_stock}).");
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
            return redirect()->back()->with('message', 'Transaksi #' . $transaction->id . ' telah dilunasi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melunasi transaksi: ' . $e->getMessage());
        }
    }

    public function updatePaymentReceipt(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:transaction_payments,id',
                'receipt'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $payment = TransactionPayment::findOrFail($request->payment_id);
            
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_update_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $payment->update([
                    'payment_receipt' => 'uploads/receipts/' . $filename
                ]);
            }

            return redirect()->back()->with('message', 'Bukti pembayaran berhasil diupload.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }

    public function quickUploadReceipt(Request $request, $id)
    {
        try {
            $request->validate([
                'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $transaction = Transaction::findOrFail($id);
            
            // Check for existing payment without receipt
            $payment = TransactionPayment::where('transaction_id', $id)
                        ->whereNull('payment_receipt')
                        ->first();
            
            if (!$payment) {
                // If no payment record exists, create one for the full amount (since it's 'paid')
                $payment = TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount'         => $transaction->total_amount,
                    'payment_date'   => $transaction->transaction_date,
                    'payment_method' => $transaction->payment_method ?: 'manual',
                    'notes'          => 'Otomatis via Upload Bukti (Lunas)',
                ]);
            }

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = time() . '_quick_' . $file->getClientOriginalName();
                $fullPath = public_path('uploads/receipts');
                if (!File::isDirectory($fullPath)) {
                    File::makeDirectory($fullPath, 0777, true, true);
                }
                $file->move($fullPath, $filename);
                $payment->update([
                    'payment_receipt' => 'uploads/receipts/' . $filename
                ]);
            }

            return redirect()->back()->with('message', 'Bukti pembayaran berhasil diupload.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }
    public function productReport()
    {
        $sources = Transaction::where('is_sample', false)
            ->whereNotNull('source')
            ->distinct()
            ->pluck('source')
            ->toArray();

        $query = Transaction::where('is_sample', false);

        if (request('start_date')) {
            $query->where('transaction_date', '>=', Carbon::parse(request('start_date'))->startOfDay());
        }
        if (request('end_date')) {
            $query->where('transaction_date', '<=', Carbon::parse(request('end_date'))->endOfDay());
        }
        if (request('source')) {
            $query->where('source', request('source'));
        }

        $revenue = $query->select(
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END), 0) as paid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "pending" THEN total_amount ELSE 0 END), 0) as pending'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END), 0) as unpaid'),
            DB::raw('COALESCE(SUM(CASE WHEN payment_status = "credit" THEN total_amount ELSE 0 END), 0) as credit'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total')
        )->first();

        return view('admin.transaction.report_product')->with([
            'sb' => 'Transaction',
            'sources' => $sources,
            'revenue' => $revenue,
        ]);
    }

    public function productReportData(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'merek.name as merek_name',
                'products.name as product_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                'product_variants.product_hpp',
                DB::raw('CASE
                    WHEN product_variants.is_approved = 1 AND product_variants.het_online > 0 THEN product_variants.het_online
                    WHEN product_variants.price > 0 THEN product_variants.price
                    WHEN products.price_real > 0 THEN products.price_real
                    ELSE products.price
                END as selling_price'),
                'product_nettos.netto_value',
                'product_nettos.satuan',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_amount'),
                DB::raw('SUM(transaction_items.qty * product_variants.product_hpp) as total_hpp')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        // Apply filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.transaction_date', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.transaction_date', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        if ($request->has('source') && !empty($request->source)) {
            $query->where('transactions.source', $request->source);
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $query->groupBy(
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                'merek.name',
                'products.name',
                'products.price_real',
                'products.price',
                'product_variants.variant_name',
                'product_variants.sku_code',
                'product_variants.product_hpp',
                'product_variants.is_approved',
                'product_variants.het_online',
                'product_variants.price',
                'product_nettos.netto_value',
                'product_nettos.satuan'
            )
              ->orderBy('total_qty', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('total_amount', function ($row) {
                return 'Rp ' . number_format($row->total_amount, 0, ',', '.');
            })
            ->editColumn('product_hpp', function ($row) {
                return 'Rp ' . number_format($row->product_hpp, 0, ',', '.');
            })
            ->editColumn('selling_price', function ($row) {
                return 'Rp ' . number_format($row->selling_price, 0, ',', '.');
            })
            ->editColumn('total_hpp', function ($row) {
                return 'Rp ' . number_format($row->total_hpp, 0, ',', '.');
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('products.name', 'like', "%{$keyword}%")
                      ->orWhere('merek.name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('variant_name', function ($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('product_variants.variant_name', 'like', "%{$keyword}%")
                      ->orWhere('product_variants.sku_code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('total_qty', function ($query, $keyword) {
                // numeric aggregate — skip global search
            })
            ->filterColumn('total_amount', function ($query, $keyword) {
                // numeric aggregate — skip global search
            })
            ->filterColumn('product_hpp', function ($query, $keyword) {
                // numeric aggregate — skip global search
            })
            ->filterColumn('selling_price', function ($query, $keyword) {
                // numeric aggregate — skip global search
            })
            ->filterColumn('total_hpp', function ($query, $keyword) {
                // numeric aggregate — skip global search
            })
            ->make(true);
    }

    public function printProductReport(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'merek.name as merek_name',
                'products.name as product_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                'product_variants.product_hpp',
                DB::raw('CASE
                    WHEN product_variants.is_approved = 1 AND product_variants.het_online > 0 THEN product_variants.het_online
                    WHEN product_variants.price > 0 THEN product_variants.price
                    WHEN products.price_real > 0 THEN products.price_real
                    ELSE products.price
                END as selling_price'),
                'product_nettos.netto_value',
                'product_nettos.satuan',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_amount'),
                DB::raw('SUM(transaction_items.qty * product_variants.product_hpp) as total_hpp')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.transaction_date', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.transaction_date', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        if ($request->has('source') && !empty($request->source)) {
            $query->where('transactions.source', $request->source);
        }

        // Bundling Logic: Show components, hide bundle parents
        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $items = $query->groupBy(
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                'merek.name',
                'products.name',
                'products.price_real',
                'products.price',
                'product_variants.variant_name',
                'product_variants.sku_code',
                'product_variants.product_hpp',
                'product_variants.is_approved',
                'product_variants.het_online',
                'product_variants.price',
                'product_nettos.netto_value',
                'product_nettos.satuan'
            )
              ->orderBy('total_qty', 'desc')
              ->get();

        return view('admin.transaction.print_product', compact('items'));
    }

    public function exportProductBatchReport(Request $request)
    {
        $query = DB::table('transaction_items')
            ->select(
                'transactions.transaction_date',
                'merek.name as merek_name',
                'products.name as product_name',
                'product_nettos.netto_value',
                'product_nettos.satuan',
                'product_batches.batch_no',
                'transaction_items.qty'
            )
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->leftJoin('product_batches', 'transaction_items.product_batch_id', '=', 'product_batches.id');

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('transactions.transaction_date', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('transactions.transaction_date', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $query->where(function($q) {
            $q->where('products.is_bundle', 0)
              ->orWhereNotNull('transaction_items.parent_item_id');
        });

        $items = $query->orderBy('transactions.transaction_date')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductBatchExport($items),
            'Laporan-Penjualan-Per-Batch-' . date('d-m-Y') . '.xlsx'
        );
    }
}

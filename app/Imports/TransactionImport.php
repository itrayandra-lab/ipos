<?php

namespace App\Imports;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Services\TransactionCodeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionImport implements ToCollection, WithHeadingRow
{
    protected $userId;
    protected $warehouseId;
    protected $imported = 0;
    protected $skipped = 0;

    public function __construct($userId, $warehouseId = null)
    {
        $this->userId = $userId;
        $this->warehouseId = $warehouseId;
    }

    public function collection(Collection $rows)
    {
        $groups = $rows->groupBy(function ($row) {
            return !empty($row['transaction_code']) ? $row['transaction_code'] : uniqid('tmp_');
        });

        foreach ($groups as $code => $group) {
            DB::transaction(function () use ($group, $code) {
                $first = $group->first();

                $transactionCode = (!str_starts_with($code, 'tmp_'))
                    ? $code
                    : TransactionCodeService::generate(
                        $this->parseDate($first['transaction_date'] ?? null),
                        ''
                    );

                $transactionDate = $this->parseDate($first['transaction_date'] ?? null);

                $totalAmount = 0;
                $items = [];

                foreach ($group as $row) {
                    $productName = trim($row['product_name'] ?? '');
                    $qty = (int)($row['qty'] ?? 1);
                    $price = (float)($row['price'] ?? 0);
                    $discount = (float)($row['discount'] ?? 0);

                    if (empty($productName) || $qty < 1) {
                        $this->skipped++;
                        continue;
                    }

                    $product = Product::where('name', $productName)->orWhere('code', $productName)->first();
                    if (!$product) {
                        $product = Product::where('name', 'like', '%' . $productName . '%')->first();
                    }
                    if (!$product) {
                        $product = Product::where('code', $productName)->orWhere('code', 'like', '%' . $productName . '%')->first();
                    }

                    if (!$product) {
                        $this->skipped++;
                        continue;
                    }

                    $variant = $product->variants()->first();

                    $subtotal = ($qty * $price) - $discount;
                    $totalAmount += $subtotal;

                    $items[] = [
                        'product' => $product,
                        'variant_id' => $variant?->id,
                        'product_name' => $productName,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'subtotal' => $subtotal,
                    ];
                }

                if (empty($items)) {
                    $this->skipped += $group->count();
                    return;
                }

                $paymentStatus = !empty($first['payment_status'])
                    ? strtolower($first['payment_status'])
                    : 'paid';

                if (!in_array($paymentStatus, ['paid', 'unpaid', 'credit', 'pending', 'canceled'])) {
                    $paymentStatus = 'paid';
                }

                $transaction = Transaction::create([
                    'transaction_code' => $transactionCode,
                    'user_id' => $this->userId,
                    'warehouse_id' => $this->warehouseId,
                    'customer_name' => $first['customer_name'] ?? null,
                    'customer_phone' => $first['customer_phone'] ?? null,
                    'source' => $first['source'] ?? 'import',
                    'payment_status' => $paymentStatus,
                    'payment_method' => $first['payment_method'] ?? 'cash',
                    'notes' => $first['notes'] ?? 'Import Excel',
                    'total_amount' => $totalAmount,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product']->id,
                        'product_variant_id' => $item['variant_id'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'discount' => $item['discount'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                $this->imported++;
            });
        }
    }

    public function getImportedCount()
    {
        return $this->imported;
    }

    public function getSkippedCount()
    {
        return $this->skipped;
    }

    protected function parseDate($value)
    {
        if (empty($value)) return Carbon::now();

        try {
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays((int)$value - 2);
            }
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e2) {
                return Carbon::now();
            }
        }
    }
}

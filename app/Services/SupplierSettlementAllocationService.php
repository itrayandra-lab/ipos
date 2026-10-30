<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupplierSettlementAllocationService
{
    public function allocations(bool $forUpdate = false, array $productIds = []): Collection
    {
        $paidQuantities = DB::table('supplier_payment_item_allocations')
            ->select('transaction_item_id')
            ->selectRaw('SUM(quantity) as paid_quantity')
            ->groupBy('transaction_item_id');

        $salesQuery = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->leftJoin('product_batches', 'transaction_items.product_batch_id', '=', 'product_batches.id')
            ->leftJoin('purchase_order_items as transaction_po_items', 'transaction_items.purchase_order_item_id', '=', 'transaction_po_items.id')
            ->leftJoin('purchase_orders as transaction_pos', 'transaction_po_items.purchase_order_id', '=', 'transaction_pos.id')
            ->leftJoin('goods_receipt_items', 'product_batches.goods_receipt_item_id', '=', 'goods_receipt_items.id')
            ->leftJoin('purchase_order_items as batch_po_items', 'product_batches.purchase_order_item_id', '=', 'batch_po_items.id')
            ->leftJoin('purchase_orders as batch_pos', 'batch_po_items.purchase_order_id', '=', 'batch_pos.id')
            ->leftJoin('purchase_order_items as receipt_po_items', 'goods_receipt_items.purchase_order_item_id', '=', 'receipt_po_items.id')
            ->leftJoin('purchase_orders as receipt_pos', 'receipt_po_items.purchase_order_id', '=', 'receipt_pos.id')
            ->leftJoinSub($paidQuantities, 'paid_allocations', function ($join) {
                $join->on('paid_allocations.transaction_item_id', '=', 'transaction_items.id');
            })
            ->leftJoin('suppliers as batch_suppliers', 'batch_suppliers.id', '=', 'product_batches.supplier_id')
            ->leftJoin('suppliers as product_suppliers', 'product_suppliers.id', '=', 'products.supplier_id')
            ->whereNull('transaction_items.supplier_payment_id')
            ->where(function ($query) {
                $query->where('products.is_bundle', 0)
                    ->orWhereNotNull('transaction_items.parent_item_id');
            })
            ->when($productIds, fn($query) => $query->whereIn('transaction_items.product_id', $productIds))
            ->select([
                'transaction_items.id as transaction_item_id',
                'transactions.id as transaction_id',
                'transactions.transaction_code',
                'transactions.invoice_number',
                DB::raw('DATE(COALESCE(transactions.transaction_date, transactions.created_at)) as transaction_date'),
                'transaction_items.product_id',
                'transaction_items.product_variant_id',
                'transaction_items.qty',
                'transaction_items.buy_price as transaction_buy_price',
                'products.name as product_name',
                'merek.name as merek_name',
                'product_variants.variant_name',
                'product_variants.sku_code',
                DB::raw('COALESCE(product_nettos.netto_value, (SELECT pn.netto_value FROM product_nettos pn WHERE pn.product_id = products.id ORDER BY pn.id LIMIT 1)) as netto_value'),
                DB::raw('COALESCE(product_nettos.satuan, (SELECT pn2.satuan FROM product_nettos pn2 WHERE pn2.product_id = products.id ORDER BY pn2.id LIMIT 1)) as satuan'),
                DB::raw('COALESCE(transaction_po_items.purchase_order_id, batch_pos.id, receipt_pos.id) as source_purchase_order_id'),
                DB::raw('COALESCE(product_batches.supplier_id, transaction_pos.supplier_id, batch_pos.supplier_id, receipt_pos.supplier_id, products.supplier_id) as supplier_id'),
                DB::raw('COALESCE(batch_suppliers.name, transaction_pos_supplier.name, batch_pos_supplier.name, receipt_pos_supplier.name, product_suppliers.name) as supplier_name'),
                DB::raw('COALESCE(NULLIF(transaction_items.buy_price, 0), NULLIF(product_batches.buy_price, 0), product_variants.product_hpp, 0) as source_buy_price'),
                DB::raw('CASE WHEN transaction_items.qty > COALESCE(paid_allocations.paid_quantity, 0) THEN transaction_items.qty - COALESCE(paid_allocations.paid_quantity, 0) ELSE 0 END as remaining_qty'),
            ])
            ->leftJoin('suppliers as transaction_pos_supplier', 'transaction_pos_supplier.id', '=', 'transaction_pos.supplier_id')
            ->leftJoin('suppliers as batch_pos_supplier', 'batch_pos_supplier.id', '=', 'batch_pos.supplier_id')
            ->leftJoin('suppliers as receipt_pos_supplier', 'receipt_pos_supplier.id', '=', 'receipt_pos.supplier_id')
            ->orderByRaw('COALESCE(transactions.transaction_date, transactions.created_at) ASC')
            ->orderBy('transaction_items.id');

        if ($forUpdate) {
            $salesQuery->lockForUpdate();
        }

        $sales = $salesQuery->get()->filter(fn($sale) => (float) $sale->remaining_qty > 0.0001)->values();

        $receivedQuantities = DB::table('goods_receipt_items')
            ->select('purchase_order_item_id')
            ->selectRaw('SUM(quantity_received) as received_qty')
            ->whereNotNull('purchase_order_item_id')
            ->groupBy('purchase_order_item_id');

        $purchaseOrdersQuery = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->leftJoinSub($receivedQuantities, 'received', function ($join) {
                $join->on('received.purchase_order_item_id', '=', 'purchase_order_items.id');
            })
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->whereNotIn('purchase_orders.status', ['draft', 'submitted', 'cancelled'])
            ->when($productIds, fn($query) => $query->whereIn('purchase_order_items.product_id', $productIds))
            ->select([
                'purchase_order_items.id as purchase_order_item_id',
                'purchase_order_items.product_id',
                'purchase_order_items.unit_price',
                'purchase_order_items.paid_qty',
                'purchase_orders.id as purchase_order_id',
                'purchase_orders.supplier_id',
                'purchase_orders.po_number',
                'purchase_orders.po_date',
                'suppliers.name as supplier_name',
                DB::raw('COALESCE(received.received_qty, 0) as received_qty'),
            ])
            ->whereRaw('COALESCE(received.received_qty, 0) > purchase_order_items.paid_qty')
            ->orderByRaw('COALESCE(purchase_orders.po_date, ?) ASC', ['9999-12-31'])
            ->orderBy('purchase_orders.id')
            ->orderBy('purchase_order_items.id');

        if ($forUpdate) {
            $purchaseOrdersQuery->lockForUpdate();
        }

        $purchaseOrders = $purchaseOrdersQuery->get();
        $purchaseOrderPools = [];
        foreach ($purchaseOrders as $purchaseOrderItem) {
            $key = $purchaseOrderItem->product_id . '|' . $purchaseOrderItem->supplier_id;
            $purchaseOrderPools[$key][] = [
                'item' => $purchaseOrderItem,
                'remaining_qty' => max(0, (float) $purchaseOrderItem->received_qty - (float) $purchaseOrderItem->paid_qty),
            ];
        }

        $poolOffsets = [];
        $allocations = collect();
        foreach ($sales as $sale) {
            $remainingQty = (float) $sale->remaining_qty;
            $supplierId = $sale->supplier_id;
            $poolKey = $sale->product_id . '|' . ($supplierId ?? 'null');
            $pool = $purchaseOrderPools[$poolKey] ?? [];
            $poolOffset = $poolOffsets[$poolKey] ?? 0;

            while ($remainingQty > 0.0001 && $poolOffset < count($pool)) {
                if ($pool[$poolOffset]['remaining_qty'] <= 0.0001) {
                    $poolOffset++;
                    continue;
                }

                $purchaseOrderItem = $pool[$poolOffset]['item'];
                $allocatedQty = min($remainingQty, $pool[$poolOffset]['remaining_qty']);
                $unitPrice = (float) $purchaseOrderItem->unit_price > 0
                    ? (float) $purchaseOrderItem->unit_price
                    : (float) $sale->source_buy_price;

                $allocations->push((object) [
                    'transaction_item_id' => $sale->transaction_item_id,
                    'transaction_id' => $sale->transaction_id,
                    'transaction_code' => $sale->transaction_code,
                    'invoice_number' => $sale->invoice_number,
                    'transaction_date' => $sale->transaction_date,
                    'product_id' => $sale->product_id,
                    'product_variant_id' => $sale->product_variant_id,
                    'product_name' => $sale->product_name,
                    'merek_name' => $sale->merek_name,
                    'variant_name' => $sale->variant_name,
                    'sku_code' => $sale->sku_code,
                    'supplier_id' => $purchaseOrderItem->supplier_id,
                    'supplier_name' => $purchaseOrderItem->supplier_name,
                    'purchase_order_item_id' => $purchaseOrderItem->purchase_order_item_id,
                    'purchase_order_id' => $purchaseOrderItem->purchase_order_id,
                    'purchase_order_supplier_id' => $purchaseOrderItem->supplier_id,
                    'po_number' => $purchaseOrderItem->po_number,
                    'buy_price' => $unitPrice,
                    'allocated_qty' => $allocatedQty,
                    'total_hpp' => round($allocatedQty * $unitPrice, 2),
                ]);

                $remainingQty -= $allocatedQty;
                $pool[$poolOffset]['remaining_qty'] -= $allocatedQty;
                if ($pool[$poolOffset]['remaining_qty'] <= 0.0001) {
                    $poolOffset++;
                }
            }

            if ($remainingQty > 0.0001) {
                $allocations->push((object) [
                    'transaction_item_id' => $sale->transaction_item_id,
                    'transaction_id' => $sale->transaction_id,
                    'transaction_code' => $sale->transaction_code,
                    'invoice_number' => $sale->invoice_number,
                    'transaction_date' => $sale->transaction_date,
                    'product_id' => $sale->product_id,
                    'product_variant_id' => $sale->product_variant_id,
                    'product_name' => $sale->product_name,
                    'merek_name' => $sale->merek_name,
                    'variant_name' => $sale->variant_name,
                    'sku_code' => $sale->sku_code,
                    'supplier_id' => $supplierId,
                    'supplier_name' => $sale->supplier_name,
                    'purchase_order_item_id' => null,
                    'purchase_order_id' => null,
                    'purchase_order_supplier_id' => null,
                    'po_number' => null,
                    'buy_price' => (float) $sale->source_buy_price,
                    'allocated_qty' => $remainingQty,
                    'total_hpp' => round($remainingQty * (float) $sale->source_buy_price, 2),
                ]);
            }

            $purchaseOrderPools[$poolKey] = $pool;
            $poolOffsets[$poolKey] = $poolOffset;
        }

        return $allocations;
    }
}

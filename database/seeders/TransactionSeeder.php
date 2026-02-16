<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [
            [
                'user_id' => 2,
                'total_amount' => 3600000,
                'payment_status' => 'paid',
                'delivery_type' => 'pickup',
                'delivery_desc' => 'Picked up at store',
                'midtrans_order_id' => 'ORDER-001',
                'midtrans_transaction_id' => 'TXN-001',
                'source' => 'web',
            ],
            [
                'user_id' => 2,
                'total_amount' => 7550000,
                'payment_status' => 'pending',
                'delivery_type' => 'delivery',
                'delivery_desc' => 'Sent to customer address',
                'midtrans_order_id' => 'ORDER-002',
                'midtrans_transaction_id' => 'TXN-002',
                'source' => 'web',
            ],
        ];

        foreach ($transactions as $trx) {
            Transaction::create($trx);
        }
    }
}

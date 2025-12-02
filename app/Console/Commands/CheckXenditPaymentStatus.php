<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class CheckXenditPaymentStatus extends Command
{
    protected $signature = 'payment:check-xendit';
    protected $description = 'Cek status pembayaran Xendit secara berkala';

    public function handle()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        $invoiceApi = new InvoiceApi();

        $pendingTransactions = Transaction::where('payment_status', 'pending')->whereDate('created_at', '>=', now()->subDay())->get();
        Log::info($pendingTransactions);

        foreach ($pendingTransactions as $trx) {

            if (!$trx->midtrans_order_id) {
                continue;
            }

            try {
                Log::info('info order id', [$trx->midtrans_order_id]);
                $invoice = $invoiceApi->getInvoiceById($trx->midtrans_order_id);
                Log::info('info order id by xendit', [$invoice]);
                $status = $invoice['status']; 

                if ($status === 'PAID') {
                    $trx->update(['payment_status' => 'paid']);
                    $this->info("Order {$trx->external_id} updated to PAID");
                }

                if ($status === 'EXPIRED') {
                    $trx->update(['payment_status' => 'failed']);
                    $this->info("Order {$trx->external_id} updated to FAILED");
                }

            } catch (\Exception $e) {
                $this->error("Error checking invoice {$trx->external_id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}

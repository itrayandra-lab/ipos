<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillBatchHpp extends Command
{
    protected $signature = 'batch:backfill-hpp';
    protected $description = 'Isi buy_price product_batches yang kosong dari product_variants.product_hpp';

    public function handle()
    {
        $updated = 0;
        $skipped = 0;

        $batches = DB::table('product_batches as pb')
            ->leftJoin('product_variants as pv', 'pb.product_variant_id', '=', 'pv.id')
            ->where(function ($q) {
                $q->whereNull('pb.buy_price')->orWhere('pb.buy_price', 0);
            })
            ->where('pv.product_hpp', '>', 0)
            ->select('pb.id', 'pv.product_hpp', 'pv.id as variant_id')
            ->get();

        foreach ($batches as $batch) {
            DB::table('product_batches')
                ->where('id', $batch->id)
                ->update(['buy_price' => $batch->product_hpp]);
            $updated++;
        }

        $totalZero = DB::table('product_batches')
            ->where(function ($q) {
                $q->whereNull('buy_price')->orWhere('buy_price', 0);
            })
            ->count();

        $this->info("{$updated} batch diperbarui dari product_variants.product_hpp.");
        $this->info("Sisa {$totalZero} batch masih kosong (tidak punya variant atau product_hpp=0).");

        return Command::SUCCESS;
    }
}

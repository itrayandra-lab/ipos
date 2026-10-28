<?php
require __DIR__ . '/../vendor/autoload.php';
$f = __DIR__ . '/../public/Order.all.20260810_20260909.xlsx';
$ss = \PhpOffice\PhpSpreadsheet\IOFactory::load($f);
$s = $ss->getActiveSheet();
function cellstr($c){
    $v = $c->getValue();
    if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) return trim($v->__toString());
    if ($v === null) return '';
    return trim((string)$v);
}
// headers
$highestCol = $s->getHighestColumn();
$highestRow = $s->getHighestRow();
$cols = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
$headers = [];
for ($c=1;$c<=$cols;$c++){
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    $headers[$col] = cellstr($s->getCell($col.'1'));
}
// map header name -> col
$hmap = array_flip($headers);
// helper to get value by header
function gv($row, $name, $hmap){ 
    if(!isset($hmap[$name])) return '';
    $col = $hmap[$name];
    return isset($row[$col]) ? $row[$col] : '';
}
function toNum($v){
    $v = trim((string)$v);
    if ($v === '' || $v === 'N' || $v === 'Y') return 0;
    // remove thousand separators (dots), keep digits
    $v = str_replace(['.', ' '], '', $v);
    $v = str_replace(',', '.', $v);
    if (!is_numeric($v)) return 0;
    return (float)$v;
}

// read rows
$rows = [];
for ($r=2;$r<=$highestRow;$r++){
    $row = [];
    $empty=true;
    for ($c=1;$c<=$cols;$c++){
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $val = cellstr($s->getCell($col.$r));
        $row[$col] = $val;
        if ($val !== '') $empty=false;
    }
    if ($empty) continue;
    $rows[] = $row;
}

// Save parsed CSV
$outDir = __DIR__;
$fp = fopen($outDir.'/orders_parsed.csv','w');
fputcsv($fp, array_values($headers));
foreach($rows as $row){ fputcsv($fp, array_values($row)); }
fclose($fp);

// Basic counts
$totalRows = count($rows);
$orders = [];
foreach($rows as $row){
    $orders[$row['A']][] = $row; // by order no
}
$uniqueOrders = count($orders);

// Status distribution
$statusCounts = [];
foreach($rows as $row){ $st=$row['B']; if($st==='')$st='(kosong)'; $statusCounts[$st]=(($statusCounts[$st]??0)+1); }
// but rows are line items; unique orders status
$orderStatus = [];
foreach($orders as $ono=>$items){ $orderStatus[$items[0]['B']] = ($orderStatus[$items[0]['B']]??0)+1; }

// Payment methods per order
$payCounts = [];
foreach($orders as $ono=>$items){ $m=$items[0]['M']; if($m==='')$m='(kosong)'; $payCounts[$m]=(($payCounts[$m]??0)+1); }

// Shipping option per order
$shipCounts = [];
foreach($orders as $ono=>$items){ $m=$items[0]['F']; if($m==='')$m='(kosong)'; $shipCounts[$m]=(($shipCounts[$m]??0)+1); }

// Cancel reasons
$cancelReasons = [];
foreach($orders as $ono=>$items){ $c=$items[0]['C']; if($c!=='') $cancelReasons[$c]=(($cancelReasons[$c]??0)+1); }
$cancelStatus = [];
foreach($orders as $ono=>$items){ $c=$items[0]['D']; if($c!=='') $cancelStatus[$c]=(($cancelStatus[$c]??0)+1); }

// Province per order
$provCounts = [];
$cityCounts = [];
foreach($orders as $ono=>$items){ 
    $p=$items[0]['AW']; if($p==='')$p='(kosong)'; $provCounts[$p]=(($provCounts[$p]??0)+1);
    $ci=$items[0]['AV']; if($ci==='')$ci='(kosong)'; $cityCounts[$ci]=(($cityCounts[$ci]??0)+1);
}

// Date range order created
$createdDates = [];
foreach($orders as $ono=>$items){ $createdDates[] = $items[0]['J']; }
sort($createdDates);
$minCreated = $createdDates[0] ?? '';
$maxCreated = end($createdDates) ?: '';

// Revenue totals (per order use Total Pembayaran AN; but multi-row same order -> same value, dedup by order)
$totalPayment = 0;
$totalSubtotal = 0;
$totalDiscount = 0;
$totalShippingBuyer = 0;
$totalShippingEst = 0;
$totalVoucherSeller = 0;
$totalVoucherShopee = 0;
$totalCod = 0;
foreach($orders as $ono=>$items){
    $it = $items[0];
    $totalPayment += toNum($it['AN']);
    $totalSubtotal += toNum($it['V']); // subtotal pesanan per line? Actually V is per line item subtotal. Sum all rows better.
    $totalDiscount += toNum($it['W']);
    $totalShippingBuyer += toNum($it['AK']);
    $totalShippingEst += toNum($it['AL']);
    $totalVoucherSeller += toNum($it['AC']);
    $totalVoucherShopee += toNum($it['AE']);
}
// line-level subtotal sum
$lineSubtotal = 0;
$lineQty = 0;
$lineOrigPrice = 0;
foreach($rows as $row){
    $lineSubtotal += toNum($row['V']);
    $lineQty += (int)toNum($row['T']);
    $lineOrigPrice += toNum($row['R'])*toNum($row['T']);
}
// Top products by quantity and revenue (line-level)
$prodQty = [];
$prodRev = [];
$prodOrders = [];
foreach($rows as $row){
    $name = $row['O'];
    if ($name==='') continue;
    // normalize product name maybe trim
    $prodQty[$name] = ($prodQty[$name]??0) + (int)toNum($row['T']);
    $prodRev[$name] = ($prodRev[$name]??0) + toNum($row['V']);
    $prodOrders[$name][$row['A']] = true;
}
arsort($prodQty);
arsort($prodRev);

// Top SKUs
$skuQty = [];
foreach($rows as $row){
    $sku = $row['N']; if($sku==='') $sku = $row['P']; if($sku==='') $sku='(no sku)';
    $skuQty[$sku] = ($skuQty[$sku]??0) + (int)toNum($row['T']);
}
arsort($skuQty);

// Top buyers
$buyerCounts = [];
$buyerRev = [];
foreach($orders as $ono=>$items){
    $b = $items[0]['AR'];
    if($b==='') $b='(unknown)';
    $buyerCounts[$b] = ($buyerCounts[$b]??0)+1;
    $buyerRev[$b] = ($buyerRev[$b]??0) + toNum($items[0]['AN']);
}
arsort($buyerCounts);
arsort($buyerRev);

// Unique products count
$uniqueProducts = count($prodQty);

// Payment method revenue
$payRev = [];
foreach($orders as $ono=>$items){
    $m=$items[0]['M']; if($m==='')$m='(kosong)';
    $payRev[$m] = ($payRev[$m]??0) + toNum($items[0]['AN']);
}
arsort($payRev);

// Status revenue
$statusRev = [];
foreach($orders as $ono=>$items){
    $st=$items[0]['B']; if($st==='')$st='(kosong)';
    $statusRev[$st] = ($statusRev[$st]??0) + toNum($items[0]['AN']);
}

// Daily order counts by created date (date part)
$daily = [];
foreach($orders as $ono=>$items){
    $d = substr($items[0]['J'],0,10);
    $daily[$d] = ($daily[$d]??0)+1;
}
ksort($daily);

// City counts sort
arsort($cityCounts);
arsort($provCounts);

// Output report
$r = [];
$r[] = "=== ANALISIS FILE: Order.all.20260810_20260909.xlsx ===";
$r[] = "Sheet: orders | Rows data: $totalRows | Unique orders: $uniqueOrders";
$r[] = "Periode (Waktu Pesanan Dibuat): $minCreated s/d $maxCreated";
$r[] = "";
$r[] = "--- STATUS PESANAN (per order) ---";
foreach($orderStatus as $k=>$v){ $r[] = "  $k: $v (".round($v/$uniqueOrders*100,1)."%)  revenue=".number_format($statusRev[$k]??0,0,',','.'); }
$r[] = "";
$r[] = "--- METODE PEMBAYARAN (per order) ---";
foreach($payCounts as $k=>$v){ $r[] = "  $k: $v  revenue=".number_format($payRev[$k]??0,0,',','.'); }
$r[] = "";
$r[] = "--- OPSI PENGIRIMAN (per order) ---";
foreach($shipCounts as $k=>$v){ $r[] = "  $k: $v"; }
$r[] = "";
$r[] = "--- ALASAN PEMBATALAN ---";
if(empty($cancelReasons)) $r[]="  (tidak ada)"; else foreach($cancelReasons as $k=>$v){ $r[]="  $k: $v"; }
$r[] = "--- STATUS PEMBATALAN/PENGEMBALIAN ---";
if(empty($cancelStatus)) $r[]="  (tidak ada)"; else foreach($cancelStatus as $k=>$v){ $r[]="  $k: $v"; }
$r[] = "";
$r[] = "--- FINANSIAL (seluruh order) ---";
$r[] = "  Total Pembayaran (sum AN): ".number_format($totalPayment,0,',','.');
$r[] = "  Subtotal line items (sum V): ".number_format($lineSubtotal,0,',','.');
$r[] = "  Total Diskon (sum W, per order): ".number_format($totalDiscount,0,',','.');
$r[] = "  Ongkir dibayar pembeli (sum AK): ".number_format($totalShippingBuyer,0,',','.');
$r[] = "  Estimasi potongan ongkir (sum AL): ".number_format($totalShippingEst,0,',','.');
$r[] = "  Voucher penjual (sum AC): ".number_format($totalVoucherSeller,0,',','.');
$r[] = "  Voucher Shopee (sum AE): ".number_format($totalVoucherShopee,0,',','.');
$r[] = "  Total qty terjual (sum T): ".number_format($lineQty,0,',','.');
$r[] = "  Jumlah line item: ".count($rows);
$r[] = "  Produk unik: $uniqueProducts";
$r[] = "  Rata-rata nilai order: ".number_format($uniqueOrders? $totalPayment/$uniqueOrders : 0,0,',','.');
$r[] = "  Rata-rata item per order: ".round(count($rows)/$uniqueOrders,2);
$r[] = "";
$r[] = "--- TOP 15 PRODUK by QTY ---";
$i=0;
foreach($prodQty as $name=>$q){
    if($i++>=15) break;
    $rev = $prodRev[$name]??0;
    $ords = count($prodOrders[$name]??[]);
    $r[] = "  ".($i).". ".mb_substr($name,0,60)." | qty=$q | revenue=".number_format($rev,0,',','.')." | orders=$ords";
}
$r[] = "";
$r[] = "--- TOP 15 PRODUK by REVENUE ---";
$i=0;
$sortedRev = $prodRev; arsort($sortedRev);
foreach($sortedRev as $name=>$rev){
    if($i++>=15) break;
    $q = $prodQty[$name]??0;
    $r[] = "  ".($i).". ".mb_substr($name,0,60)." | revenue=".number_format($rev,0,',','.')." | qty=$q";
}
$r[] = "";
$r[] = "--- TOP 15 SKU by QTY ---";
$i=0;
foreach($skuQty as $sku=>$q){
    if($i++>=15) break;
    $r[] = "  ".($i).". $sku | qty=$q";
}
$r[] = "";
$r[] = "--- TOP 10 PROVINSI ---";
$i=0;
foreach($provCounts as $p=>$c){
    if($i++>=10) break;
    $r[] = "  ".($i).". $p: $c order";
}
$r[] = "";
$r[] = "--- TOP 15 KOTA/KAB ---";
$i=0;
foreach($cityCounts as $c=>$n){
    if($i++>=15) break;
    $r[] = "  ".($i).". $c: $n order";
}
$r[] = "";
$r[] = "--- TOP 10 PEMBELI by jumlah order ---";
$i=0;
foreach($buyerCounts as $b=>$n){
    if($i++>=10) break;
    $r[] = "  ".($i).". $b: $n order | revenue=".number_format($buyerRev[$b]??0,0,',','.');
}
$r[] = "";
$r[] = "--- ORDER PER HARI (created) ---";
foreach($daily as $d=>$n){ $r[] = "  $d: $n"; }

file_put_contents($outDir.'/analysis_report.txt', implode("\n",$r));
echo implode("\n",$r);

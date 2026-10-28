<?php
require __DIR__ . '/../vendor/autoload.php';
$f = __DIR__ . '/../public/Order.all.20260810_20260909.xlsx';
$ss = \PhpOffice\PhpSpreadsheet\IOFactory::load($f);
$s = $ss->getActiveSheet();
function cellstr($c){ $v=$c->getValue(); if($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) return trim($v->__toString()); if($v===null) return ''; return trim((string)$v); }
$highestCol=$s->getHighestColumn(); $highestRow=$s->getHighestRow();
$cols=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
$headers=[]; for($c=1;$c<=$cols;$c++){ $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c); $headers[$col]=cellstr($s->getCell($col.'1')); }
$hmap=array_flip($headers);
function toNum($v){ $v=trim((string)$v); if($v===''||$v==='N'||$v==='Y') return 0; $v=str_replace(['.',' '],'',$v); $v=str_replace(',','.',$v); if(!is_numeric($v)) return 0; return (float)$v; }
$rows=[]; for($r=2;$r<=$highestRow;$r++){ $row=[]; $empty=true; for($c=1;$c<=$cols;$c++){ $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c); $val=cellstr($s->getCell($col.$r)); $row[$col]=$val; if($val!=='') $empty=false; } if($empty) continue; $rows[]=$row; }
$orig=0; $disc=0; $after=0;
foreach($rows as $row){
  $orig += toNum($row['R'])*toNum($row['T']);
  $disc += toNum($row['W']);
  $after += toNum($row['V']);
}
echo "Original price sum (R*T): ".number_format($orig,0,',','.')."\n";
echo "Discount sum (W): ".number_format($disc,0,',','.')."\n";
echo "After-discount subtotal sum (V): ".number_format($after,0,',','.')."\n";
echo "orig - disc = ".number_format($orig-$disc,0,',','.')."\n";
// check W vs (R-S)*T
$mismatch=0;
foreach($rows as $row){
  $calc = (toNum($row['R'])-toNum($row['S']))*toNum($row['T']);
  if (abs($calc - toNum($row['W'])) > 1) $mismatch++;
}
echo "Rows where W != (R-S)*T: $mismatch\n";
// shipping AK distribution
$akVals=[]; foreach($rows as $row){ $akVals[$row['AK']]=($akVals[$row['AK']]??0)+1; }
echo "AK distinct: "; print_r($akVals);
// check Status Pesanan distinct
$st=[]; foreach($rows as $row){ $st[$row['B']]=1; } echo "Statuses: ".implode(', ',array_keys($st))."\n";
// check Tipe Pesanan L
$tp=[]; foreach($rows as $row){ if($row['L']!=='') $tp[$row['L']]=1; } echo "Tipe Pesanan: ".implode(', ',array_keys($tp))."\n";
// check Metode Pembayaran distinct
$mp=[]; foreach($rows as $row){ $mp[$row['M']]=1; } echo "Pay methods: ".implode(', ',array_keys($mp))."\n";
// check Opsi Pengiriman distinct count
echo "Shipping options: ".count(array_unique(array_column($rows,'F')))."\n";
// date completion AX
$completed=[]; foreach($rows as $row){ if($row['AX']!=='') $completed[$row['A']]=1; } 
echo "Orders with completion time: ".count($completed)."\n";
// delivery time calc sample
echo "H sample: ".$rows[0]['H']." I sample: ".$rows[0]['I']."\n";
// check if there are rows with returned quantity >0
$ret=0; foreach($rows as $row){ if(toNum($row['U'])>0) $ret++; } echo "Rows with returned qty>0: $ret\n";
// check Paket Diskon values
$pk=[]; foreach($rows as $row){ if($row['AF']!=='') $pk[$row['AF']]=($pk[$row['AF']]??0)+1; } echo "Paket Diskon (AF): "; print_r($pk);

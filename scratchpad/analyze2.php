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
function gv($row,$name,$hmap){ if(!isset($hmap[$name])) return ''; $col=$hmap[$name]; return isset($row[$col])?$row[$col]:''; }
function toNum($v){ $v=trim((string)$v); if($v===''||$v==='N'||$v==='Y') return 0; $v=str_replace(['.',' '],'',$v); $v=str_replace(',','.',$v); if(!is_numeric($v)) return 0; return (float)$v; }
$rows=[]; for($r=2;$r<=$highestRow;$r++){ $row=[]; $empty=true; for($c=1;$c<=$cols;$c++){ $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c); $val=cellstr($s->getCell($col.$r)); $row[$col]=$val; if($val!=='') $empty=false; } if($empty) continue; $rows[]=$row; }
$orders=[]; foreach($rows as $row){ $orders[$row['A']][]=$row; }

// line-level sums
$sumW=0;$sumX=0;$sumY=0;$sumV=0;$sumT=0;$sumZ=0;$sumAB=0;$sumAC=0;$sumAD=0;$sumAE=0;$sumAF=0;$sumAG=0;$sumAH=0;$sumAI=0;$sumAJ=0;$sumAK=0;$sumAL=0;$sumAM=0;$sumANline=0;$sumAO=0;
foreach($rows as $row){
  $sumW+=toNum($row['W']); $sumX+=toNum($row['X']); $sumY+=toNum($row['Y']);
  $sumV+=toNum($row['V']); $sumT+=(int)toNum($row['T']); 
  $sumAC+=toNum($row['AC']); $sumAD+=toNum($row['AD']); $sumAE+=toNum($row['AE']);
  $sumAJ+=toNum($row['AJ']); $sumAK+=toNum($row['AK']); $sumAL+=toNum($row['AL']); $sumAM+=toNum($row['AM']); $sumAO+=toNum($row['AO']);
}
// order-level sums (first row per order)
$sumAN=0;$sumAKo=0;$sumALo=0;$sumACo=0;$sumAEo=0;$sumAOo=0;$sumShippingEst=0;
foreach($orders as $ono=>$items){ $it=$items[0];
  $sumAN+=toNum($it['AN']); $sumAKo+=toNum($it['AK']); $sumALo+=toNum($it['AL']);
  $sumACo+=toNum($it['AC']); $sumAEo+=toNum($it['AE']); $sumAOo+=toNum($it['AO']);
}
// brand grouping
$brandQty=[]; $brandRev=[];
foreach($rows as $row){
  $name=$row['O'];
  // brand = first token before space? Most names start with brand uppercase
  $brand = preg_match('/^([A-Z]+(?:LATORY|DERM|UMA)?)/', $name, $m) ? $m[1] : explode(' ',$name)[0];
  // refine: known brands
  foreach(['BABYLATORY','MOMMYLATORY','BEAUTYLATORY','DERMOND','UMADERM','MOMMYALTORY'] as $b){ if(stripos($name,$b)===0){ $brand=$b; break; } }
  if(stripos($name,'SAM SUN')===0) $brand='SAM SUN AND MOON';
  $brandQty[$brand]=($brandQty[$brand]??0)+(int)toNum($row['T']);
  $brandRev[$brand]=($brandRev[$brand]??0)+toNum($row['V']);
}
arsort($brandQty);
// shipping option cost: sum AO per option
$shipCost=[];
foreach($orders as $ono=>$items){ $it=$items[0]; $opt=$it['F']; $shipCost[$opt]=($shipCost[$opt]??0)+toNum($it['AO']); }
arsort($shipCost);
// payment revenue share
// cancelled orders value (AN)
$cancelVal=0; foreach($orders as $ono=>$items){ if($items[0]['B']==='Batal') $cancelVal+=toNum($items[0]['AN']); }
// unique buyers
$buyers=[]; foreach($orders as $ono=>$items){ $buyers[$items[0]['AR']]=true; }
// multi-item orders
$multi=0; foreach($orders as $ono=>$items){ if(count($items)>1) $multi++; }
// max items in an order
$maxItems=0; foreach($orders as $ono=>$items){ $maxItems=max($maxItems,count($items)); }
// weight
$sumWeightStr=''; 
// top product combos? orders with >1 line
$comboOrders=[]; foreach($orders as $ono=>$items){ if(count($items)>1){ $names=[]; foreach($items as $it){ $names[]=mb_substr($it['O'],0,25); } $comboOrders[]=implode(' + ',$names); } }

$r=[];
$r[]="=== ANALISIS LANJUTAN ===";
$r[]="Line-level: rows=$sumV? no";
$r[]="Sum V (line subtotal): ".number_format($sumV,0,',','.');
$r[]="Sum W (line diskon): ".number_format($sumW,0,',','.');
$r[]="Sum X (diskon penjual): ".number_format($sumX,0,',','.');
$r[]="Sum Y (diskon shopee): ".number_format($sumY,0,',','.');
$r[]="Sum T (qty): ".number_format($sumT,0,',','.');
$r[]="Sum AC (voucher penjual, line): ".number_format($sumAC,0,',','.');
$r[]="Sum AD (cashback koin): ".number_format($sumAD,0,',','.');
$r[]="Sum AE (voucher shopee, line): ".number_format($sumAE,0,',','.');
$r[]="Sum AJ (diskon kartu kredit): ".number_format($sumAJ,0,',','.');
$r[]="Sum AK (ongkir pembeli, line): ".number_format($sumAK,0,',','.');
$r[]="Sum AL (est potongan ongkir, line): ".number_format($sumAL,0,',','.');
$r[]="Sum AM (ongkir retur): ".number_format($sumAM,0,',','.');
$r[]="Sum AO (perkiraan ongkir, line): ".number_format($sumAO,0,',','.');
$r[]="";
$r[]="Order-level: sum AN (total bayar): ".number_format($sumAN,0,',','.');
$r[]="Sum AK (ongkir pembeli): ".number_format($sumAKo,0,',','.');
$r[]="Sum AL (est potongan ongkir): ".number_format($sumALo,0,',','.');
$r[]="Sum AC (voucher penjual): ".number_format($sumACo,0,',','.');
$r[]="Sum AE (voucher shopee): ".number_format($sumAEo,0,',','.');
$r[]="Sum AO (perkiraan ongkir): ".number_format($sumAOo,0,',','.');
$r[]="";
$r[]="Pembeli unik: ".count($buyers)." | Order multi-item: $multi | Max item dalam 1 order: $maxItems";
$r[]="Order batal nilai AN: ".number_format($cancelVal,0,',','.');
$r[]="";
$r[]="--- BRAND MIX (by qty / revenue) ---";
foreach($brandQty as $b=>$q){ $r[]="  $b: qty=$q | revenue=".number_format($brandRev[$b]??0,0,',','.'); }
$r[]="";
$r[]="--- BIAYA KIRIM per opsi (sum perkiraan ongkir AO) ---";
foreach($shipCost as $opt=>$c){ $r[]="  $opt: ".number_format($c,0,',','.'); }
$r[]="";
$r[]="--- ORDER MULTI-ITEM (combo) ---";
foreach($comboOrders as $c){ $r[]="  $c"; }

file_put_contents(__DIR__.'/analysis_report2.txt', implode("\n",$r));
echo implode("\n",$r);

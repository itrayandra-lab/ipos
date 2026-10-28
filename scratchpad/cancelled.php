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
$rows=[]; for($r=2;$r<=$highestRow;$r++){ $row=[]; $empty=true; for($c=1;$c<=$cols;$c++){ $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c); $val=cellstr($s->getCell($col.$r)); $row[$col]=$val; if($val!=='') $empty=false; } if($empty) continue; $rows[]=$row; }
$orders=[]; foreach($rows as $row){ $orders[$row['A']][]=$row; }
foreach($orders as $ono=>$items){
  if($items[0]['B']==='Batal'){
    $it=$items[0];
    echo $ono." | ".$it['J']." | ".$it['C']." | pay=".$it['M']." | prod=".$items[0]['O']." | total=".$it['AN']."\n";
  }
}
// completion lag for completed
$lags=[];
foreach($orders as $ono=>$items){
  $it=$items[0];
  if($it['B']==='Selesai' && $it['J']!=='' && $it['AX']!==''){
    $t1=strtotime($it['J']); $t2=strtotime($it['AX']);
    $lags[]=($t2-$t1)/86400;
  }
}
sort($lags);
echo "\nCompletion lag days: min=".round(min($lags),1)." max=".round(max($lags),1)." avg=".round(array_sum($lags)/count($lags),1)." median=".round($lags[floor(count($lags)/2)],1)."\n";
// completed date range
$cd=[]; foreach($orders as $ono=>$items){ if($items[0]['B']==='Selesai') $cd[]=$items[0]['AX']; } sort($cd);
echo "Completed from ".$cd[0]." to ".end($cd)."\n";
// unique buyers repeat
$bc=[]; foreach($orders as $ono=>$items){ $b=$items[0]['AR']; $bc[$b]=($bc[$b]??0)+1; }
$repeat=array_filter($bc,function($v){return $v>1;});
echo "Repeat buyers: ".count($repeat)." (".implode(', ',array_keys($repeat)).")\n";

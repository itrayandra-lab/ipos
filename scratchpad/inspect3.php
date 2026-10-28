<?php
require __DIR__ . '/../vendor/autoload.php';
$f = __DIR__ . '/../public/Order.all.20260810_20260909.xlsx';
$ss = \PhpOffice\PhpSpreadsheet\IOFactory::load($f);
$s = $ss->getActiveSheet();
function cellstr($c){
    $v = $c->getValue();
    if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) return $v->__toString();
    if ($v === null) return '';
    return (string)$v;
}
// show first data row key values plain
for ($r=2;$r<=2;$r++){
    for ($col='A';$col<='C';$col++){
        echo $col.': '.cellstr($s->getCell($col.$r))."\n";
    }
}
echo "toString test: ".get_class($s->getCell('A2')->getValue())."\n";
$v=$s->getCell('A2')->getValue();
echo "method exists: ".(method_exists($v,'__toString')?'yes':'no')."\n";
echo "str: ".$v."\n";

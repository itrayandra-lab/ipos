<?php
require __DIR__ . '/../vendor/autoload.php';
$f = __DIR__ . '/../public/Order.all.20260810_20260909.xlsx';
$ss = \PhpOffice\PhpSpreadsheet\IOFactory::load($f);
$s = $ss->getActiveSheet();
$highestCol = $s->getHighestColumn();
$highestRow = $s->getHighestRow();
echo "highestCol=$highestCol highestRow=$highestRow\n";
// headers row 1
$cols = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
echo "numCols=$cols\n";
$row1 = [];
for ($c=1;$c<=$cols;$c++){
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    $v = $s->getCell($col.'1')->getValue();
    $row1[$col] = $v;
}
echo "HEADERS:\n";
foreach ($row1 as $col=>$v){ echo "$col: ".var_export($v,true)."\n"; }
echo "\n--- first 3 data rows (row2-4) ---\n";
for ($r=2;$r<=4;$r++){
    echo "ROW $r:\n";
    for ($c=1;$c<=$cols;$c++){
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        $v = $s->getCell($col.$r)->getValue();
        if ($v !== null && $v !== '') echo "  $col: ".var_export($v,true)."\n";
    }
}

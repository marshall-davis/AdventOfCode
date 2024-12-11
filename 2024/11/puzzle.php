<?php

$inputStream = fopen('input.txt', 'r+');
$stones = explode(' ', trim(fgets($inputStream)));
echo implode(' ', $stones).PHP_EOL;
$blinks = 25;
for ($blink = 1; $blink <= $blinks; $blink++) {
    $transformed = [];
    $splits = 0;
    foreach ($stones as $position => $engraving) {
        if ($engraving === '0') {
            $transformed[$position + $splits] = 1;
        } elseif (($length = strlen($engraving)) % 2 === 0) {
            $first = substr($engraving, 0, $length / 2);
            $second = substr($engraving, ($length / 2));
            array_splice($transformed, $position + $splits, 1, [(string) (int) $first, (string) (int) $second]);
            ++$splits;
        } else {
            $transformed[$position + $splits] = $engraving * 2024;
        }
    }
    $stones = $transformed;
    echo "Blink $blink: ".count($stones).PHP_EOL;
//    echo implode(' ', $stones).PHP_EOL.PHP_EOL;
}

echo "FINAL: ".count($stones).PHP_EOL;


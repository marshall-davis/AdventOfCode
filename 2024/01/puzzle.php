<?php

$left = [];
$right = [];
$input = fopen('input.txt', 'r+');
while(!feof($input)) {
    $matches = [];
    preg_match('/(\d+) {3}(\d+)/', trim(fgets($input)), $matches);
    $left[] = $matches[1];
    $right[] = $matches[2];
}

$sum=0;
foreach ($left as $leftValue) {
    $arr =array_filter($right, fn (string $target) => $target===$leftValue);
    $multiplier = count($arr);
    $sum += $leftValue * $multiplier;
}

echo PHP_EOL.PHP_EOL.$sum.PHP_EOL;
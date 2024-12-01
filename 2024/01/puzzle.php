<?php

$left = [];
$right = [];
$input = fopen('input.txt', 'r+');
while(!feof($input)) {
    $matches = [];
    preg_match('/(\d+) {3}(\d+)/', trim(fgets($input)), $matches);
    $left[] = $matches[1];
    $right[] = $matches[2];
};

sort($left);
sort($right);

$sum = 0;
for ($i = 0; $i < count($left); $i++) {
    $sum += abs($left[$i]-$right[$i]);
}

echo PHP_EOL.PHP_EOL.$sum.PHP_EOL;
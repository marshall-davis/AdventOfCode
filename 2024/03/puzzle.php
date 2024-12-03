<?php

$input = fopen('input.txt', 'r+');
$memory = file_get_contents('input.txt');
$matches = [];

preg_match_all('/mul\((\d{1,3}),(\d{1,3})\)/', $memory, $matches);

$sum = 0;

foreach ($matches[1] as $occurrence => $match) {
    $sum += $match * $matches[2][$occurrence];
}

echo $sum. PHP_EOL;
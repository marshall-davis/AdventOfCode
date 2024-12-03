<?php

$input = fopen('input.txt', 'r+');
$memory = file_get_contents('input.txt');


$sum = 0;

$parts = explode('don\'t()', $memory);

$initial = array_shift($parts);
preg_match_all('/mul\((\d{1,3}),(\d{1,3})\)/', $initial, $matches);
foreach ($matches[1] as $occurrence => $match) {
    $sum += $match * $matches[2][$occurrence];
}
echo "Initially: $sum\n";
foreach ($parts as $part => $segment) {

    $matches = [];
    $start = strpos($segment, 'do()');
    if ($start === false) {
        continue;
    }
    $instruction = substr($segment, $start + 4);
    echo $instruction."\n\n";
    preg_match_all('/mul\((\d{1,3}),(\d{1,3})\)/', $instruction, $matches);
    foreach ($matches[1] as $occurrence => $match) {
        $sum += $match * $matches[2][$occurrence];
    }
}

assert(173529487 >= $sum);

echo $sum.PHP_EOL;
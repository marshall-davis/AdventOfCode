<?php

$input = fopen('input.txt', 'r+');
$safe = 0 ;
$unsafe = 0;
while (!feof($input)) {
    $readings = fgetcsv($input, separator: ' ');

    for($i = 0; $i < count($readings); $i++) {
        if (array_key_exists($i+1, $readings) && abs($readings[$i]  - $readings[$i+1]) > 3) {
            echo "Difference is too great. {$readings[$i]} and " . $readings[$i+1] . PHP_EOL;
            continue 2;
        }

        if (array_key_exists($i+1, $readings) && array_key_exists($i-1, $readings) && (($readings[$i-1] <=> $readings[$i]) !== ($readings[$i] <=> $readings[$i+1]))) {
            echo 'Trend change. ' . join(', ', [$readings[$i-1], $readings[$i], $readings[$i+1]]) . PHP_EOL;
            continue 2;
        }

        if (array_key_exists($i+1, $readings) && $readings[$i] == $readings[$i+1]) {
            echo "No change! {$readings[$i]} {$readings[$i+1]}" . PHP_EOL;
        }

    }

    ++$safe;

}

echo $safe . PHP_EOL;

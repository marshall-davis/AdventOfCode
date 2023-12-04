<?php
require_once __DIR__.'/../../common/numbers.php';
require_once __DIR__.'/../../common/strings.php';

$input = fopen('input.txt', 'r');
$calibrations = [];

$needles = [
    1,2,3,4,5,6,7,8,9,'one','two','three','four','five','six','seven','eight','nine'
];

while (!feof($input)) {
    $matches = [];
    $line = rtrim(fgets($input), "\r\n");
    echo $line.PHP_EOL;

    $firstDigit = wordToInteger(first_of($line, $needles));
    if ($firstDigit === null) {
        echo 'Skipped "'.$line.'"'.PHP_EOL;
        continue;
    }
    $secondDigit = wordToInteger(last_of($line, $needles)) ?? $firstDigit;
    $calibration=(int)($firstDigit.$secondDigit);
    echo $calibration.PHP_EOL;
    $calibrations[]=$calibration;

    echo PHP_EOL;
}

echo array_sum($calibrations).PHP_EOL;

<?php
$input = fopen('input.txt', 'r');
$calibrations = [];
while (!feof($input)) {
    $matches = [];
    $line = rtrim(fgets($input), "\r\n");
    echo $line.PHP_EOL;
    preg_match_all('/\d/', $line, $matches);
    $firstDigit = array_shift($matches[0]);
    if ($firstDigit === null) continue;
    $secondDigit = array_pop($matches[0]) ?? $firstDigit;
    $calibration=(int)($firstDigit.$secondDigit);
    echo $calibration.PHP_EOL;
    $calibrations[]=$calibration;

    echo PHP_EOL;
}

echo array_sum($calibrations).PHP_EOL;

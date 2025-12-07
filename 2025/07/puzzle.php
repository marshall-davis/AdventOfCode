<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$map = [];
while (!feof($input)) {
    $map[] = str_split(trim(fgets($input)));
}

$counter = array_pad([], count(array_shift($map)), 0);
$counter[70] = 1;

foreach ($map as $row) {
    foreach ($row as $column => $cell) {
        match ($cell) {
            '.' => $counter[$column],
            '^' => ($counter[$column-1] += $counter[$column]) && ($counter[$column+1] += $counter[$column]) && ($counter[$column] = 0),
        };
    }
}

echo number_format(array_sum($counter), thousands_separator: '') . PHP_EOL;

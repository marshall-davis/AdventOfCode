<?php

declare(strict_types=1);

$position = 50;

$input = fopen('input.txt', 'r+');

$hits = 0;

while (!feof($input)) {
    $step = [];
    preg_match('/([LR])(\d+)/', trim(fgets($input)), $step);
    [,$direction, $clicks] = $step;

    $position = match ($direction) {
        'L' => $position - ($clicks % 100),
        'R' => $position + ($clicks % 100),
    };

    echo "Moving $direction $clicks\n";

    if ($position > 99) {
        $position -= 100;
    } elseif ($position < 0) {
        $position = 100 + $position;
    }

    echo "Now at $position\n";



    if ($position === 0) {
        $hits++;
    }
}

echo $hits . PHP_EOL;

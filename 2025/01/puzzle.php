<?php

declare(strict_types=1);

$position = 50;

$input = fopen('input.txt', 'r+');

$hits = 0;

while (!feof($input)) {
    $step = [];
    preg_match('/([LR])(\d+)/', trim(fgets($input)), $step);
    [,$direction, $clicks] = $step;

    $startedAtZero = $position === 0;
    $passes = (int) ($clicks / 100);

    $position = match ($direction) {
        'L' => $position - ($clicks % 100),
        'R' => $position + ($clicks % 100),
    };

    echo "Moving $direction $clicks\n";
    if ($clicks >= 100) {
        echo "Spun $passes times.\n";
    }

    if ($position === 100) {
        $position = 0;
    }
    elseif ($position > 99) {
        $position -= 100;
        $passes++;
    } elseif ($position < 0) {
        $position = 100 + $position;
        if ($startedAtZero === false) {
            $passes++;
        }
    }

    if ($position === 0) {
//        if ($passes) {
//            $passes--;
//        }
        $hits++;
    }
    $hits += $passes;

    echo "Passed zero $passes times, $hits total.\n";
    echo "Now at $position\n";
}

if (in_array($hits, [6480, 7049, 6561, 6280])) {
    echo 'WRONG!'.PHP_EOL;
}

if ($hits <= 6067) {
    echo 'TOO LOW!'.PHP_EOL;
}

echo $hits . PHP_EOL;

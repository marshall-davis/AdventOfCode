<?php

$input = fopen('input.txt', 'r+');
$safe = 0;
$unsafe = 0;
$line = 0;

// This worked for Part 1, so assuming it checks out.
function checkReading(array $readings): ?int
{
    for ($i = 0; $i < count($readings); $i++) {
        if (array_key_exists($i + 1, $readings) && abs($readings[$i] - $readings[$i + 1]) > 3) {
            return $i;

        }

        if (array_key_exists($i + 1, $readings) && array_key_exists($i - 1,
                $readings) && (($readings[$i - 1] <=> $readings[$i]) !== ($readings[$i] <=> $readings[$i + 1]))) {
            return $i;
        }

        if (array_key_exists($i + 1, $readings) && $readings[$i] == $readings[$i + 1]) {
            return $i;
        }
    }
    return null;
}

function duplicateValues(array $readings): array
{
    return array_keys(array_filter(array_count_values($readings), fn (int $count) => $count > 1));
}

while (!feof($input)) {
    ++$line;
    $readings = fgetcsv($input, separator: ' ');

    // Does the simple check work?
    if (checkReading($readings) === null) {
        // Great, easy!
        ++$safe;
        continue;
    }

    for ($i = 0; $i < count($readings); $i++) {
        $test = $readings;
        array_splice($test, $i, 1);
        if (checkReading($test) === null) {
            // Great, easy!
            ++$safe;
            continue 2; // Safe version found, next reading!
        }
    }
}

if ($safe <= 488 || $safe >= 538) {
    echo 'WRONG'.PHP_EOL;
}
echo $safe.PHP_EOL;

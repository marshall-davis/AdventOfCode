<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$map = [];

while (!feof($input)) {
    $map[] = str_split(trim(fgets($input)));
}

function removeRolls(array $map): array
{
    return array_map(fn(array $line) => array_map(fn(string $cell) => $cell === 'X' ? '.' : $cell, $line), $map);
}

$removed = 0;
do {
    $accessible = 0;
    foreach ($map as $row => $line) {
        foreach ($line as $column => $cell) {
            if ($cell === '.') {
                continue;
            }

            $adjacent = 0;
//            echo "Working on $row,$column as $cell.\n";
            foreach ([-1, 1] as $offset) {
                $current = $line[$column + $offset] ?? null;
//            echo "\tOffset $offset $current\n";
                $adjacent += $current !== '@' ? 0 : 1; // Space to the right and right.

                $current = $map[$row + $offset][$column] ?? null;
//            echo "\tOffset $offset $current\n";
                $adjacent += $current !== '@' ? 0 : 1; // Space below and below.

                $current = $map[$row - 1][$column + $offset] ?? null;
//            echo "\tOffset $offset $current\n";
                $adjacent += $current !== '@' ? 0 : 1; // Space to Diagonal.

                $current = $map[$row + 1][$column + $offset] ?? null;
//            echo "\tOffset $offset $current\n";
                $adjacent += $current !== '@' ? 0 : 1; // Space to the lower left.
            }

            if ($adjacent < 4) {
                $map[$row][$column] = 'X';
                $accessible++;
            }
        }
    }
    $map = removeRolls($map);
    $removed += $accessible;
} while ($accessible !== 0);
echo "$removed rolls removed.".PHP_EOL;

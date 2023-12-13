<?php

const MULTIPLIER = 1000000;
$map = file('input.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$expanded = ['rows' => [], 'columns' => []];
foreach ($map as $index => $line) {
    if (!str_contains($line, '#')) {
        $expanded['rows'][] = $index;
    }
}
$map = array_map(fn(string $line) => str_split($line), $map);
for ($column = 0; $column < count($map[0]); $column++) {
    if (!in_array('#', array_column($map, $column))) {
        $expanded['columns'][] = $column;
    }
}

$galaxies = [];
foreach ($map as $line => $content) {
    foreach ($content as $character => $value) {
        if ($value === '#') {
            $galaxies[] = [$line, $character];
        }
    }
}

$distances = [];
foreach ($galaxies as $location) {
    foreach ($galaxies as $destination) {
        $verticalDistance = $destination[0] - $location[0];
        $horizontalDistance = $destination[1] - $location[1];
        /**
         * Add vertical multipliers by counting how many exist in the slice of the column.
         */
        $column = array_column($map, $location[1]);
        $columnSlice = array_slice($column, min($destination[0], $location[0]), abs($verticalDistance), true);
        $expandedRows = array_intersect(array_keys($columnSlice), $expanded['rows']);
        $verticalDistance = abs($verticalDistance) + ((count($expandedRows) * MULTIPLIER));
        /**
         * Add horizontal multipliers by counting how many exist in the slice of the row.
         */
        $row = $map[$location[0]];
        $rowSlice = array_slice($row, min($destination[1], $location[1]), abs($horizontalDistance), true);
        $expandedColumns = array_intersect(array_keys($rowSlice), $expanded['columns']);
        $horizontalDistance = abs($horizontalDistance) + ((count($expandedColumns) * MULTIPLIER));

        $distances[] = abs($verticalDistance) + abs($horizontalDistance) - count($expandedRows) - count($expandedColumns);
    }
}
echo count($galaxies) . " galaxies\n";
echo count($distances) . " distances.\n";
assert(count($galaxies)*count($galaxies) === count($distances));
echo "Expanded rows: ". implode(', ', $expanded['rows']).PHP_EOL;
echo "Expanded columns: ". implode(', ', $expanded['columns']).PHP_EOL;
$d = array_sum($distances) / 2;
assert($d == 483844716556);
echo "$d\n";

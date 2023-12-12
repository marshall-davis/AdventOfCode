<?php

$map = file('input.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
//$map = [
//'...#......',
//'.......#..',
//'#.........',
//'..........',
//'......#...',
//'.#........',
//'.........#',
//'..........',
//'.......#..',
//'#...#.....',
//];
$expanded = 0;
foreach ($map as $index => $line) {
    if (!str_contains($line, '#')) {
        echo "Expand Vertically from {$index}!\n";
        array_splice($map, $index + ++$expanded, 0, str_pad('', strlen($line), '.'));
    }
}
$map = array_map(fn(string $line) => str_split($line), $map);
$expanded = 0;
for ($column = 0; $column < count($map[0]); $column++) {
    if (!in_array('#', array_column($map, $column))) {
        echo "Expand Horizontally!\n";
        array_walk($map, fn (array &$line) => array_splice($line, $column + ++$expanded, 0, ['.']));
        $column++;
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
        $distances[] = $d = abs($location[0] - $destination[0]) + abs($location[1] - $destination[1]);
    }
}

$d = array_sum($distances) / 2;
assert($d < 10772649);
echo "$d\n";

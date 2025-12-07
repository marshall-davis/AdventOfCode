<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$map = [];
while (!feof($input)) {
    $map[] = str_split(trim(fgets($input)));
}

$beams = [array_find_key(array_shift($map), fn(string $cell) => $cell === 'S')];

$splits = 0;
foreach ($map as $row => $line) {
    foreach ($beams as $id => $beam) {
        if ($line[$beam] === '^') {
            // This is a split.
            $splits++;
            unset($beams[$id]);
            $beams[] = $beam - 1;
            $beams[] = $beam + 1;
        }
        $beams = array_unique($beams);
    }
    foreach ($beams as $splitBeams) {
        $map[$row][$splitBeams] = '|';
    }

    echo implode('', $map[$row]).PHP_EOL;
}


echo "There are $splits splits.\n";

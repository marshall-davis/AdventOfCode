<?php

$inputStream = fopen('input.txt', 'r+');
$line = fgets($inputStream);

function defrag($initial): int
{
    $map = '';
    $isFile = true;
    $file = 0;
    foreach (str_split($initial) as $size) {
        $id = $isFile ? $file++ : '.';
        echo "Padding with $id for $size\n";
        $map .= str_repeat($id.',', $size);
        $isFile = !$isFile;
    }

    $tick = 1;
    while (str_contains(rtrim($map, '.,'), '.')) {
        if (preg_match('/(\d+?,)(?:\.,)*$/', $map, $matches) === 0) {
            echo "Confused by matching.\n";
            break;
        };
        $map = substr_replace($map, '.,', strrpos($map, $matches[1]), strlen($matches[1]));
        $map = substr_replace($map, $matches[1], strpos($map, '.,'), 2);
        echo '.';
        if ($tick++ % 80 === 0) {
            echo "\n";
        }
    }
    echo "\n$tick iterations.\n";

    $checksum = 0;
    foreach (explode(',', $map) as $position => $id) {
        $checksum = bcadd($checksum, bcmul($position, (int) $id));
    }

    return $checksum;
}

// Verify Part One Works
assert(defrag('2333133121414131402') === 1928);

echo 'Checksum: ' . defrag($line).PHP_EOL;

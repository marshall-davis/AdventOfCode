<?php

$inputStream = fopen('input.txt', 'r+');
$line = fgets($inputStream);

function defrag($initial): int
{
    $map = '';
    $isFile = true;
    $files = [];
    foreach (str_split($initial) as $size) {
        if ($isFile) {
            $files[] = $size;
        }
        $id = $isFile ? array_key_last($files) : '.';
        $map .= str_repeat($id.',', $size);
        $isFile = !$isFile;
    }

    echo str_replace(',', '', $map).PHP_EOL;
    foreach(array_reverse($files, true) as $id=>$size) {
        echo "Moving $id if able.\n";
        $file = str_repeat("$id,", $size);
        $required= str_repeat('.,', $size);
        if (($firstBlock = strpos($map, $required)) && $firstBlock < strpos($map, $file)) {
            // Remove the file from existing location.
            $map=substr_replace($map, $required, strpos($map, $file), strlen($file));
            // Put file in place
            $map = substr_replace($map, $file, $firstBlock, strlen($required));
        }
    }

    $checksum = 0;
    foreach (explode(',', $map) as $position => $id) {
        $checksum = bcadd($checksum, bcmul($position, (int) $id));
    }

    return $checksum;
}

echo 'Checksum: ' . defrag($line).PHP_EOL;

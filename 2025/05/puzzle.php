<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$fresh = [];
$ingredients = [];
while ($range = trim(fgets($input))) {
    preg_match('/(\d+)-(\d+)/', $range, $parts);
    echo "$parts[1] - $parts[2]\n";
    $fresh[] = [$parts[1], $parts[2]];
}
while (!feof($input) && $id = intval(trim(fgets($input)))) {
    foreach($fresh as $range) {
        if ($id >= $range[0] && $id <= $range[1]) {
            $ingredients[] = $id;
            echo "$id is fresh.\n";
            break;
        }
    }
}

echo "There are ".count($ingredients)." ingredients.\n";

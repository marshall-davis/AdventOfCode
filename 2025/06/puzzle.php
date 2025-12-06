<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$lines = [];
while (!feof($input)) {
    $lines[] = array_values(array_filter(array_map('trim', explode(" ", trim(fgets($input))))));
}

$operators = array_values(array_pop($lines));
$results = [];
for($i = 0; $i < count($lines[0]); $i++) {
    $results[] = match($operators[$i]) {
        '+' => array_sum(array_column($lines, $i)),
        '*' => array_product(array_column($lines, $i)),
        default => throw new Exception('Invalid operator.')
    };
}

foreach ($results as $column =>  $result) {
    echo "Column $column: $result\n";
}

echo "Total: ".array_sum($results)."\n";

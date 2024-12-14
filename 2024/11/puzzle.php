<?php

$inputStream = fopen('input.txt', 'r+');
$stones = array_count_values(explode(' ', trim(fgets($inputStream))));
$blinks = 75;
for ($blink = 1; $blink <= $blinks; $blink++) {
    echo "Blink $blink\n";
    $iterator = new ArrayIterator($stones);
    $additions = [];
    while ($iterator->valid()) {
        $value = $iterator->key();
        if (!is_int($value)) {
            throw new RuntimeException();
        }
        if ($value == 0) {
            /**
             * Set the number of with a 1 engraving in $stones to be the number
             * of 0 engravings in $next plus any existing 1s.
             */
            $additions[1] = ($additions[1] ?? 0) + $stones[0];

        } elseif (($length = strlen($value)) % 2 === 0) {
            /**
             * Split the number into its two halves.
             */
            $first = (int) substr($value, 0, $length / 2);
            $second = (int) substr($value, ($length / 2));
            /**
             * Each one of those should be stored, plus any current with that value.
             */
            $additions[$first] = ($additions[$first] ?? 0) + $stones[$value];
            $additions[$second] = ($additions[$second] ?? 0) + $stones[$value];
        } else {
            $product = bcmul($value, 2024);
            $additions[$product] = ($additions[$product] ?? 0) + $stones[$value];
        }
        unset($stones[$value]);
        $iterator->next();
    }
    foreach ($additions as $value => $count) {
        $stones[$value] = ($stones[$value] ?? 0) + $count;
    }
}

echo "FINAL: ".array_sum($stones).PHP_EOL;


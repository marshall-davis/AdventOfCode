<?php

class Hash {
    public static function make(string $secret): int
    {
        $value = 0;
        foreach(str_split($secret) as $character) {
            $value += ord($character);
            $value *= 17;
            $value %= 256;
        }

        return $value;
    }
}

assert(52 === Hash::make('HASH'));


echo 'Sum: ' . array_sum(array_map(
    Hash::make(...),
        explode(',',trim(fgets(fopen('full.txt', 'r'))))
    )) . PHP_EOL;

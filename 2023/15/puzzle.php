<?php

class Hash
{
    public static function make(string $secret): int
    {
        $value = 0;
        foreach (str_split($secret) as $character) {
            $value += ord($character);
            $value *= 17;
            $value %= 256;
        }

        return $value;
    }
}

assert(52 === Hash::make('HASH'));

function removeLens(array &$box, string $lens): void
{
    unset($box[$lens]);
}

function addLens(array &$box, string $lens, int $focalLength): void
{
    $box[$lens] = $focalLength;
}

$boxes = [];
$steps = explode(',', trim(fgets(fopen('full.txt', 'r'))));
foreach ($steps as $step) {
    $parts = [];
    preg_match('/(?<label>\w+)(?<operation>[=-])(?<focal>\d*)/', $step, $parts);
    ['label' => $label, 'operation' => $operation, 'focal' => $focalLength] = $parts;
    $boxNumber = Hash::make($label);
    $box = $boxes[$boxNumber] ?? [];

    match ($operation) {
        '=' => addLens($box, $label, $focalLength),
        '-' => removeLens($box, $label),
        default => throw new RuntimeException("Unrecognized operator {$operation}.")
    };

    $boxes[$boxNumber] = $box;
}

$counter = 1;
$powers = [];
foreach ($boxes as $boxNumber => $box) {
    $power = 0;
    $box = array_values($box);
    for ($lens = 1; $lens <= count($box); $lens++) {
        $power += ($boxNumber+1)*$lens *$box[$lens-1];
    }
    $powers[] = $power;
}

echo "Focused: " . array_sum($powers).PHP_EOL;

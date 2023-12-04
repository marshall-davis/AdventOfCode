<?php

$signal = str_split(trim(fgets(fopen('input.txt', 'r'))));
$possibleMarker = [];

foreach (['Part One' => 4, 'Part Two' => 14] as $part => $packetLength) {
    foreach ($signal as $position => $character) {
        $possibleMarker[] = $character;

        if (count($possibleMarker) >= $packetLength + 1) {
            array_shift($possibleMarker);
        }

        if (count($possibleMarker) !== $packetLength || count(array_unique($possibleMarker)) !== $packetLength) {
            continue;
        }

        $position += 1;
        echo "Signal start marker for {$part} (" . implode($possibleMarker) . ") found at position {$position}\n";
        break;
    }
}

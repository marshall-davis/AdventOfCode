<?php

readonly class Race
{
    public function __construct(
        public int $duration,
        public int $record
    ){}
}

$races = [
    new Race(45, 305),
    new Race(97, 1062),
    new Race(72, 1110),
    new Race(95, 1695),
];

$winConditions = [0,0,0,0];
foreach ($races as $id => $race) {
    for ($held = 0; $held <= $race->duration; $held++) {
        $distance = $held * ($race->duration - $held);
        if ($distance > $race->record) {
            $winConditions[$id] += 1;
        }
    }
}

echo 'Races have the following win conditions: ' . implode(', ', $winConditions) . PHP_EOL;
echo array_reduce($winConditions, fn (int $carry, int $conditions) => $carry * $conditions, 1) . PHP_EOL;

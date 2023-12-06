<?php

readonly class Race
{
    public function __construct(
        public int $duration,
        public int $record
    ){}
}

$races = [
    new Race(45977295, 305106211101695),
];

$winConditions = [0,];
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

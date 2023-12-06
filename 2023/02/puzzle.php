<?php

$cubesLoaded = [
    'red' => 12,
    'green' => 13,
    'blue' => 14
];

readonly class Game
{
    public function __construct(
        public int $id,
        public int $red,
        public int $green,
        public int $blue
    )
    {
    }

    public function validate(?int $red, ?int $blue, ?int $green): bool
    {
        if ($red > $this->red) {

            return false;
        }
        if ($blue > $this->blue) {
            return false;
        }
        if ($green > $this->green) {
            return false;
        }
        return true;
    }
}

$input = fopen('input.txt', 'r');
$valid = [];
$invalid = [];
$powers = [];
while (!feof($input)) {
    $parts = [];
    if (preg_match('/Game (?<id>\d+): (?<plays>.*)/', fgets($input), $parts) === 0) {
        continue;
    }
    ['id' => $game, 'plays' => $plays] = $parts;
    $plays = explode(';', $plays);
    $isValid = true;
    $minimums = ['red' => 0, 'blue' => 0, 'green' => 0];
    foreach ($plays as $play) {
        $cubes = [];
        preg_match_all('/(?<red>\d+ red)|(?<blue>\d+ blue)|(?<green>\d+ green)/', $play, $cubes);
        array_walk(
            $cubes,
            fn(&$value, $key) => $value = is_numeric($key) ? null : $value
        );
        $cubes = array_map(
            function (array $value) {
                $value = array_filter($value);
                return (int)array_pop($value);
            },
            array_filter(
                $cubes
            )
        );
        foreach ($cubes as $color => $number) {
            $minimums[$color] = max($minimums[$color], $number);
        }
        $isValid = $isValid && (new Game($game, ...$cubesLoaded))
                ->validate(...
                    $cubes
                );
    }

    $powers[$game] = array_reduce($minimums, fn($carry, $item) => $carry * $item, 1);

    if ($isValid) {
        $valid[] = $game;
    } else {
        $invalid[] = $game;
    }
}

$valid = array_unique($valid);
$invalid = array_unique($invalid);

echo 'Valid: ' . implode(', ', $valid) . PHP_EOL;
echo 'Invalid: ' . implode(', ', $invalid) . PHP_EOL;
echo array_sum($powers) . PHP_EOL;

<?php

enum Shape: int {
    public const A = self::ROCK;
    public const B = self::PAPER;
    public const C = self::SCISSORS;

    case ROCK = 1;
    case PAPER = 2;
    case SCISSORS = 3;

    public function check(self $opponent): ?bool
    {
        return match ($opponent) {
            self::ROCK => $this === self::PAPER ? true : ($this === self::SCISSORS ? false : null),
            self::PAPER => $this === self::SCISSORS ? true : ($this === self::ROCK ? false : null),
            self::SCISSORS => $this === self::ROCK ? true : ($this === self::PAPER ? false : null),
        };
    }

    public static function convert(string $letter): Shape
    {
        return constant('Shape::'.$letter);
    }

    public static function isBeatBy(Shape $played): Shape
    {
        return match ($played) {
            self::ROCK => self::PAPER,
            self::PAPER => self::SCISSORS,
            self::SCISSORS => self::ROCK
        };
    }

    public static function beats(Shape $played): Shape
    {
        return match ($played) {
            self::ROCK => self::SCISSORS,
            self::PAPER => self::ROCK,
            self::SCISSORS => self::PAPER
        };
    }
}

$games = fopen('input.txt', 'r');
$score = 0;

while (!feof($games)) {
    $line = trim(fgets($games));
    if (empty($line)) {
        continue;
    }
    [$opponent, $player] = array_map('strtoupper',explode(' ', $line));
    echo "Input: " . $opponent . ' ' . $player.PHP_EOL;

    $opponent = Shape::convert($opponent);
    $player = match ($player) {
        'X'=> Shape::beats($opponent), // Player Loses
        'Y'=> $opponent, // Draw
        'Z' => Shape::isBeatBy($opponent), // Player Wins
    };
    $points = match ($player->check($opponent)) {
        true => 6,
        false => 0,
        default => 3
    };

    echo implode(' ', [
        'This round played',
        implode(' ', [
            'Opponent:',
            $opponent->name,
            'Player:',
            $player->name
        ]),
        $player->value,
        '+',
            (string)$points
    ]).PHP_EOL;

    $score += $points + $player->value;
    echo "Total Score: $score". PHP_EOL;
}

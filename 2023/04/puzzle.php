<?php

readonly class Card
{
    public function __construct(
        public int   $id,
        public array $winning,
        public array $played
    )
    {
    }

    public function value(): int
    {
        $matches = $this->matches();

        $value = array_reduce($matches, fn(int $value) => $value * 2, count($matches) > 0 ? 1 : 0) /2;
        echo "Card {$this->id} has ". count($matches). ' matches for '.$value.PHP_EOL;
        return $value;
    }

    public function matches(): array
    {
        return array_intersect($this->winning, $this->played);
    }
}

$input = fopen('input.txt', 'r');
$cards = [];
while (!feof($input)) {
    $line = [];
    if (preg_match('/^Card\s+(?<id>\d+):\s+(?<winning>.+?)\|(?<played>.+?)$/', fgets($input), $line) !== 1) {
        continue;
    }
    $cards[] = new Card(
        (int)trim($line['id']),
        array_map(fn(string $number) => (int)trim($number), preg_split('/\s+/', trim($line['winning']))),
        array_map(fn(string $number) => (int)trim($number), preg_split('/\s+/', trim($line['played'])))
    );
}

echo array_reduce($cards, fn(int $carry, Card $current) => $carry + $current->value(), 0) . PHP_EOL;

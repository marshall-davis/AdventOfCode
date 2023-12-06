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
        return array_reduce($matches, fn(int $value) => $value * 2, count($matches) > 0 ? 1 : 0) / 2;
    }

    public function matches(): array
    {
        return array_intersect($this->winning, $this->played);
    }

    public function awards(): int
    {
        return count($this->matches());
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

$awards = [];
foreach ($cards as $card) {
    echo "Card {$card->id} awards {$card->awards()} per copy (";
    $awards[$card->id] = ($awards[$card->id] ?? 0) + 1;
    echo "{$awards[$card->id]}).\n";
    if ($card->awards() > 0) {
        for ($copy = 1; $copy <= $awards[$card->id]; $copy++) {
//            echo "\tFor copy {$copy}/{$awards[$card->id]}.\n";
            for ($awarded = 1; $awarded <= $card->awards(); $awarded++) {
                $add = $card->id + $awarded;
//                echo "\t\tOne {$add}.\n";
                if (!isset($awards[$add])) {
                    $awards[$add] = 1;
                    continue;
                }

                $awards[$add] += 1;
            }
        }
    }
}

echo array_reduce($awards, fn (int $count, int $awarded) => $count + $awarded, 0) . PHP_EOL;
echo "YAY!\n";

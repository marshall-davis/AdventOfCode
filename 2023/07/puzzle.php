<?php

readonly class Hand
{
    private array $cards;

    public function __construct(string $cards, public int $bid)
    {
        $this->cards = str_split($cards);
    }

    public function type(): int
    {
        $sets = array_count_values($this->cards);

        return match (count($sets)) {
            1 => 7, // Five of a kind.
            5 => 1, // High card.
            3 => in_array(3, $sets) ? 4 : 3, // Three of a kind or two pair.
            4 => 2, // Pair
            2 => in_array(4, $sets) ? 6 : 5, // Full house or four of a kind.
        };
    }

    public function beats(Hand $opponent): bool
    {
        if ($this->type() > $opponent->type()) {
            return true;
        }

        if ($this->type() < $opponent->type()) {
            return false;
        }

        foreach ($this->cards as $position => $card) {
            $card = is_numeric($card) ? $card : ['T'=>10,'J'=>11,'Q'=>12,'K'=>13,'A'=>14][$card];
            $opposed = is_numeric($opponent->cards[$position]) ? $opponent->cards[$position] : ['T'=>10,'J'=>11,'Q'=>12,'K'=>13,'A'=>14][$opponent->cards[$position]];
            if ($card > $opposed) {
                return true;
            }
            if ($card < $opposed) {
                return false;
            }
        }

        return false;
    }
}

$hands = [];
$game = fopen('input.txt', 'r');
while (!feof($game)) {
    $line = explode(' ', trim(fgets($game)));
    if (count($line) !== 2) {
        continue;
    }
    $hands[] = new Hand(...$line);
}
// Sort hands in ascending order of type
usort($hands, fn($a, $b) => $a->beats($b) ? 1 : -1);

$total = 0;
foreach ($hands as $rank => $hand) {
    $total += ($rank + 1) * $hand->bid;
}
echo "$total\n";

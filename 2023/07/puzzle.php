<?php

readonly class Card
{
    public function __construct(public string $face)
    {
    }

    public function value(): int
    {
        $faces = ['T' => 10, 'J' => 1, 'Q' => 12, 'K' => 13, 'A' => 14];
        return is_numeric($this->face) ? $this->face : $faces[$this->face];
    }
}

readonly class Hand
{
    private array $cards;

    public function __construct(string $cards, public int $bid)
    {
        $this->cards = array_map(fn(string $face) => new Card($face), str_split($cards));
    }

    public function type(): int
    {
        $sets = array_count_values(array_map(fn(Card $c) => $c->face, $this->cards));

        if (count($sets) > 1 && array_key_exists('J', $sets)) {
            $biggestSet = max($sets);
            $bigs = array_keys(array_filter($sets, fn(int $n) => $n === $biggestSet));
            $jacks = $sets['J'];
            unset($sets['J']);
            assert($jacks + array_sum($sets) === 5);
            if (in_array('A', $bigs)) {
                $sets['A'] += $jacks;
            } elseif (in_array('K', $bigs)) {
                $sets['K'] += $jacks;
            } elseif (in_array('Q', $bigs)) {
                $sets['Q'] += $jacks;
            } elseif (in_array('T', $bigs)) {
                $sets['T'] += $jacks;
            } else {
                $bigs = array_filter($bigs, fn($c) => is_numeric($c));
                sort($bigs);
                $backups = array_keys($sets);
                sort($backups);
                $addingTo = array_pop($bigs) ?? array_pop($backups);
                if ($addingTo === 'J') {
                    $sets['J'] = $jacks;
                } else {
                    $sets[$addingTo] += $jacks;
                }
            }
        }

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
            echo "$this beats $opponent\n";
            return true;
        }

        if ($this->type() < $opponent->type()) {
            echo "$this loses to $opponent\n";
            return false;
        }

        foreach ($this->cards as $position => $card) {

            $card = $card->value();
            $opposed = $opponent->cards[$position]->value();
            if ($card > $opposed) {
                echo "$this beats $opponent\n";
                return true;
            }
            if ($card < $opposed) {
                echo "$this loses to $opponent\n";
                return false;
            }
        }

        echo "$this loses to $opponent\n";
        return false;
    }

    function __toString(): string
    {
        return implode('', array_map(fn(Card $c)=>$c->face,$this->cards));
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

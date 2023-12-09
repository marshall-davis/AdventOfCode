<?php

class History
{
    private array $deltas = [];

    public function __construct(string $readings)
    {
        $this->deltas[] = array_map(fn(string $reading) => (int)$reading, explode(' ', $readings));
        do {
            $this->deltas[] = $this->calculateDeltas($this->deltas[array_key_last($this->deltas)]);
        } while (!array_reduce($this->deltas[array_key_last($this->deltas)], fn(bool $isZero, int $delta) => $isZero && ($delta === 0), true));
        $this->deltas = array_reverse($this->deltas);
        array_shift($this->deltas); // Can always throw away the row with zeros.
    }

    private function calculateDeltas(array $readings): array
    {
        $deltas = [];
        do {
            $a = array_shift($readings);
            $b = $readings[0];
            $deltas[] = $b - $a;
        } while (count($readings) > 1);

        return $deltas;
    }

    public function extrapolate(): int
    {
        // Retrieve last, don't pop it.
        return array_reduce($this->deltas, fn(int $previous, array $current) => $previous + $current[array_key_last($current)], 0);
    }

    public function backstrapolate(): int
    {
        return array_reduce(
            $this->deltas,
            function (int $previous, array $current) {
                return (($this->deltas[array_search($current, $this->deltas)] ?? $current)[0]) - $previous;
            },
            0
        );
    }
}

$input = fopen('input.txt', 'r');
$histories = [];
do {
    if ($line = trim(fgets($input))) {
        $histories[] = new History($line);
    }
} while (!feof($input));

echo 'Extrapolating: ' . array_reduce($histories, fn(int $sum, History $history) => $sum + $history->extrapolate(), 0) . PHP_EOL;
echo 'Backstrapolating: ' . array_reduce($histories, fn(int $sum, History $history) => $sum + $history->backstrapolate(), 0) . PHP_EOL;

echo "DONE!\n";

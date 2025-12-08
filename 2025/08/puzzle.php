<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
$connections = 1000;
//$input = fopen('example.txt', 'r+');
//$connections = 10;

readonly class Point implements Stringable
{
    public function __construct(
        public int $x,
        public int $y,
        public int $z
    ) {
    }

    public function __toString(): string
    {
        return "({$this->x},{$this->y},{$this->z})";
    }
}

class Junction implements Stringable
{
    public function __construct(
        public readonly int $id,
        public readonly Point $point,
        /** @var list<Junction> */
        private array $connections = []
    ) {
    }

    public function connect(Junction $junction, bool $reciprocal = true): void
    {
        if (array_key_exists($junction->id, $this->connections)) {
            throw new RuntimeException('Short circuit detected!');
        }
        $this->connections[$junction->id] = $junction;
        if ($reciprocal) {
            $junction->connect($this, false);
        }
    }

    /**
     * @param  Junction|null  $incoming
     * @return list<Junction>
     */
    public function circuit(?Junction $incoming = null): array
    {
        return array_unique(array_merge([$this],$this->connections,
            ...array_map(
                fn(Junction $junction) => $junction->circuit($this),
                array_filter($this->connections, fn(Junction $junction) => $junction->id !== $incoming?->id))));
    }

    public function connected(?Junction $to = null): bool
    {
        return $to === null ? !!$this->connections : in_array($to->id, array_column($this->circuit(), 'id'));
    }

    public function __toString(): string
    {
        return "{$this->id}: {$this->point}";
    }
}

function distance(Point $a, Point $b): int
{
    return (int) sqrt(pow($a->x - $b->x, 2) + pow($a->y - $b->y, 2) + pow($a->z - $b->z, 2));
}

function closest(Junction $junction, array $junctions): Junction
{
    $available = array_filter($junctions, fn(Junction $target) => $junction->connected($target) === false);
    uasort($available,
        fn(Junction $a, Junction $b) => distance($junction->point, $a->point) <=> distance($junction->point,
                $b->point));
    return array_first($available);
}

/** @var list<Junction> $junctions */
$junctions = [];
while (!feof($input)) {
    $junctions[] = new Junction(count($junctions ?? []),
        new Point(...array_map('intval', explode(',', fgets($input)))));
}

$circuits = [];

$distances = [];

foreach ($junctions as $a) {
    foreach ($junctions as $b) {
        if ($a->id === $b->id) {
            continue;
        }
        $id = $a->id < $b->id ? "$a->id-$b->id" : "$b->id-$a->id";
        if (!isset($distances[$id])) {
            $distances[$id] = distance($a->point, $b->point);
        }
    }
}
asort($distances);
//assert(array_key_first($distances) === '0-19', 'Distance formulas are incorrect.');
//foreach (array_map(fn (string $key) => $key.' '.$distances[$key], array_combine(range(1, count($distances)), array_keys($distances))) as $pair => $distance) {
//    echo "$pair: $distance\n";
//}

while ($connections--) {
    $pair = array_key_first($distances);
    array_shift($distances);
    [$a, $b] = explode('-', $pair);

    if ($junctions[$a]->connected($junctions[$b])) {
        echo "Already connected {$junctions[$a]} and {$junctions[$b]}\n";
//        $connections++; // In part one the elves DO connect the junctions, dummies
        continue;
    }
    echo "Connecting {$junctions[$a]} and {$junctions[$b]}\n";
    $junctions[$a]->connect($junctions[$b]);
//    echo 'Sizes '. implode(', ',$circuitSizes =array_count_values(array_map(fn (Junction $junction) => count($junction->circuit()), $junctions))).PHP_EOL;
}

$circuitSizes = array_unique(array_map(fn (Junction $junction) => count($junction->circuit()), $junctions));
arsort($circuitSizes);
$pertinent = array_slice($circuitSizes, offset: 0, length: 3);
echo 'Pertinent circuit sizes: '.implode(', ', $pertinent).PHP_EOL;

echo 'Circuit product: '.array_product($pertinent).PHP_EOL;

<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

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
        private array $connections = [],
        /** @var list<int> */
        private ?array $circuit = null
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
        $this->refreshCircuit();
    }

    /**
     * @param  Junction|null  $incoming
     * @return list<int>
     */
    public function circuit(?Junction $incoming = null): array
    {
        $connectionsWithoutIncoming = array_filter($this->connections,
            fn(Junction $junction) => $junction->id !== $incoming?->id);
        $connectionsWithoutIncoming = array_map(
            fn(Junction $junction) => $junction->circuit($this),
            $connectionsWithoutIncoming);
        $connectionsWithoutIncoming = array_merge([$this->id],
            array_map(fn(Junction $connection) => $connection->id, $this->connections), ...$connectionsWithoutIncoming);
        return array_unique($connectionsWithoutIncoming);
    }

    public function refreshCircuit(): self
    {
        $this->circuit = null;
        return $this;
    }

    public function connected(?Junction $to = null): bool
    {
        return $to === null ? !!$this->connections : in_array($to->id, $this->circuit());
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

/** @var list<Junction> $junctions */
$junctions = [];
while (!feof($input)) {
    $junctions[] = new Junction(count($junctions ?? []),
        new Point(...array_map('intval', explode(',', fgets($input)))));
}

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


do {
    echo "Trying ".array_key_first($distances)."\n";
    $pair = array_key_first($distances);
    if ($pair === null) {
        throw new RuntimeException('Out of options!');
    }

    array_shift($distances);
    [$a, $b] = explode('-', $pair);

    if (!$junctions[$a]->connected($junctions[$b])) {
        $junctions[$a]->connect($junctions[$b]);
    }

    foreach ($junctions as $junction) {
        if (!array_diff_key($junctions, array_flip($junctions[$a]->refreshCircuit()->circuit()))) {
            break 2;
        }
    }
} while (true);

echo "{$junctions[$a]->point->x} * {$junctions[$b]->point->x}\n";
$wall = $junctions[$a]->point->x * $junctions[$b]->point->x;
assert($wall < 5166672507, "$wall is too high!");
echo 'Wall distance: '.$wall.PHP_EOL;

<?php

class Warehouse
{
    public static function fromMap(array $map): self
    {
        $warehouse = new self();
        foreach ($map as $level) {
            foreach ($level as $stack => $item) {
                if (!empty($item)) {
                    $warehouse->store((int)$stack, $item);
                }
            }
        }

        return $warehouse;
    }

    public function __construct(private array $floor = [])
    {
    }

    public function store(int $bay, string|array $items): bool
    {
        $stack = $this->floor[$bay] ?? [];
        $items = is_array($items) ? $items : [$items];

        array_push($stack, ...$items);

        $this->floor[$bay] = $stack;

        return true;
    }

    public function move(int $fromBay, int $toBay, int $number = 1, bool $isUberCrane = false): bool
    {
        $fromStack = $this->floor[$fromBay];
        $toStack = $this->floor[$toBay];

        $activeCargo = array_splice($fromStack, -$number);
        $activeCargo = $isUberCrane ? $activeCargo : array_reverse($activeCargo);

        array_push($toStack, ...$activeCargo);

        $this->floor[$fromBay] = $fromStack;
        $this->floor[$toBay] = $toStack;

        return true;
    }

    public function tops(): array
    {
        return array_map(
            fn(array $stack) => $stack[count($stack) - 1],
            $this->floor
        );
    }
}

function parseInput(string $path): array
{
    $input = fopen($path, 'r');
    $map = [];
    $instructions = [];
    while (!feof($input)) {
        $line = trim(fgets($input), "\n\r");
        switch (true) {
            case preg_match('/(\s*\[\w])+/', $line):
                $map[] = array_map(
                    fn(string $crate) => trim($crate, ' []'),
                    str_split($line, 4)
                );
                break;
            case preg_match('/move \d+/', $line):
                $instructions[] = trim($line);
                break;
        }
    }

    return [array_reverse($map), $instructions];
}

[$map, $instructions] = parseInput('input.txt');

// Part 1
$warehouse = Warehouse::fromMap($map);

foreach ($instructions as $instruction) {
    preg_match('/move (?P<number>\d+) from (?P<fromStack>\d+) to (?P<toStack>\d+)/', $instruction, $params);
    $warehouse->move($params['fromStack'] - 1, $params['toStack'] - 1, $params['number']);
}

echo implode('', $warehouse->tops()) . PHP_EOL;

// Part 2
$warehouse = Warehouse::fromMap($map);

foreach ($instructions as $instruction) {
    preg_match('/move (?P<number>\d+) from (?P<fromStack>\d+) to (?P<toStack>\d+)/', $instruction, $params);
    $warehouse->move($params['fromStack'] - 1, $params['toStack'] - 1, $params['number'], true);
}

echo implode('', $warehouse->tops()) . PHP_EOL;

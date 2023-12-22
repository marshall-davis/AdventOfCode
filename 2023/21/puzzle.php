<?php
$start = microtime(true);

readonly class Coordinate implements Stringable
{
    public function __construct(public int $x, public int $y)
    {
    }

    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }
}

$map = array_map(
    str_split(...),
    file('full.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
);

$plots = [];

foreach ($map as $y => $row) {
    foreach ($row as $x => $column) {
        if ($map[$y][$x] === 'S') {
            $start = new Coordinate($x, $y);
        }
    }
}

echo "Starting at $start\n";
$possibles = [$start];
for ($step = 1; $step <= 64; $step++) {
    $next = [];
    foreach ($possibles as $index => $position) {
        $checking = ($map[$position->y - 1] ?? [])[$position->x] ?? null;
        if (in_array($checking, ['S', '.'])) {
            $next[] = new Coordinate($position->x, $position->y - 1);
            if (!in_array($next[array_key_last($next)], $plots)) {
                $plots[] = $next[array_key_last($next)];
            }
        }
        $checking = ($map[$position->y + 1] ?? [])[$position->x] ?? null;
        if (in_array($checking, ['S', '.'])) {
            $next[] = new Coordinate($position->x, $position->y + 1);
            if (!in_array($next[array_key_last($next)], $plots)) {
                $plots[] = $next[array_key_last($next)];
            }
        }
        $checking = ($map[$position->y] ?? [])[$position->x - 1] ?? null;
        if (in_array($checking, ['S', '.'])) {
            $next[] = new Coordinate($position->x - 1, $position->y);
            if (!in_array($next[array_key_last($next)], $plots)) {
                $plots[] = $next[array_key_last($next)];
            }
        }
        $checking = ($map[$position->y] ?? [])[$position->x + 1] ?? null;
        if (in_array($checking, ['S', '.'])) {
            $next[] = new Coordinate($position->x + 1, $position->y);
            if (!in_array($next[array_key_last($next)], $plots)) {
                $plots[] = $next[array_key_last($next)];
            }
        }
    }
    $next = array_unique($next);
    echo 'Might be at '.count($next).' spots.'.PHP_EOL;
    $possibles = $next;
}

echo "Plots: " . count($possibles).PHP_EOL;

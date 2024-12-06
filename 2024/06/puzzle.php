<?php

$input = fopen('input.txt', 'r+');

$grid = [];

while (!feof($input)) {
    $grid[] = str_split(fgets($input));
}

class Position implements Stringable {
    public function __construct(
        public string $orientation,
        public int $row,
        public int $column
    ) {}

    public function __toString(): string {
        return "\{{$this->orientation}, {$this->row}, {$this->column}\}";
    }
}

foreach ($grid as $row => $currentRow) {
    foreach ($currentRow as $currentColumn => $value) {
        if (in_array($value, ['^','<','>','V'])) {
            $position = new Position($value, $row, $currentColumn);
        }
    }
}

$step = 0;
$seen = [];

function move(Position $position, array $grid, bool $verbose = false, int $depth = 0): bool
{
    global $step, $seen;
    if ($verbose) {
        echo "Going down to level $depth\n";
    }
    if ($depth > 10) {
        echo "We're spinning\n";
        return true;
    }
    if ($verbose) {
        echo "Moving from $position\n";
    }
    if (in_array("{$position->orientation},{$position->row},{$position->column}", $seen)) {
        return true;
    }
    $seen[] = "{$position->orientation},{$position->row},{$position->column}";
    $attempt = new Position($position->orientation, $position->row, $position->column);
    match ($attempt->orientation) {
        'V' => ++$attempt->row,
        '<'=> --$attempt->column,
        '>'=> ++$attempt->column,
        '^'=>--$attempt->row,
    };

    if (($grid[$attempt->row][$attempt->column] ?? null) === '#') {
        $step = 0;
        if ($verbose) {
            echo "Turning from {$attempt->orientation} to";
        }
        match ($attempt->orientation) {
            'V' => $position->orientation = '<',
            '<'=> $position->orientation = '^',
            '>'=> $position->orientation = 'V',
            '^'=>$position->orientation = '>',
        };
        if ($verbose) {
            echo " {$position->orientation}\n";
        }
        move($position, $grid, depth: $depth+1);


        return false;
    }

    $position->row = $attempt->row;
    $position->column = $attempt->column;

    if ($verbose) {
        echo "Stepped\n";
    }
    return false;
}

$found = 0;
$o = $position->orientation;
$r = $position->row;
$c = $position->column;
foreach ($grid as $row => $currentRow) {
    foreach ($currentRow as $currentColumn => $value) {
        if ($currentColumn === $c && $row === $r) {
            echo "Skipping origin\n";
            continue;}
        if($value === '#') {
            echo "Skipping existing obstacle\n";
            continue;}
        $testGrid = $grid;
        $testGrid[$row][$currentColumn] = '#';
        $seen = [];
        $runner = new Position($o, $r, $c);
        do {
            if ($looped = move($runner, $testGrid, ($row === 44 && $currentColumn === 87))) {
                ++$found;
                echo "Looped at {$row},{$currentColumn}\n";
            }

        } while (($testGrid[$runner->row][$runner->column] ?? false) && $looped===false);
    }
}


array_pop($seen);
echo $found.PHP_EOL;

assert($found < 16215);

/**
 * Given an obstacle at (3,1) as depicted below.
 * ..#...............
 * You can predict what spaces require an obstacle to cause a loop when approached
 * from any side. For instance if approached from above:
 * ..V...............
 * ..#...............
 * It is known that the guard turns right, and an obstacle would be required in any space to
 * the left of this row. These would (1,1) or (1,2).
 * You can then check for the existence of an obstacle in column C+1 where C is the column
 * in which the obstacle was placed. If one exists this has potential for a loop.
 * ..#...............
 * ..................
 * ??V...............
 * ..#...............
 * Placing an obstacle at (1,3) would cause the guard to turn right and move up column 2;
 * eventually leaving the grid. However, placing it at (2,3) means that the guard will
 * eventually hit the column at (3,1); there is an obstacle in (C+1,<R) from the placed
 * barrier.
 *
 * Knowing that moving up requires a matched obstacle in (>C,R), moving down requires
 * a match in (<C,R), and so on you may find a speedier approach by walking the path
 * once and checking placements based on contact with existing obstacles.
 *
 * Something to think about!
 */
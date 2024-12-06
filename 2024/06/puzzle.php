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

$step = 1;
$seen = ["{$position->row},{$position->column}"];

function move(Position $position, array $grid): void
{
    global $step, $seen;
    $attempt = new Position($position->orientation, $position->row, $position->column);
    match ($attempt->orientation) {
        'V' => ++$attempt->row,
        '<'=> --$attempt->column,
        '>'=> ++$attempt->column,
        '^'=>--$attempt->row,
    };

    if (($grid[$attempt->row][$attempt->column] ?? null) === '#') {
        match ($attempt->orientation) {
            'V' => $position->orientation = '<',
            '<'=> $position->orientation = '^',
            '>'=> $position->orientation = 'V',
            '^'=>$position->orientation = '>',
        };

        move($position, $grid);

        return;
    }

    $position->row = $attempt->row;
    $position->column = $attempt->column;
    if (!in_array("{$position->row},{$position->column}", $seen)) {
        ++$step;
        $seen[] = "{$position->row},{$position->column}";
    }
}

do {
    move($position, $grid);
} while ($grid[$position->row][$position->column] ?? false);

array_pop($seen);
echo count($seen).PHP_EOL;
<?php

class Location
{
    public const KEY = '.';

    private array $connections = [];

    public function __construct(
        public readonly int $row,
        public readonly int $column
    )
    {
    }

    public function key(): string
    {
        return $this::KEY;
    }

    public function __toString(): string
    {
        return $this::KEY . ' at (' . $this->row . ', ' . $this->column . ')';
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            default => null,
            'up', 'left', 'right', 'down' => $this->connections[$name] = $value,
        };
    }

    public function isEntry(string $moving): bool
    {
        return false;
    }

    public function __get(string $name): ?Location
    {
        return match ($name) {
            default => null,
            'up', 'down', 'left', 'right' => $this->connections[$name] ?? null,
        };
    }

    public static function fromKey(string $key, int $row, int $column): ?Location
    {
        return match ($key) {
            default => new Location($row, $column),
            'S' => new Start($row, $column),
            '|' => new UpDown($row, $column),
            'L' => new UpRight($row, $column),
            'J' => new UpLeft($row, $column),
            '7' => new DownLeft($row, $column),
            'F' => new DownRight($row, $column),
            '-' => new LeftRight($row, $column),
        };
    }
}

class Start extends Location
{
    public const KEY = 'S';

    public function isEntry(string $moving): bool
    {
        return true;
    }
}

class UpDown extends Location
{
    public const KEY = '|';

    public function isEntry(string $moving): bool
    {
        return $moving === 'up' || $moving === 'down';
    }
}

class UpRight extends Location
{
    public const KEY = 'L';

    public function isEntry(string $moving): bool
    {
        return $moving === 'left' || $moving === 'down';
    }
}

class UpLeft extends Location
{
    public const KEY = 'J';

    public function isEntry(string $moving): bool
    {
        return $moving === 'right' || $moving === 'down';
    }
}

class DownLeft extends Location
{
    public const KEY = '7';

    public function isEntry(string $moving): bool
    {
        return $moving === 'up' || $moving === 'right';
    }
}

class Open extends Location {
    public const KEY = 'O';
}

class Enclosed extends Location{
    public const KEY = 'I';
}

class DownRight extends Location
{
    public const KEY = 'F';

    public function isEntry(string $moving): bool
    {
        return $moving === 'left' || $moving === 'up';
    }
}

class LeftRight extends Location
{
    public const KEY = '-';

    public function isEntry(string $moving): bool
    {
        return $moving === 'left' || $moving === 'right';
    }
}

class Map
{
    /** @var array<Location> */
    private array $locations = [];
    public readonly Location $start;

    private array $loop = [];

    private array $bounded = [];

    public function __construct(string $input)
    {
        $contents = file($input, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->locations = array_map(
            fn(string $data, $row) => array_map(
                function (string $location, $column) use ($row) {
                    $location = Location::fromKey($location, $row, $column);
                    if ($location::KEY === 'S') {
                        $this->start = $location;
                    }
                    return $location;
                },
                str_split($data),
                array_keys(str_split($data))
            ),
            $contents,
            array_keys($contents)
        );
    }

    public function link(): static
    {
        foreach ($this->locations as $row) {
            foreach ($row as $location) {
                match ($location::KEY) {
                    'S' => $location->up = $this->at($this->start->row-1, $this->start->column) and $location->right = $this->at($location->row, $location->column+1),
                    '.' => null,
                    '|' => $location->up = $this->at($location->row - 1, $location->column) and $location->down = $this->at($location->row + 1, $location->column),
                    'F' => $location->right = $this->at($location->row, $location->column + 1) and $location->down = $this->at($location->row + 1, $location->column),
                    'L' => $location->right = $this->at($location->row, $location->column + 1) and $location->up = $this->at($location->row - 1, $location->column),
                    '-' => $location->right = $this->at($location->row, $location->column + 1) and $location->left = $this->at($location->row, $location->column - 1),
                    'J' => $location->up = $this->at($location->row - 1, $location->column) and $location->left = $this->at($location->row, $location->column - 1),
                    '7' => $location->left = $this->at($location->row, $location->column - 1) and $location->down = $this->at($location->row + 1, $location->column)
                };
            }
        }
        return $this;
    }

    public function findLoop(): array
    {
        $this->loop = [$this->start];

        do {
            foreach (['up', 'down', 'left', 'right'] as $direction) {
                $attempt = $this->loop[array_key_last($this->loop)]->$direction;
                if ($attempt && $attempt !== ($this->loop[array_key_last($this->loop)-1]?? null)) {
                    $this->loop[] = $attempt;
                    break;
                }
            }
        } while ($this->loop[array_key_last($this->loop)] !== $this->start);

        return $this->loop;
    }


    public function bounded(): array
    {
        foreach ($this->locations as $row) {
            foreach ($row as $location) {
                $blockLeft = false;
                $blockRight= false;
                $blockUp = false;
                $blockDown = false;
                if ($location::KEY !== '.') {
                    continue;
                }

                // Check vertically
                for ($offset = 0; $offset < count($this->locations); $offset++) {
                    $toDown = $this->at($location->row + $offset, $location->column);
                    if ($toDown?->key() === 'O') {
                        $this->locations[$location->row][$location->column] = new Open($location->row, $location->column);
                        continue 2;
                    }
                    if (in_array($toDown, $this->loop) && $toDown?->left && $toDown?->right) {
                        $blockDown = true;
                    }
                    $toUp = $this->at($location->row - $offset, $location->column);
                    if ($toUp?->key() === 'O') {
                        echo "FREEDOM!\n";
                        $this->locations[$location->row][$location->column] = new Open($location->row, $location->column);
                        continue 2;
                    }
                    if (in_array($toUp, $this->loop) && $toUp?->left && $toUp?->right) {
                        $blockUp = true;
                    }
                }

                // Check horizontally
                for ($offset = 0; $offset < count($this->locations); $offset++) {
                    $toLeft = $this->at($location->row, $location->column - $offset);
                    if ($toLeft?->key() === 'O') {
                        $this->locations[$location->row][$location->column] = new Open($location->row, $location->column);
                        continue 2;
                    }
                    if ($toLeft?->up && $toLeft?->down) {
                        $blockLeft = true;
                    }
                    $toRight = $this->at($location->row, $location->column+$offset);
                    if ($toRight?->key() === 'O') {
                        $this->locations[$location->row][$location->column] = new Open($location->row, $location->column);
                        continue 2;
                    }
                    if ($toRight?->up && $toRight?->down) {
                        $blockRight = true;
                    }
                }

                if ($blockDown && $blockUp && $blockRight && $blockLeft) {
                    $this->bounded[] = $this->locations[$location->row][$location->column] = new Enclosed($location->row, $location->column);
                }
            }
        }

        return $this->bounded;
    }

    public function visualize(): string
    {
        $image = array_pad([], 140, array_pad([], 140, ' '));
        foreach ($this->loop as $location) {
            $image[$location->row][$location->column] = $location::KEY;
        }
        foreach ($this->bounded as $bounded) {
            $image[$bounded->row][$bounded->column] = $bounded::KEY;
        }

        return implode('', array_map(fn (array $row) => implode('', $row) . "\n",$image));
    }

    public function at(int $row, int $column): ?Location
    {
        $row = $this->locations[$row] ?? [];
        return $row[$column] ?? null;
    }
}

$map = new Map('input.txt');
$loop = $map->link()->findLoop();

echo count($loop) . ' steps.'.PHP_EOL;

echo 'Thing at: ' .(count($loop)-1)/2 . PHP_EOL;

echo 'Bounds ' . count($map->bounded()).PHP_EOL;
echo $map->visualize();

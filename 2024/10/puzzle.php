<?php

$inputStream = fopen('input.txt', 'r+');

$map = [];

while (!feof($inputStream)) {
    $map[] = str_split(trim(fgets($inputStream)));
}

readonly class Coordinate implements Stringable
{
    public function __construct(public int $row, public int $column, public ?int $height = 0)
    {
    }

    public function distanceTo(Coordinate $destination): int
    {
        return (int) sqrt(abs(pow($destination->column - $this->column, 2) + pow($destination->row - $this->row, 2)));
    }

    public function slope(Coordinate $destination): mixed
    {
        try {
            return ($destination->row - $this->row) / ($destination->column - $this->column);
        } catch (DivisionByZeroError) {
            return ($destination->row - $this->row) > 0 ? '-INF' : 'INF';
        }
    }

    public function surrounding($orthogonal = true): array
    {
        return [
            new Coordinate($this->row - 1, $this->column),
            new Coordinate($this->row, $this->column + 1),
            new Coordinate($this->row, $this->column - 1),
            new Coordinate($this->row + 1, $this->column),
        ];
    }

    public function __toString(): string
    {
        return "{$this->column}-{$this->row}";
    }
}

class Map
{
    public array $trailheads = [];

    public function __construct(private array $map)
    {
        $this->map = array_map(fn(array $row) => array_map(fn(string $height) => $height !== '.'? (int) $height:null, $row), $map);
    }

    public function trails(): array
    {
        foreach ($this->map as $row => $line) {
            foreach ($line as $col => $cell) {
                if ($cell === 0) {
                    echo "Starting at {$col},{$row}: {$this->map[$row][$col]}\n";
                    $this->score($coordinate = new Coordinate($row, $col,                        $cell));
                    echo "\tScored {$coordinate} as ".count($this->trailheads[(string)$coordinate])."\n";
                }
            }
        }

        echo "There are ".count($this->trailheads)." trailheads.\n";
        return $this->trailheads;
    }

    private function score(Coordinate $coordinate, ?Coordinate $trailhead = null): void
    {
        foreach (
            array_filter(
                array_map(
                    fn(Coordinate $coord) => new Coordinate($coord->row, $coord->column,
                        $this->map[$coord->row][$coord->column]),
                    array_filter(
                        $coordinate->surrounding(),
                        fn(Coordinate $possible) => $possible->row >= 0
                            && $possible->row < count($this->map)
                            && $possible->column >= 0
                            && $possible->column < count($this->map[0])
                    )
                ),
                fn($coord) => $coord->height === ($coordinate->height + 1)
            ) as $path) {
            if (($coordinate->height === 8) && ($path->height === 9)) {
                echo "\tReached summit at {$path}\n";
                $this->trailheads[(string)($trailhead ?? $coordinate)][] = $path;
                // This line gathers the score, without it this becomes the rating.
//                $this->trailheads[(string)($trailhead ?? $coordinate)] = array_unique($this->trailheads[(string)($trailhead ?? $coordinate)]);
                continue;
            }

            echo "\tMoving to {$path}\n";
            $this->score($path, $trailhead ?? $coordinate);
        }
    }
}

$scores = (new Map($map))->trails();
echo 'SUM: '.array_reduce($scores, fn (int $carry, array $trail) => $carry + count($trail), 0).PHP_EOL;

echo "FIN\n";
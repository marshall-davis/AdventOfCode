<?php

readonly class Coordinate implements Stringable
{
    public function __construct(public int $row, public int $column)
    {
    }

    public function distanceTo(Coordinate $destination): int
    {
        return (int)sqrt(abs(pow($destination->column - $this->column, 2) + pow($destination->row - $this->row, 2)));
    }

    public function __toString(): string
    {
        return $this->column.$this->row;
    }
}

class Antenna implements Stringable
{
    public function __construct(public Coordinate $coordinate, public string $frequency, public bool $paired = false)
    {
    }

    public function __get(string $name)
    {
        if (property_exists($this->coordinate, $name)) {
            return $this->coordinate->$name;
        }

        return null;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->coordinate, $name)) {
            return call_user_func_array([$this->coordinate, $name], $arguments);
        }

        throw new BadMethodCallException();
    }

    public function identifier(): string
    {
        return $this->frequency.$this->coordinate;
    }

    public function __toString(): string
    {
        return $this->identifier();
    }
}

class AntiNode implements Stringable
{
    public function __construct(public readonly Coordinate $coordinate, private array $frequencies = []){}

    public function broadcasts(string $frequency): self
    {
        $this->frequencies[] = $frequency;
        return $this;
    }

    public function isBroadcasting(string $frequency): bool
    {
        return in_array($frequency, $this->frequencies);
    }

    public function __toString(): string
    {
        return $this->coordinate;
    }
}

class Map
{
    public array $antennas = [];
    public array $antinodes = [];

    public function __construct(private array $grid)
    {
    }

    public function visualize()
    {
        foreach ($this->grid as $row) {
            foreach ($row as $cell) {
                echo $cell;
            }
            echo "\n";
        }
    }

    // Should accept a closure
    public function transform(): self
    {
        foreach ($this->grid as $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell !== '.') { // dot character should be configurable
                    $this->antennas[] = new Antenna(new Coordinate($y, $x), $cell);
                }
            }
        }

        return $this;
    }

    public function contains(Coordinate $coordinate): bool
    {
        if ($this->grid[$coordinate->row][$coordinate->column] ?? false) {
            return true;
        }

        return false;
    }

    // Specific to puzzle, maybe macros?
    public function antiNodes(Antenna $antenna): int
    {
        $antiNodes = 0;
        // Could generalize this row/column, get all possible pair locations and compare
        $seCoords = [];
        $position = $antenna;
        $onGrid = true;
        while($onGrid) {
            $position = new Coordinate($position->row+1, $position->column+1);
            if (!$this->contains($position)) {
                break;
            }

        }
        $swCoords = [];
        $position = $antenna;
        do {
            $position = new Coordinate($position->row+1, $position->column-1);
            if ($contained = $this->contains($position)) {
                $swCoords[] = $position;
            }
        } while ($contained);
        $neCoords = [];
        $position = $antenna;
        do {
            $position = new Coordinate($position->row-1, $position->column+1);
            if ($contained = $this->contains($position)) {
                $neCoords[] = $position;
            }
        } while ($contained);
        $nwCoords = [];
        $position = $antenna;
        do {
            $position = new Coordinate($position->row-1, $position->column-1);
            if ($contained = $this->contains($position)) {
                $nwCoords[] = $position;
            }
        } while ($contained);
        /** @var Antenna $node */
        foreach (array_filter($this->antennas, fn(Antenna $node) => !$node->paired) as $node) {
            if ($node->coordinate->row === $antenna->coordinate->row) {
                $nodeDistance = $antenna->coordinate->distanceTo($node->coordinate);
                if ($nodeDistance === 0) {
                    // Same node
                    continue;
                }
                if ($node->coordinate->row - $antenna->coordinate->row > 0) {
                    // The pair is to the right, so the anti is left 1d and right 2d
                    if ($this->contains(new Coordinate($antenna->row, $antenna->column - $nodeDistance)) === true) {
                        ++$antiNodes;
                    }
                    if ($this->contains(new Coordinate($antenna->row,
                            $antenna->column + (2 * $nodeDistance))) === true) {
                        ++$antiNodes;
                    }
                    $antenna->paired = true;
                    $node->paired = true;
                }
                if ($node->coordinate->row - $antenna->coordinate->row < 0) {
                    // The pair is to the left, so the anti is left 2d and right 1d
                    if ($this->contains(new Coordinate($antenna->row,
                            $antenna->column - (2 * $nodeDistance))) === true) {
                        ++$antiNodes;
                    }
                    if ($this->contains(new Coordinate($antenna->row,
                            $antenna->column + $nodeDistance)) === true) {
                        ++$antiNodes;
                    }
                    $antenna->paired = true;
                    $node->paired = true;
                }
            }
            if ($node->coordinate->column === $antenna->coordinate->column) {
                $nodeDistance = $antenna->coordinate->distanceTo($node->coordinate);
                if ($nodeDistance === 0) {
                    // Same node
                    continue;
                }
                if ($node->coordinate->column - $antenna->coordinate->column > 0) {
                    // The pair is down, so the anti is up 1d and down 2d
                    if ($this->contains(new Coordinate($antenna->row + (2 * $nodeDistance),
                            $antenna->column)) === true) {
                        ++$antiNodes;
                    }
                    if ($this->contains(new Coordinate($antenna->row - $nodeDistance, $antenna->column)) === true) {
                        ++$antiNodes;
                    }
                    $antenna->paired = true;
                    $node->paired = true;
                }
                if ($node->coordinate->column - $antenna->coordinate->column < 0) {
                    // The pair is down, so the anti is down 1d and up 2d
                    if ($this->contains(new Coordinate($antenna->row + $nodeDistance, $antenna->column)) === true) {
                        ++$antiNodes;
                    }
                    if ($this->contains(new Coordinate($antenna->row - (2 * $nodeDistance),
                            $antenna->column)) === true) {
                        ++$antiNodes;
                    }
                    $antenna->paired = true;
                    $node->paired = true;
                }
            }
            foreach ($seCoords as $se) {
                if ($node->distanceTo($se) === 0) {
                    // Pair to our se, so there is anti at se 2d the distance from antenna and 1d nw
                    if ($this->contains(
                        new Coordinate(
                            $antenna->row + (2 * abs($node->row - $antenna->row)),
                            $antenna->column + (2 * abs($node->column - $antenna->column))
                        )
                    )) {
                        ++$antiNodes;
                        echo "Found pair going SE for frequency {$antenna->frequency} at {$antenna->column},{$antenna->row}\n";
                    }
                    if ($this->contains(
                        new Coordinate(
                            $antenna->row - (abs($node->row - $antenna->row)),
                            $antenna->column - (abs($node->column - $antenna->column))
                        )
                    )) {
                        ++$antiNodes;
                        echo "Found pair going SE for frequency {$antenna->frequency} at {$antenna->column},{$antenna->row}\n";
                    }
                    $antenna->paired = true;
                    $node->paired = true;
                }
            }
        }

        return $antiNodes;
    }
}

$inputStream = fopen('input.txt', 'r+');

// Great, another grid. I should save a grid-walker
$grid = [];
while (!feof($inputStream)) {
    $grid[] = str_split(trim(fgets($inputStream)));
}

$map = (new Map($grid))->transform();

//echo array_reduce($map->antennas, fn($carry, $node) => $carry + $map->antiNodes($node), 0).PHP_EOL;

echo 'Pray'.PHP_EOL;

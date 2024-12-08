<?php

readonly class Coordinate implements Stringable
{
    public function __construct(public int $row, public int $column)
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
            return null;
        }
    }

    public function __toString(): string
    {
        return "{$this->column}-{$this->row}";
    }
}

/**
 * @mixin Coordinate
 */
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
        return "{$this->frequency}-{$this->coordinate}";
    }

    public function __toString(): string
    {
        return $this->identifier();
    }
}

/**
 * @mixin Coordinate
 */
class AntiNode implements Stringable
{
    public function __construct(public readonly Coordinate $coordinate, private array $frequencies = [])
    {
        $this->frequencies = array_unique($this->frequencies);
    }

    public function broadcasts(array|string $frequency): self
    {
        if (is_string($frequency)) {
            $frequency = [$frequency];
        }
        $this->frequencies = array_merge($this->frequencies, $frequency);
        $this->frequencies = array_unique($this->frequencies);
        return $this;
    }

    public function isBroadcasting(string $frequency): bool
    {
        return in_array($frequency, $this->frequencies);
    }

    public function broadcasting(): array
    {
        return $this->frequencies;
    }

    public function __toString(): string
    {
        return $this->coordinate;
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
}

class Map implements Stringable
{
    public array $antennas = [];
    public array $antinodes = [];

    public function __construct(private array $grid)
    {
        $this->findAntennas()->findAntinodes();
    }

    public function __toString(): string
    {
        return $this->visualize();
    }

    public function visualize(): string
    {
        $representation = '';
        foreach ($this->grid as $x => $row) {
            foreach ($row as $y => $cell) {
                foreach ($this->antinodes as $antinode) {
                    if ((string) $antinode === "$y-$x") {
                        $representation .= '#';
                        continue 2;
                    }
                }
                $representation .= $cell === '.' ? '-' : $cell;
            }
            $representation .= "\n";
        }

        return $representation;
    }

    private function findAntennas(): self
    {
        echo "Discovering antennas.\n";
        foreach ($this->grid as $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell !== '.') { // dot character should be configurable
                    $antenna = new Antenna(new Coordinate($y, $x), $cell);
                    echo "\tNew antenna {$antenna}\n";
                    $this->antennas[] = $antenna;
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
    private function findAntinodes(): self
    {
        echo "Discovering antinodes.\n";
        /** @var Antenna $originator */
        foreach ($this->antennas as $originator) {
            echo "\tFor originator {$originator}\n";
            /** @var Antenna $relay */
            foreach (array_filter($this->antennas, fn(Antenna $antenna
            ) => ($antenna->identifier() !== $originator->identifier()) && ($antenna->frequency === $originator->frequency)) as $relay) {
                foreach ($this->grid as $row => $entries) {
                    foreach ($entries as $column => $cell) {
                        $position = new Coordinate($row, $column);
                        if (
                            $relay->row !== $originator->row
                            && $relay->column !== $originator->column
                            && $position->slope($originator->coordinate) !== $originator->slope($relay->coordinate)
                        ) {
                            continue;
                        }
                        $distances = [
                            $position->distanceTo($originator->coordinate),
                            $position->distanceTo($relay->coordinate),
                        ];

                        if (in_array(0, $distances)) {
                            continue;
                        }

                        sort($distances);

                        if ((($distances[1] / $distances[0]) === 2) && empty(array_filter($this->antinodes,
                                fn(AntiNode $node) => (string) $node === (string) $position))) {
                            $this->antinodes[] = new AntiNode($position, [$originator->frequency]);
                        }
                    }
                }
            }
        }

        return $this;
    }
}

$inputStream = fopen('input.txt', 'r+');

// Great, another grid. I should save a grid-walker
$grid = [];
while (!feof($inputStream)) {
    $grid[] = str_split(trim(fgets($inputStream)));
}

$m = new Map($grid);
echo $m.PHP_EOL;
echo $count = count($m->antinodes).PHP_EOL;

if (261 >= $count || $count >= 2455) {
    echo "WRONG!\n";
}

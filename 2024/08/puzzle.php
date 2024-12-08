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

    public function __toString(): string
    {
        return $this->column.$this->row;
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
        return $this->frequency.$this->coordinate;
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
        foreach ($this->grid as $row) {
            foreach ($row as $cell) {
                $representation .= $cell === '.' ? ' ' : $cell;
            }
            $representation.="\n";
        }

        return $representation;
    }

    private function findAntennas(): self
    {
        echo "Discovering antennas.\n";
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
    private function findAntinodes(): self
    {
        echo "Discovering antinodes.\n";
        /** @var Antenna $originator */
        foreach ($this->antennas as $originator) {
            /** @var Antenna $relay */
            foreach (array_filter($this->antennas, fn(Antenna $antenna
            ) => ($antenna->identifier() !== $originator->identifier()) && ($antenna->frequency === $originator->frequency)) as $relay) {
                if (
                    $relay->row === $originator->row
                ) {
                    $wavelength = $relay->row - $originator->row;
                    $this->antinodes[] = new AntiNode(new Coordinate($originator->row + $wavelength,
                        $originator->column), [$originator->frequency]);
                    $this->antinodes[] = new AntiNode(new Coordinate($originator->row + (-2 * $wavelength),
                        $originator->column), [$originator->frequency]);
                }
                if (
                    $relay->column === $originator->column
                ) {
                    $wavelength = $relay->column - $originator->column;
                    $this->antinodes[] = new AntiNode(new Coordinate($originator->row,
                        $originator->column + $wavelength), [$originator->frequency]);
                    $this->antinodes[] = new AntiNode(new Coordinate($originator->row,
                        $originator->column + (-2 * $wavelength)), [$originator->frequency]);
                }
                if (($verticalDelta = $relay->row - $originator->row) === ($horizontalDelta = $relay->column - $originator->column)) {
                    // These are diagonal relays
                    foreach (
                        [
                            'increment' => new Antinode(new Coordinate($originator->row + $verticalDelta,
                                $originator->column + $horizontalDelta), [$originator->frequency]),
                            'inverse' => new Antinode(new Coordinate($originator->row - $verticalDelta,
                                $originator->column - $horizontalDelta), [$originator->frequency]),
                        ]
                        as $location => $node
                    ) {
                        if ($node->distanceTo($relay->coordinate) === 0) {
                            $node = new Antinode(new Coordinate(
                                $node->row + ($location === 'inverse' ? -$verticalDelta : $verticalDelta),
                                $node->column + ($location === 'inverse' ? -$horizontalDelta : $horizontalDelta)
                            ),
                                [$originator->frequency]);
                        }
                        $this->antinodes[] = $node;
                    }
                }
            }
        }

        $this->collapseWavelengths();

        return $this;
    }

    private function collapseWavelengths(): void
    {
        echo "Collapsing node interference.\n";
        $collapsed = false;
        while (!$collapsed) {
            $collapsed = true;
            /** @var AntiNode $antinode */
            foreach ($this->antinodes as $stable => $antinode) {
                /**
                 * @var int $index
                 * @var AntiNode $interference
                 */
                foreach ($this->antinodes as $index => $interference) {
                    if ($antinode->distanceTo($interference->coordinate) === 0 && $stable !== $index) {
                        $antinode->broadcasts($interference->broadcasting());
                        unset($this->antinodes[$index]);
                        $collapsed = false;
                    }
                }
            }
        }
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
echo count($m->antinodes).PHP_EOL;

//echo array_reduce($map->antennas, fn($carry, $node) => $carry + $map->antiNodes($node), 0).PHP_EOL;

echo 'Pray'.PHP_EOL;

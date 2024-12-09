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
            return ($destination->row - $this->row) > 0 ? '-INF' : 'INF';
        }
    }

    public function __toString(): string
    {
        return "{$this->column}-{$this->row}";
    }
}

assert((new Coordinate(20, 0))->slope(new Coordinate(1, 0)) === "INF");

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
                /** @var AntiNode $antinode */
                foreach ($this->antinodes as $antinode) {
                    if ((string) $antinode === "$y-$x") {
                        $representation .= $antinode->broadcasting()[0];
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
            foreach (
                array_filter(
                    $this->antennas,
                    fn(Antenna $antenna) => ($antenna->identifier() !== $originator->identifier())
                        && ($antenna->frequency === $originator->frequency)
                ) as $relay) {
                $distance = $originator->distanceTo($relay->coordinate);
                $slope = $originator->slope($relay->coordinate);
                if ($slope === 'INF') {
                    /**
                     * This is a vertical line; INF means that the relay is "above" the originator.
                     * Therefor there is a node at ($originator->row + $distance, $originator->column) "below"
                     * the originator and one at ($relay->row - $distance, $relay->column)
                     */
                    if ($this->contains(($lowerNode = new AntiNode(new Coordinate($originator->row + $distance, $originator->column), [$originator->frequency]))->coordinate)) {
                        $this->antinodes[] = $lowerNode;
                    }
                    if ($this->contains(($upperNode = new AntiNode(new Coordinate($relay->row - $distance, $relay->column), [$originator->frequency]))->coordinate)) {
                        $this->antinodes[] = $upperNode;
                    }
                    continue;
                }
                if ($slope === '-INF') {
                    /**
                     * This is a vertical line; INF means that the relay is "below" the originator.
                     * Therefor there is a node at ($relay->row + $distance, $relay->column) "below"
                     * the relay and one at ($originator->row - $distance, $originator->column)
                     */
                    if ($this->contains(($lowerNode = new AntiNode(new Coordinate($relay->row + $distance, $relay->column), [$originator->frequency]))->coordinate)) {
                        $this->antinodes[] = $lowerNode;
                    }
                    if ($this->contains(($upperNode = new AntiNode(new Coordinate($originator->row - $distance, $originator->column), [$originator->frequency]))->coordinate)) {
                        $this->antinodes[] = $upperNode;
                    }
                    continue;
                }
                if ($slope === 0) {
                    /**
                     * This is a horizontal line. The "rightmost" antenna can be determined by comparing the columns;
                     * to the "right"" of this is by the distance one node. To the "left" of the other is another.
                     */
                    if ($relay->column > $originator->column) {
                        // The relay is to the "right".
                        if ($this->contains(($rightNode = new AntiNode(new Coordinate($relay->row, $relay->column+$distance), [$originator->frequency]))->coordinate)) {
                            $this->antinodes[] = $rightNode;
                        }
                        if ($this->contains(($leftNode = new AntiNode(new Coordinate($originator->row, $originator->column - $distance), [$originator->frequency]))->coordinate)) {
                            $this->antinodes[] = $leftNode;
                        }
                    } else {
                        if ($this->contains(($rightNode = new AntiNode(new Coordinate($originator->row, $originator->column+$distance), [$originator->frequency]))->coordinate)) {
                            $this->antinodes[] = $rightNode;
                        }
                        if ($this->contains(($upperNode = new AntiNode(new Coordinate($relay->row, $relay->column - $distance), [$originator->frequency]))->coordinate)) {
                            $this->antinodes[] = $upperNode;
                        }
                    }
                    continue;
                }
                /**
                 * The simple ones are accounted for. Now we must determine the location of nodes on a line
                 * with a given slope.
                 */
                $dx = fn ($slope) => ($distance / sqrt($distance+pow($slope,2)));
                $dy = fn ($slope) => round(($slope * $dx($slope)),0);
                echo "\tSlope: $slope\n\tDelta X: {$dx($slope)}\n\tDelta Y: {$dy($slope)}\n";
                // We need to know which is "left"
                if ($originator->column > $relay->column) {
                    // The relay is "left"
                    if ($this->contains(($node = new AntiNode(new Coordinate($relay->row - $dx($slope), $relay->column - $dy($slope)), ['.']))->coordinate)) {
                        $this->antinodes[] = $node;
                    }
                    if ($this->contains(($node = new AntiNode(new Coordinate($originator->row + $dx($slope), $originator->column + $dy($slope)), ['.']))->coordinate)) {
                        $this->antinodes[] = $node;
                    }
                } else {
                    // The relay is to the right
                    if ($this->contains(($node = new AntiNode(new Coordinate($relay->row + $dy($slope), round($relay->column + $dx($slope))), ['1']))->coordinate)) {
                        $this->antinodes[] = $node;
                        echo "Added $node\nFrom $relay\n";
                    }
                    echo "DEBUG: $node\n";
//                    if ($this->contains(($node = new AntiNode(new Coordinate($originator->row - $dx($slope), $originator->column - $dy($slope)), ['2']))->coordinate)) {
//                        $this->antinodes[] = $node;
//                    }
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

if ((261 >= $count) || ($count >= 2455)) {
    echo "WRONG!\n";
}

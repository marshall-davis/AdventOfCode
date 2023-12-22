<?php

class Coordinate implements Stringable
{
    public function __construct(public readonly int $x, public readonly int $y)
    {
    }

    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }
}

class Location
{
    private array $beams = [];

    public function __construct(public readonly Coordinate $coordinate, public readonly string $base = '.')
    {
    }

    public function crossedBy(Beam $beam): static
    {
        if (in_array($beam::class, $this->beams)) {
            $beam->terminate();
        }

        $this->beams[] = $beam::class;

        return $this;
    }

    public function isEnergized(): bool
    {
        return !empty($this->beams);
    }
}

abstract class Beam
{
    private bool $terminated = false;

    public function __construct(protected Coordinate $coordinate)
    {
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }

    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    public function terminate(): static
    {
        $this->terminated = true;

        return $this;
    }

    abstract public function split(): array;

    public function shift(): Beam
    {
        $this->coordinate = $this->move();

        return $this;
    }

    abstract public function move(): Coordinate;
}

class Right extends Beam
{
    public function move(): Coordinate
    {
        return new Coordinate($this->coordinate->x, $this->coordinate->y + 1);
    }

    public function split(): array
    {
        $this->terminate();
        return [
            new Up($this->coordinate),
            new Down($this->coordinate)
        ];
    }
}

class Left extends Beam
{
    public function move(): Coordinate
    {
        return new Coordinate($this->coordinate->x, $this->coordinate->y - 1);
    }

    public function split(): array
    {
        $this->terminate();
        return [
            new Up($this->coordinate),
            new Down($this->coordinate)
        ];
    }
}

class Up extends Beam
{
    public function move(): Coordinate
    {
        return new Coordinate($this->coordinate->x - 1, $this->coordinate->y);
    }

    public function split(): array
    {
        $this->terminate();
        return [
            new Right($this->coordinate),
            new Left($this->coordinate)
        ];
    }
}

class Down extends Beam
{
    public function move(): Coordinate
    {
        return new Coordinate($this->coordinate->x + 1, $this->coordinate->y);
    }

    public function split(): array
    {
        $this->terminate();
        return [
            new Right($this->coordinate),
            new Left($this->coordinate)
        ];
    }
}

class Contraption
{
    private array $beams = [];
    private array $map = [];

    public function __construct(private readonly string $path)
    {
        $this->remap();
    }

    private function remap(): void
    {
        $input = file($this->path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $this->map = array_map(
            fn(string $row, int $x) => array_map(
                fn(string $character, int $y) => new Location(new Coordinate($x, $y), $character),
                str_split($row),
                array_keys(str_split($row))
            ),
            $input,
            array_keys($input)
        );
    }

    public function at(Coordinate $coordinate): ?Location
    {
        return ($this->map[$coordinate->x] ?? [])[$coordinate->y] ?? null;
    }

    public function fire(Beam $initial): static
    {
        $this->beams[] = $initial;
        $this->at($initial->getCoordinate())?->crossedBy($initial);
        $this->trace();

        return $this;
    }

    private function trace(): static
    {
        do {
            $walking = array_filter($this->beams, fn(Beam $beam) => !$beam->isTerminated());
            array_walk(
                $walking,
                fn(Beam $beam, int $identifier) => match ($this->at($beam->shift()->getCoordinate())?->crossedBy($beam)?->base) {
                    default => $beam->terminate(),
                    '.' => null,
                    '/' => match ($beam::class) {
                        Right::class => $this->beams[$identifier] = new Up($beam->getCoordinate()),
                        Down::class => $this->beams[$identifier] = new Left($beam->getCoordinate()),
                        Up::class => $this->beams[$identifier] = new Right($beam->getCoordinate()),
                        Left::class => $this->beams[$identifier] = new Down($beam->getCoordinate()),
                        default => throw new RuntimeException('Unhandled reflection of ' . $beam::class)
                    },
                    '\\' => match ($beam::class) {
                        Right::class => $this->beams[$identifier] = new Down($beam->getCoordinate()),
                        Down::class => $this->beams[$identifier] = new Right($beam->getCoordinate()),
                        Up::class => $this->beams[$identifier] = new Left($beam->getCoordinate()),
                        Left::class => $this->beams[$identifier] = new Up($beam->getCoordinate()),
                        default => throw new RuntimeException('Unhandled reflection of ' . $beam::class)
                    },
                    '-' => match ($beam::class) {
                        Left::class, Right::class => null,
                        default => $this->beams = array_merge($this->beams, $beam->split())
                    },
                    '|' => match ($beam::class) {
                        Up::class, Down::class => null,
                        default => $this->beams = array_merge($this->beams, $beam->split())
                    },
                }
            );
        } while (array_reduce($this->beams, fn(bool $untraced, Beam $beam) => $untraced || ($beam->isTerminated() === false), false));
        return $this;
    }

    public function maximumCharge(): int
    {
        $charges = [];
        $fired = 1;
        $totalFirings = count($this->map) * 2 + count($this->map[0]) * 2;
        echo "Firing {$totalFirings} times.\n";
        for ($row = 0;$row<count($this->map);$row++) {
            echo "Fire ".$fired++." of {$totalFirings}\n";
            $charges[] =  $this->fire(new Right(new Coordinate($row, -1)))->totalCharged(true);
            echo "Fire ".$fired++." of {$totalFirings}\n";
            $charges[] =  $this->fire(new Left(new Coordinate($row, count($this->map[$row]))))->totalCharged(true);
        }
        for ($column = 0;$column<count($this->map[0]);$column++) {
            echo "Fire ".$fired++." of {$totalFirings}\n";
            $charges[] =  $this->fire(new Down(new Coordinate(-1, $column)))->totalCharged(true);
            echo "Fire ".$fired++." of {$totalFirings}\n";
            $charges[] =  $this->fire(new Up(new Coordinate(count($this->map), $column)))->totalCharged(true);
        }

        return max($charges);
    }

    public function totalCharged(bool $remap = false): int
    {
        $charge = array_reduce(
            $this->map,
            fn(int $charged, array $row) => $charged + array_reduce(
                    $row,
                    fn(int $chargedInRow, Location $location) => $chargedInRow + ($location->isEnergized() ? 1 : 0),
                    0
                ),
            0
        );

        if ($remap) {
            $this->remap();
        }

        return $charge;
    }
}

$contraption = new Contraption('full.txt');

echo "Max charged " . $contraption->maximumCharge() . PHP_EOL;

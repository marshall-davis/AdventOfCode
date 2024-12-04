<?php

$input = fopen('input.txt', 'r+');

class wordsearch
{
    private readonly array $target;
    private int $found = 0;

    public function __construct(private array $grid)
    {
        $this->target = str_split('MAS');
    }

    public function search(): self
    {
        $xmas = [];
        foreach ($this->grid as $row => $line) {
            foreach ($line as $column => $value) {
                if ($value === $this->target[1]) {
                    $surrounding = [
                        'ne' => $this->grid[$row - 1][$column + 1] ?? null,
                        'n' => '.',
                        's' => '.',
                        'e' => '.',
                        'w' => '.',
                        'nw' => $this->grid[$row - 1][$column - 1] ?? null,
                        'sw' => $this->grid[$row + 1][$column - 1] ?? null,
                        'se' => $this->grid[$row + 1][$column + 1] ?? null,
                    ];


                    $filtered = array_filter(array_count_values(array_filter($surrounding,
                        fn(?string $letter) => in_array($letter, ['M', 'S']))),
                        fn(int $count) => $count === 2);
                    if (count($filtered) === 2) {

                        if ($surrounding['ne'] === $surrounding['sw'] || $surrounding['nw'] === $surrounding['se']) {
                            continue;
                        }
                        $this->printMatch($surrounding, $row, $column);
                        $this->found++;
                    } else {
                        echo "Did skip A at {$row}, {$column}\n";
                    }
                }
            }
        }

        return $this;
    }

    private function printMatch(array $surrounding, int $row, int $col): void
    {
        echo "At {$row},{$col}\n";
        echo "{$surrounding['nw']}{$surrounding['n']}{$surrounding['ne']}\n{$surrounding['w']}A{$surrounding['e']}\n{$surrounding['sw']}{$surrounding['s']}{$surrounding['se']}\n\n";
    }

    public function getFound(): int
    {
        return $this->found;
    }

    function searchFrom(int $x, int $y): void
    {
        if (
            $this->searchNE($x + 1, $y - 1)
            && ($this->searchNW($x + 1, $y + 1) || $this->searchSE($x - 1, $y - 1))
        ) {
            ++$this->found;
        }

        if (
            $this->searchNW($x + 1, $y + 1)
            && ($this->searchNE($x + 1, $y - 1) || $this->searchSW($x - 1, $y + 1))
        ) {
            ++$this->found;
        }

        if (
            $this->searchSE($x - 1, $y - 1)
            && ($this->searchSW($x - 1, $y + 1) || $this->searchNE($x + 1, $y - 1))
        ) {
            ++$this->found;
        }

        if (
            $this->searchSW($x - 1, $y + 1)
            && ($this->searchNW($x + 1, $y + 1) || $this->searchSE($x - 1, $y - 1))
        ) {
            ++$this->found;
        }
    }

    private function searchUp(int $x, int $y): self
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x -= 1][$y] ?? null) !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;

        return $this;
    }

    private function searchDown(int $x, int $y): self
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x += 1][$y] ?? null) !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchLeft(int $x, int $y): self
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x][$y -= 1] ?? null) !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchRight(int $x, int $y): self
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x][$y += 1] ?? null) !== $this->target[$pos]) {
                return $this;
            }
        }

        return $this;
    }

    private function searchNE(int $x, int $y): bool
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x -= 1][$y += 1] ?? null) !== $this->target[$pos]) {
                return false;
            }
        }

        return true;
    }

    private function searchSW(int $x, int $y): bool
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x += 1][$y -= 1] ?? null) !== $this->target[$pos]) {
                return false;
            }
        }

        return true;
    }

    private function searchNW(int $x, int $y): bool
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x -= 1][$y -= 1] ?? null) !== $this->target[$pos]) {
                return false;
            }
        }

        return true;
    }

    private function searchSE(int $x, int $y): bool
    {
        for ($pos = 1; $pos < count($this->target); $pos++) {
            if (($this->grid[$x += 1][$y += 1] ?? null) !== $this->target[$pos]) {
                return false;
            }
        }

        return true;
    }
}

$grid = [];

while (!feof($input)) {
    $grid[] = str_split(fgets($input));
}

$matches = (new wordsearch($grid))->search()->getFound();

echo $matches.PHP_EOL;

assert($matches < 1784);










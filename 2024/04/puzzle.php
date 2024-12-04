<?php

$input = fopen('input.txt', 'r+');

class wordsearch {
    private readonly array $target;
    private int $found = 0;
    public function __construct(private array $grid) {
        $this->target = str_split('XMAS');
    }

    public function search(): self {
        foreach ($this->grid as $row => $line) {
            foreach($line as $column => $value) {
                if ($value === 'X') {
                    $this->searchFrom($row, $column);
                }
            }
        }

        return $this;
    }

    public function getFound(): int {
        return $this->found;
    }

    function searchFrom(int $x, int $y): void
    {
        $this->searchRight($x, $y)
            ->searchLeft($x, $y)
            ->searchUp($x, $y)
            ->searchDown($x, $y)
            ->searchNE($x, $y)
            ->searchNW($x, $y)
            ->searchSE($x, $y)
            ->searchSW($x, $y);
    }

    private function searchUp(int $x, int $y):self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x-=1][$y]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;

        return $this;
    }

    private function searchDown(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x+=1][$y]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchLeft(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x][$y-=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchRight(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x][$y+=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchNE(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x-=1][$y+=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchSW(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x+=1][$y-=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchNW(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x-=1][$y-=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }

    private function searchSE(int $x, int $y): self {
        for ($pos = 1; $pos < 4; $pos++) {
            if (($this->grid[$x+=1][$y+=1]?? null)  !== $this->target[$pos]) {
                return $this;
            }
        }

        ++$this->found;
        return $this;
    }
}

$grid = [];

while (!feof($input)) {
    $grid[] = str_split(fgets($input));
}

echo (new wordsearch($grid))->search()->getFound().PHP_EOL;








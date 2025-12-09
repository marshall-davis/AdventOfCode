<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

readonly class Point implements Stringable
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    public function __toString(): string
    {
        return "({$this->x},{$this->y},{$this->z})";
    }
}

$points = [];
while (!feof($input)) {
    $points[] = new Point(...array_map('intval', explode(',', fgets($input))));
}

$maxArea = 0;
foreach ($points as $i => $p1) {
    foreach ($points as $j => $p2) {
        $area = (abs($p2->x - $p1->x)+1) * (abs($p2->y - $p1->y) +1);
        if ($area > $maxArea) {
            $maxArea = $area;
        }
    }
}

echo $maxArea.PHP_EOL;

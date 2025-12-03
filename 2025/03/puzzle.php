<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

class Joltage implements Stringable {
    public function __construct(public int $joltage, public ?int $position = null) {
    }


    public function __toString(): string
    {
        return json_encode(['joltage' => $this->joltage, 'position' => $this->position]);
    }
}

function largestDigit(string $in, bool $allowForLast = false): ?Joltage
{
    foreach(range(9,0) as $digit) {
        $position = false;
        if (($position = strpos($in, (string) $digit)) === false || ($allowForLast === false &&(int)$position === strlen($in) - 1)) {
            continue;
        }

        return new Joltage($digit, $position);
    }

    return null;
}

$joltages = [];
while (!feof($input)) {
    $line = trim(fgets($input));

    $first = largestDigit($line);
    $second = largestDigit(substr($line, $first->position + 1), true);

    $joltages[] = new Joltage((int)($first->joltage . $second->joltage));
}

foreach($joltages as $joltage) {
    echo $joltage . PHP_EOL;
}

echo 'Total joltage: ' .array_reduce($joltages, fn (int $carry, Joltage $item) => $carry + $item->joltage, 0) . PHP_EOL;

<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

class Battery implements Stringable
{
    public function __construct(
        private(set) int $joltage,
        private(set) bool $active = false
    ) {
    }


    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return ['joltage' => $this->joltage, 'active' => $this->active];
    }

    public function toggle(): Battery
    {
        $this->active = !$this->active;
        return $this;
    }
}

class Bank implements Countable, Stringable
{
    public function __construct(
        private array $batteries = [],
        private(set) int $maximumActive = 12
    ) {

    }

    public function insert(Battery $battery, ?int $position = null): Bank
    {
        array_splice($this->batteries, $position ?? count($this->batteries), 0, [$battery]);
        return $this;
    }

    public function batteries(?bool $active = null): array
    {
        return match ($active) {
            true => array_filter($this->toArray(), fn(Battery $battery) => $battery->active),
            false => array_filter($this->toArray(), fn(Battery $battery) => !$battery->active),
            default => $this->toArray()
        };
    }

    public function maximumJoltage(): string
    {
        $cursor = 0;
        while (count($this->batteries(true)) < $this->maximumActive) {
            $segment = array_slice($this->batteries(), $cursor,
                count($this->batteries(true)) - $this->maximumActive + 1 ?: null, preserve_keys: true);
            uasort($segment, fn(Battery $a, Battery $b) => $b->joltage <=> $a->joltage);;
            $cursor = array_key_first($segment);
            $this->battery($cursor++)->toggle();

        }
        return implode('', (array_column($this->batteries(true), 'joltage')));
    }

    public function toggle(int $position): Bank
    {
        $this->batteries[$position]->toggle();
        return $this;
    }

    static function fromString(string $from): Bank
    {
        $bank = new Bank;
        foreach (str_split($from) as $joltage) {
            $bank->insert(new Battery((int) $joltage));
        };
        return $bank;
    }

    public function count(): int
    {
        return count($this->batteries);
    }

    public function __toString(): string
    {
        return json_encode(array_map(fn(Battery $battery) => $battery->__toString(), $this->toArray()));
    }

    public function toArray(): array
    {
        return $this->batteries;
    }

    public function battery(int $position): Battery
    {
        return $this->batteries[$position];
    }
}

$answer = 0;
$maximums = [];
while (!feof($input)) {
    $maximums[] = Bank::fromString(trim(fgets($input)))->maximumJoltage();
    echo 'This bank has a maximum joltage of '.array_last($maximums).PHP_EOL;
    $answer += intval(array_last($maximums));;
}

echo 'Total joltage: '.$answer.PHP_EOL;

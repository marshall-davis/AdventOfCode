<?php

class Rucksack
{
    private readonly array $compartment;
    public function __construct(
        private readonly string $contents,
        private readonly int $numberOfCompartments = 2
    )
    {
        $totalItems = strlen($contents);
        $segmentLength = floor($totalItems /$this->numberOfCompartments);

        $this->load($contents, $segmentLength);
    }

    protected function load(string $contents, int $lengthOfSegments): void
    {
        $this->compartment = str_split($contents, $lengthOfSegments);
    }

    public function validate():object
    {
        $loaded = array_reduce(
            $this->compartment,
            fn (int|false $previous, $compartment) => ($previous && strlen($compartment) === $previous) ? strlen($compartment) : false,
            strlen($this->compartment[0])
        );

        $sharedInventory = array_intersect(...array_map('str_split', $this->compartment));
        $sharedInventory = array_pop($sharedInventory);

        return new class($loaded, $sharedInventory, $sharedInventory ? $this::itemPriority($sharedInventory) : null) {
            public function __construct(
                public readonly bool $loadedCorrectly = true,
                public readonly ?string $incorrectItem = null,
                public readonly ?int $incorrectPriority = null
            )
            {}
        };
    }

    public function fullContents(): string
    {
        return $this->contents;
    }

    public static function itemPriority(string $item): int
    {
        $value = ord(strtoupper($item)) - ord('A') +1;

        return ctype_upper($item) ? $value + 26 : $value;
    }
}

$rucksacks = [];
$inventories = fopen('input.txt', 'r');
$totalIncorrectPriority = 0;
$numberIncorrect = 0;
while (!feof($inventories)) {
    $inventory=trim(fgets($inventories));
    if (empty($inventory)) {
        continue;
    }

    $rucksack = new Rucksack($inventory);
    $validation = $rucksack->validate();
    $rucksacks[] = $rucksack;
    if ($validation->incorrectPriority) {
        $totalIncorrectPriority += $validation->incorrectPriority;
        $numberIncorrect++;
    } else {echo PHP_EOL.PHP_EOL;}
}
echo "Sum of incorrect priorities: {$totalIncorrectPriority}".PHP_EOL;

$badgeTotal = 0;
$groupNumber = 0;
$totalBadgePriority = 0;
while (($group = array_slice($rucksacks,$groupNumber * 3, 3)) && !empty($group)) {
    $commonItem = array_intersect(...array_map(fn(Rucksack $rucksack)=> str_split($rucksack->fullContents()),$group));
    $commonItem = array_pop($commonItem);
    $priority = Rucksack::itemPriority($commonItem);
    $groupNumber++;
    $totalBadgePriority += $priority;
}

echo "Total badge priority {$totalBadgePriority}.".PHP_EOL;

<?php

class Range implements Iterator
{
    private int $current;

    public function __construct(
        public readonly int $sourceStart,
        public readonly int $destinationStart,
        public readonly int $entries
    )
    {
        $this->current = $this->sourceStart;
    }

    public function minimumSource(): int
    {
        return $this->sourceStart;
    }

    public function maximumSource(): int
    {
        return $this->sourceStart + $this->entries - 1;
    }

    public function contains(int $needle): bool
    {
        return $needle >= $this->sourceStart && $needle < ($this->sourceStart + $this->entries);
    }

    public function leadsTo(int $destination): bool
    {
        return $destination >= $this->destinationStart && $destination < ($this->destinationStart + $this->entries);
    }

    #[\Override] public function current(): int
    {
        return $this->current;
    }

    #[\Override] public function next(): void
    {
        ++$this->current;
    }

    #[\Override] public function key(): mixed
    {
        return $this->current;
    }

    #[\Override] public function valid(): bool
    {
        return $this->current >= $this->sourceStart && $this->current < $this->sourceStart + $this->entries;
    }

    #[\Override] public function rewind(): void
    {
        $this->current = $this->sourceStart;
    }
}

class Map
{
    /** @var array<Range> */
    private array $ranges = [];

    public function __construct(string $map)
    {
        $input = fopen($map, 'r');
        while (!feof($input)) {
            $line = trim(fgets($input));
            if (empty($line)) {
                continue;
            }
            $this->mapRange(...array_map(fn(string $value) => (int)$value, explode(' ', $line)));
        }
    }

    public function isContiguous(): bool
    {
        return true;
    }

    public function mapRange(int $destinationStart, int $sourceStart, int $entries): void
    {
        $this->ranges[] = new Range($sourceStart, $destinationStart, $entries);
    }

    public function translate(int $source): int
    {
        foreach ($this->ranges as $range) {
            if ($range->contains($source)) {
                return $range->destinationStart + ($source - $range->sourceStart);
            }
        }

        return $source;
    }

    public function reverse(int $destination): ?int
    {
        foreach ($this->ranges as $range) {
            if ($range->leadsTo($destination)) {
                return $range->sourceStart + ($destination - $range->destinationStart);
            }
        }

        return null;
    }
}

readonly class IslandCoordinator
{
    private Map $seedToSoil;
    private Map $soilToFertilizer;
    private Map $fertilizerToWater;
    private Map $waterToLight;
    private Map $lightToTemperature;
    private Map $temperateToHumidity;
    private Map $humidityToLocation;

    public function __construct()
    {
        $this->seedToSoil = new Map('seed-to-soil.map');
        $this->soilToFertilizer = new Map('soil-to-fertilizer.map');
        $this->fertilizerToWater = new Map('fertilizer-to-water.map');
        $this->waterToLight = new Map('water-to-light.map');
        $this->lightToTemperature = new Map('light-to-temperature.map');
        $this->temperateToHumidity = new Map('temperature-to-humidity.map');
        $this->humidityToLocation = new Map('humidity-to-location.map');
    }

    function seedToLocation(int $seed): int
    {
        return $this->humidityToLocation->translate(
            $this->temperateToHumidity->translate(
                $this->lightToTemperature->translate(
                    $this->waterToLight->translate(
                        $this->fertilizerToWater->translate(
                            $this->soilToFertilizer->translate(
                                $this->seedToSoil->translate($seed)
                            )
                        )
                    )
                )
            )
        );
    }

    function locationToSeed(int $location): int
    {
        return $this->seedToSoil->reverse(
            $this->soilToFertilizer->reverse(
                $this->fertilizerToWater->reverse(
                    $this->waterToLight->reverse(
                        $this->lightToTemperature->reverse(
                            $this->temperateToHumidity->reverse(
                                $this->humidityToLocation->reverse($location)
                            )
                        )
                    )
                )
            )
        );

    }
}

assert((new IslandCoordinator)->seedToLocation(128801087) === 0);
assert((new IslandCoordinator)->locationToSeed(0) === 128801087);

$pairs = [];
preg_match_all('/\d+ \d+/', '1263068588 44436703 1116624626 2393304 2098781025 128251971 2946842531 102775703 2361566863 262106125 221434439 24088025 1368516778 69719147 3326254382 101094138 1576631370 357411492 3713929839 154258863', $pairs);
$seeds = [];
$pairs = array_map(
    fn(array $pair) => ['min' => $pair[0], 'max' => $pair[0] + $pair[1] - 1],
    array_map(
        fn(string $pair) => array_map(
            fn(string $value) => (int)$value,
            explode(' ', trim($pair))
        ),
        array_shift($pairs)
    )
);
usort(
    $pairs,
    fn(array $a, $b) => $a['max'] - $b['max']
);

// Maximum location is 1,017,149,391.
for ($location = 148062; $location <= PHP_INT_MAX; $location++) {
    echo "Checking location ". number_format($location)."\n";
    $seed = (new IslandCoordinator)->locationToSeed($location);
    foreach ($pairs as $pair) {
        if ($pair['min'] < $seed && $seed < $pair['max']) {
            echo "{$seed} is an initial seed at location {$location}.\n";
            exit;
        }
    }
}

exit;

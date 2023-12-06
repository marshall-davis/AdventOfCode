<?php

readonly class Range
{
    public function __construct(
        public int $sourceStart,
        public int $destinationStart,
        public int $entries
    )
    {
    }

    public function contains(int $needle): bool
    {
        return $needle > $this->sourceStart && $needle < ($this->sourceStart + $this->entries);
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
}

$seedToSoil = new Map('seed-to-soil.map');
$soilToFertilizer = new Map('soil-to-fertilizer.map');
$fertilizerToWater = new Map('fertilizer-to-water.map');
$waterToLight = new Map('water-to-light.map');
$lightToTemperature = new Map('light-to-temperature.map');
$temperateToHumidity = new Map('temperature-to-humidity.map');
$humidityToLocation = new Map('humidity-to-location.map');

foreach (array_map(
             fn(string $seed) => (int)$seed,
             explode(' ', '1263068588 44436703 1116624626 2393304 2098781025 128251971 2946842531 102775703 2361566863 262106125 221434439 24088025 1368516778 69719147 3326254382 101094138 1576631370 357411492 3713929839 154258863')
         ) as $seed) {
    $locations[] = $translated = $humidityToLocation->translate(
        $temperateToHumidity->translate(
            $lightToTemperature->translate(
                $waterToLight->translate(
                    $fertilizerToWater->translate(
                        $soilToFertilizer->translate(
                            $seedToSoil->translate($seed)
                        )
                    )
                )
            )
        )
    );
    echo sprintf(
        "Seed %d should be in Location: %d\n",
        $seed,
        $translated
    );
}

sort($locations);
echo array_shift($locations) . PHP_EOL;
echo "DONE\n";

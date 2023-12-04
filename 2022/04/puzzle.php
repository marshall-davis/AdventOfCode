<?php

$input = fopen('input.txt', 'r');
$pairs = [];

while (!feof($input)) {
    $pair = trim(fgets($input));
    if (empty($pair)) {
        continue;
    }
    $pairs[] = array_map(
        function (string $range) {
            [$min, $max] = explode('-', $range);
            return new class($min, $max) {
                public function __construct(
                    public readonly int $minimum,
                    public readonly int $maximum
                )
                {
                }

                public function __toString()
                {
                    return "{$this->minimum}-{$this->maximum}";
                }

                public function overlaps(self $other): bool
                {
                    $lowSideOverlap = $other->minimum <= $this->minimum && $this->minimum <= $other->maximum;
                    $highSideOverlap = $other->maximum >= $this->maximum && $this->maximum >= $other->minimum;

                    return $lowSideOverlap || $highSideOverlap;
                }

                public function encloses(self $other): bool
                {
                    return $this->minimum >= $other->minimum && $this->maximum <= $other->maximum;
                }
            };
        },
        explode(',', $pair)
    );
}

$overlapping = array_reduce(
    $pairs,
    fn ($total, $pair) => $pair[0]->overlaps($pair[1]) || $pair[1]->overlaps($pair[0]) ? ++$total : $total,
    0
);

$enclosed = array_reduce(
    $pairs,
    fn($total, $pair) => $pair[0]->encloses($pair[1]) || $pair[1]->encloses($pair[0]) ? ++$total : $total,
    0
) ?? 'WTF';

echo "Total overlapping: {$overlapping}.".PHP_EOL;
echo "Total enclosed: {$enclosed}.".PHP_EOL;

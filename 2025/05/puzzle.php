<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$fresh = [];
$ingredients = [];
while ($range = trim(fgets($input))) {
    preg_match('/(\d+)-(\d+)/', $range, $parts);
    $fresh[] = [$parts[1], $parts[2]];
}

function contains(array $haystack, array $needle): bool
{
    return $needle[0] >= $haystack[0] && $needle[1] <= $haystack[1];
}

assert(contains([131859446065085, 139731761639513], [134586353952964, 139731761639513]));

function overlaps(int|string $target, array $range): bool
{
    return $target >= $range[0] && $target <= $range[1];
}

assert(overlaps(23388191326133, [20529181418212, 29825483179707]));

function collapseRanges(array $ranges): ?array
{
    echo "Collapsing ".count($ranges)." ranges...\n";
    $collapsed = [$ranges[0]];
    foreach ($ranges as $i => $range) {
        foreach ($collapsed as $j => $collapsedRange) {
            // This is an existing range.
            if ($collapsedRange[0] === $range[0] && $collapsedRange[1] === $range[1]) {
                continue 2;
            }
            // A previous range is contained within this range.
            if (contains($range, $collapsedRange)) {
                $collapsed[$j] = $range;
                continue;
            }
            // The range is contained within a previous range.
            if (contains($collapsedRange, $range)) {
                continue 2;
            }
            // The range overlaps with the high end of a previous range.
            if (overlaps($collapsedRange[1], $range)) {
                $collapsed[$j] = [$collapsedRange[0], $range[1]];
                continue;
            }
            // The range overlaps with the low end of a previous range.
            if (overlaps($collapsedRange[0], $range)) {
                $collapsed[$j] = [$range[0], $collapsedRange[1]];
            }
        }

        // It's a new range.
        $collapsed[] = $range;
    }

    return count($collapsed) === count($ranges) ? null : $collapsed;
}

function joinRanges(array $ranges): array
{
    $result = [];
    foreach ($ranges as $range) {
        $end = array_last($result)[1] ?? null;
        if ($end && (($end + 1) === intval($range[0]))) {
            $result[array_key_last($result)] = [array_last($result)[0], $range[1]];
        } else {
            $result[] = $range;
        }
    }

    return $result;
}

echo "Starting with ".count($fresh)." ranges.\n";
while ($collapsed = collapseRanges($fresh)) {
    $fresh = $collapsed;
    uasort($fresh, fn($a, $b) => intval($a[1]) - intval($b[1]));
    $fresh = joinRanges(array_values($fresh));
}
echo "Collapsed to ".count($fresh)." ranges.\n";

foreach ($fresh as $line => $range) {
    assert(intval($range[0]) <= intval($range[1]));
}

//foreach ($fresh as $row => $range) {
//    echo "Range $row:\t{$range[0]}\t-\t{$range[1]}\n";
//}

$total = 0;
foreach ($fresh as $range) {
    $total += intval($range[1]) - intval($range[0]) + 1;
}

$fresh = array_values($fresh);

try {
    assert(count(array_unique(array_column($fresh, 0))) === count($fresh), 'Duplicated range starts.');
    assert(count(array_unique(array_column($fresh, 1))) === count($fresh), 'Duplicated range ends.');
    foreach ($fresh as $row => $range) {
        assert(intval($range[1]) < (intval($fresh[$row + 1][0] ?? PHP_INT_MAX)), "Row $row overlaps the next.\n");
    }
    assert($total < 437298360170255, "$total is too high!");
    assert($total !== 351022724181788, "$total is wrong!");
    assert($total !== 343860644368880, "$total is wrong!");
} catch (\AssertionError $e) {
    echo $e->getMessage().PHP_EOL;
    exit;
}

echo "There are $total fresh ingredients.\n";

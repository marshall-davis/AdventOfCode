<?php

declare(strict_types=1);

$pairs = explode(',',file_get_contents('input.txt'));
//$pairs = explode(',','11-22,95-115,998-1012,1188511880-1188511890,222220-222224,1698522-1698528,446443-446449,38593856-38593862,565653-565659,824824821-824824827,2121212118-2121212124');
$pairs = array_map('trim',$pairs);

$invalidIds = [];
$processed = 0;

foreach($pairs as $pair) {
    [$start,$end] = explode('-',$pair);
    $valid = [];

    foreach(range(intval($start),intval($end)) as $id) {
        $processed++;
        if (strlen((string)$id) === 1) {
            continue; // Cannot repeat a single digit.
        }
        if (count(array_unique(str_split((string)$id))) === 1) {
            $invalidIds[] = $id;
            continue;
        }

        for($length = 2;$length < strlen((string)$id);$length++) {
            $chunks = array_map('implode',array_chunk(str_split((string)$id), $length));
            $chunks = array_unique($chunks);
            if (count($chunks) === 1) {
                $invalidIds[] = $id;
                break;
            }
        }
        $valid[] = $id;
    }

    if (count($valid) === 0) {
        echo "No valid IDs for $pair.\n";
    }
//    echo implode("\n",$valid).PHP_EOL;
//    readline('Continue?');
}

$invalidIds = array_unique($invalidIds);
//echo implode("\n",$invalidIds).PHP_EOL;
echo 'Found '.count($invalidIds)." invalid IDs of $processed.".PHP_EOL;
echo 'Sum of invalid IDs: '.array_sum($invalidIds).PHP_EOL;
assert(array_sum($invalidIds) !== 45283684600);
assert(array_sum($invalidIds) > 20984710784);

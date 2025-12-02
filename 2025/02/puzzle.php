<?php

declare(strict_types=1);

$pairs = explode(',',file_get_contents('input.txt'));
$pairs = array_map('trim',$pairs);

$invalidIds = [];

foreach($pairs as $pair) {
    [$start,$end] = explode('-',$pair);

    foreach(range(intval($start),intval($end)) as $id) {
        if (strlen((string)$id) % 2 !== 0) {
            continue;
        }
        if (count(array_unique(str_split((string)$id))) === 1) {
            $invalidIds[] = $id;
            continue;
        }

        if (strlen((string)$id) === 2) {
            continue;
        }

        foreach(str_split((string)$id) as $position => $char) {
            $splits = array_unique(str_split((string)$id, $position + 1));
            if (count($splits) === 1 && strlen((string)$id) / strlen($splits[0]) == 2) {
                $invalidIds[] = $id;
            }
        }
    }
}
$invalidIds = array_unique($invalidIds);
echo implode("\n",$invalidIds).PHP_EOL;
echo 'Found '.count($invalidIds).' invalid IDs.'.PHP_EOL;
echo 'Sum of invalid IDs: '.array_sum($invalidIds).PHP_EOL;

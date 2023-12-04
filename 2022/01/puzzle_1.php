<?php

$input = fopen('puzzle_1_input.txt', 'r');
$elves = [[]];
$elfNumber = 0;

while (!feof($input)) {
    $line = rtrim(fgets($input), "\r\n");
    if (strlen($line) === 0) {
        $elves[++$elfNumber]=[];

        continue;
    }

    $elves[$elfNumber][]=intval($line);
}

$elves= array_filter($elves);

$totals = array_map(fn (array $inventory) => array_sum($inventory), $elves);

asort($totals);

$strongest=array_key_last($totals);
$max=$totals[$strongest];

// Part 1
echo "Maximum Calorie Carried by Elf {$strongest} with {$max}". PHP_EOL;

$topThree=0;

for ($i=0;$i<3;$i++) {
    $topThree += array_pop($totals);
}

// Part 2
echo "Total for top three is {$topThree}".PHP_EOL;

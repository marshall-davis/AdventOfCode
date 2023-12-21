<?php

$input = array_map(str_split(...),file('full.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
reset($input);

do {
    $delta = false;
    foreach ($input as $row => $line) {
        if (!isset($input[$row +1])) {
            break;
        }
        foreach ($line as $column => $occupant) {
            $intruder = $input[$row+1][$column];
            if ($intruder !== 'O') {
                continue; // These won't move
            }
            if ($occupant === '.') {
                $input[$row][$column] = $intruder;
                $input[$row+1][$column] = '.';
                $delta =true;
            }
        }
    }
//    foreach ($input as $line) {
//        echo implode('', $line). PHP_EOL;
//    }
//    echo "\n";
    reset($input);

}while($delta ?? false);

$pressure = 0;
foreach ($input as $row => $line) {
    $pressure += (array_count_values($line)['O']??0) * (count($input)-$row);
}

echo "Pressure: {$pressure}\n";

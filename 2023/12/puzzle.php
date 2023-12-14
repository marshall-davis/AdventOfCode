<?php
$input = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$input = [
'???.### 1,1,3',
'.??..??...?##. 1,1,3',
'?#?#?#?#?#?#?#? 1,3,1,6',
'????.#...#... 4,1,1',
'????.######..#####. 1,6,5',
'?###???????? 3,2,1',
];



foreach ($input as $line) {
    $combinations = 1;
    [$diagram, $groups] = explode(' ', $line);
    echo "{$diagram} has these groups: {$groups}\n";
    $groups = array_map(fn(string $value) => (int)$value, explode(',', $groups));

    $missing = [];
    preg_match_all('/[?#]+/', $diagram, $missing);
    while ($possibles = array_shift($missing)) {
        $pattern = array_shift($possibles);
        if (strlen($pattern) === $groups[0]) {
            unset($groups[0]);
            continue;
        }


    }
}

echo "$combinations\n";

<?php
function first_of(string $haystack, array $needles): ?string
{
    $first = [null, null];

    foreach ($needles as $needle) {
       if (($position = strpos($haystack, $needle)) !== false && ($first[0] === null ||$position < $first[0])) {
           $first[0] = $position;
           $first[1] = $needle;
       }
    }

    return $first[1];
}

function last_of(string $haystack, array $needles): ?string
{
    $last = [null, null];

    foreach ($needles as $needle) {
        if (($position = strrpos($haystack, $needle)) && ($last[0] === null ||$position > $last[0])) {
            $last[0] = $position;
            $last[1] = $needle;
        }
    }

    return $last[1];
}

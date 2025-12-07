<?php

declare(strict_types=1);

class AnsiColors
{
    public const RESET = "\033[0m";
    public const BOLD = "\033[1m";
    public const UNDERSCORE = "\033[4m";
    public const REVERSE = "\033[7m";

    // Foreground colors
    public const BLACK = "\033[0;30m";
    public const RED = "\033[0;31m";
    public const GREEN = "\033[0;32m";
    public const YELLOW = "\033[0;33m";
    public const BLUE = "\033[0;34m";
    public const MAGENTA = "\033[0;35m";
    public const CYAN = "\033[0;36m";
    public const WHITE = "\033[0;37m";

    // Light foreground colors
    public const LIGHT_RED = "\033[1;31m";
    public const LIGHT_GREEN = "\033[1;32m";
    public const LIGHT_YELLOW = "\033[1;33m";
    public const LIGHT_BLUE = "\033[1;34m";
    public const LIGHT_MAGENTA = "\033[1;35m";
    public const LIGHT_CYAN = "\033[1;36m";
    public const LIGHT_WHITE = "\033[1;37m";

    // Background colors
    public const BG_BLACK = "\033[40m";
    public const BG_RED = "\033[41m";
    public const BG_GREEN = "\033[42m";
    public const BG_YELLOW = "\033[43m";
    public const BG_BLUE = "\033[44m";
    public const BG_MAGENTA = "\033[45m";
    public const BG_CYAN = "\033[46m";
    public const BG_WHITE = "\033[47m";
}

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$map = [];
while (!feof($input)) {
    $map[] = str_split(trim(fgets($input)));
}

$timeline = array_find_key(array_shift($map), fn(string $cell) => $cell === 'S');
$timelines = 1;

/**
 * @param  int  $beam
 * @param  list<list<string>>  $map
 * @param  int  $spawn
 * @return int
 */
function track(int $beam, array $map, int &$spawn = 1): int
{
    if ($impact = array_find_key(array_column($map, $beam), fn(string $cell) => $cell === '^')) {
        $spawn++;
        track($beam + 1, array_slice($map, array_key_first($map) + $impact), $spawn);
        track($beam - 1, array_slice($map, array_key_first($map) + $impact), $spawn);
    }

    return $spawn;
}

$total = track($timeline, $map) + 1;
echo "\nTotal timelines: $total\n";

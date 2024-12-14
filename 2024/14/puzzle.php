<?php

$inputStream = fopen('input.txt', 'r+');

class Robot
{
    public function __construct(public int $y, public int $x, public int $horizontal, public int $vertical)
    {
    }
}

function display(array $map) {
    foreach ($map as $row) {
        echo implode(array_map(fn(int $c) => $c === 0 ? '.' : $c, $row))."\n";
    }
    echo PHP_EOL;
}

$height = 103;
$width = 101;
$time = 100;

$robots = [];
while (!feof($inputStream)) {
    $parameters = [];
    preg_match('/p=(?<px>-?\d+?),(?<py>-?\d+?) v=(?<vx>-?\d+?),(?<vy>-?\d+?)$/', trim(fgets($inputStream)),
        $parameters);
    $robots[] = new Robot($parameters['py'], $parameters['px'], $parameters['vx'], $parameters['vy']);
}

$grid = array_pad([], $height, array_pad([], $width, 0));

foreach ($robots as $robot) {
    $grid[$robot->x][$robot->y]++;
}

$low = null;
for ($t = 1; true; $t++) {
    /** @var Robot $robot */
    foreach ($robots as $robot) {
        $robot->x += $robot->horizontal;
        if ($robot->x >= $width) {
            $robot->x -= $width;
        }
        if ($robot->x < 0) {
            $robot->x = $width + $robot->x;
        }

        $robot->y += $robot->vertical;
        if ($robot->y >= $height) {
            $robot->y -= $height;
        }
        if ($robot->y < 0) {
            $robot->y = $height + $robot->y;
        }
    }


    $grid = array_pad([], $height, array_pad([], $width, 0));

    foreach ($robots as $robot) {
        $grid[$robot->y][$robot->x]++;
    }

    $q1 = 0;
    $q2 = 0;
    $q3 = 0;
    $q4 = 0;

    foreach ($robots as $robot) {
        if ($robot->x >= ceil($width / 2) && $robot->y < floor($height / 2)) {
            $q2++;
        }
        if ($robot->x < floor($width / 2) && $robot->y < floor($height / 2)) {
            $q1++;
        }
        if ($robot->x < floor($width / 2) && $robot->y >= ceil($height / 2)) {
            $q3++;
        }
        if ($robot->x >= ceil($width / 2) && $robot->y >= ceil($height / 2)) {
            $q4++;
        }
    }
    $safe = $q1 * $q2 * $q3 * $q4;
    if (is_null($low) || $safe < $low) {
        // Bunching up?
        echo "Seconds past $t\n";
        $low = $safe;
        display($grid);
    }
}
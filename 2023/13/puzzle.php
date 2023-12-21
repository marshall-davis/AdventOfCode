<?php

$contents = file('full.txt', FILE_IGNORE_NEW_LINES);

$patterns = [];
while (($separator = array_search('', $contents)) !== false) {
    $patterns[] = array_map(str_split(...), array_splice($contents, 0, $separator));
    array_shift($contents);
}
$patterns[] = array_map(str_split(...),$contents);
unset($contents);
$score = 0;

class Map {
    public function __construct(private readonly array $map) {

    }

    public function verticalReflection(): int|false
    {
        $width = count($this->map[0]);

        for ($i = 0;$i < $width;$i++) {
            $left = [];
            $right = [];
            $l = 0;
            $r = $i + 1;
            while ($l <= $i) {
                $left[] = array_column($this->map, $l++);
            }
            while (count($right) < count($left) && $r < $width) {
                $right[] = array_column($this->map, $r++);
            }
            while (count($left) > count($right)) {
                array_shift($left);
            }

            if (!empty($left) && !empty($right) && array_reverse($left) == $right) {
                echo "Reflected vertically at $i\n";
                return ++$i;
            }
        }

        return false;
    }

    public function horizontalReflection(): int|false
    {
        $height = count($this->map);

        for ($i = 0;$i < $height;$i++) {
            $top = [];
            $bottom = [];
            $t = 0;
            $b = $i + 1;
            while ($t <= $i) {
                $top = array_slice($this->map,0, ++$t);
            }
            while (count($bottom) < count($top) && $b < $height) {
                $bottom = array_slice($this->map,count($top), $b++);
            }
            while (count($top) > count($bottom)) {
                array_shift($top);
            }

            foreach(array_map(fn (array $piece) => implode('', $piece), $top) as $line) {echo "$line\n";}
            echo "----------------\n";
            foreach(array_map(fn (array $piece) => implode('', $piece), $bottom) as $line) {echo "$line\n";}
            echo "\n\n";


            if ($top == array_reverse($bottom)) {
                echo "Reflected horizontally at $i\n";
                return ++$i;
            }
        }

        return false;
    }

    public function score(): int
    {
        if ($vertical = $this->verticalReflection()) {
            return $vertical;
        }

        return 100 * $this->horizontalReflection();
    }
}

foreach ($patterns as $id => $pattern) {
    $scores += $score = (new Map($pattern))->score();
    echo "Pattern $id scored " . $score . PHP_EOL.PHP_EOL;
}

echo "DONE\nScored {$scores}\n";

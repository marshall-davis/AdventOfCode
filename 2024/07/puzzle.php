<?php

$input = fopen('input.txt', 'r+');

$sum = 0;

function buildOperators(array $operators,int $length, ?array $list = null): array
{
    $list ??= $operators;

    if ($length < 1) {
        return $list;
    }

    $additional = [];
    foreach ($list as $option) {
        foreach ($operators as $operator) {
            $additional[] = $option.$operator;
        }
    }

    return buildOperators($operators,$length-1,$additional);
}


function check(int $goal, array $operators, array $operands): int
{
    foreach ($operators as $operatorPattern) {
        $result = 0;
        foreach ($operands as $position => $operand) {
            if ($position === 0) {
                $result = $operand;
                continue;
            }
            $result = match ($operatorPattern[$position - 1]) {
                '+' => $result + $operand,
                '*' => $result * $operand,
                default => throw new RuntimeException("Unexpected operator '$operatorPattern[$position]'"),
            };


        }

        if ($result == $goal) {
            echo "Got one! $result\n";
            return $result;
        }
    }

    return 0;
}

$iteration=1;
while (!feof($input)) {
    echo 'Parsing line ' . $iteration++.PHP_EOL;
    $line = explode(':', fgets($input));
    $target = $line[0];
    $operands = explode(' ', trim($line[1]));

    $sum += check($target, buildOperators(['+','*'], count($operands)-1), $operands);
}

echo "$sum\n";
<?php

$input = fopen('input.txt', 'r+');

$sum = 0;

function buildOperators(array $operators, int $length, ?array $list = null): array
{
    $list ??= $operators;

    if ($length == 1) {
        return $list;
    }

    $additional = [];
    foreach ($list as $option) {
        foreach ($operators as $operator) {
            $additional[] = $option.$operator;
        }
    }

    return buildOperators($operators, $length - 1, $additional);
}


function check(int $goal, array $operators, array $operands): int
{
    $operatorsIterator = new ArrayIterator($operators);
    $operandIterator = new ArrayIterator($operands);

    while ($operatorsIterator->valid()) {
        echo "Let's try ". $operatorsIterator->current().PHP_EOL;
        $operandIterator->rewind();
        $operatorIterator = new ArrayIterator(str_split($operatorsIterator->current()));
        $result = $operandIterator->current();
        while ($operatorIterator->valid()) {
            $operandIterator->next();
            match ($operatorIterator->current()) {
                '+' => function () use (&$result, $operandIterator) {
                    // Add the current operand to the next.
                    $result += $operandIterator->current();
                    // That was used, so move on
                    $operandIterator->next();
                },
                '|' => function () use (&$result, $operandIterator) {
                    // This is the tricky one.
                    // The current operand is set to a concatenation of it and the next.
                    $current = $operandIterator->current();
                    $operandIterator->next();
                    $current .= $operandIterator->current();
                    $operandIterator->offsetSet($operandIterator->key(), $current);
                    // The current value is correct, no need to advance the pointer
                },
                '*' => function () use (&$result, $operandIterator) {
                    // Add the current operand to the next.
                    $result *= $operandIterator->current();
                    // That was used, so move on
                    $operandIterator->next();
                }
            };

            // Alright, deal with the next problem.
            $operatorIterator->next();

            if ($result == $goal) {
                echo "Got one! $result\n";
                return $result;
            } else {
                echo "$result is not $goal! \n";
            }
        }

        // Next set of problems
        $operatorsIterator->next();
    }

    return 0;
}

while (!feof($input)) {
    $line = explode(':', fgets($input));
    $target = $line[0];
    $operands = explode(' ', trim($line[1]));

    $sum += check($target, array_filter(buildOperators(['+', '*', '|'], count($operands) - 1),
        fn(string $ops) => str_ends_with($ops, '|') === false), $operands);

    echo "TICK!\n\n";
}

assert($sum > 2002330);

echo "$sum\n";
<?php

$inputStream = fopen(__DIR__.'/input.txt', 'r+');

function verifyPartOne($stream): void
{
    $sum = 0;
    while (!feof($stream)) {
        $input = explode(':', fgets($stream));
        $target = $input[0];
        $operands = explode(' ', trim($input[1]));

        $sum += check($target, buildOperators(['+', '*'], count($operands) - 1), $operands, verbose: false);
    }

    // Verifies that part one remains intact, so we know + and * work.
    assert($sum === 6083020304036);
    rewind($stream);
}

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

function addition(ArrayIterator $operands, int $initial): string
{
    /**
     * For addition, simply consume the next operand with this operator.
     */
    $initial = bcadd($initial, $operands->current());
    $operands->next();

    return $initial;
}

function multiplication(ArrayIterator $operands, int $initial): string
{
    /**
     * For multiplication, we simply consume the next operand with this operator
     */
//    echo "$initial * ".$operands->current();
    $initial = bcmul($initial, $operands->current());
//    echo " = $initial\n";
    $operands->next();

    return $initial;
}

function concatenate(ArrayIterator $operands, int $result): string
{
    /**
     * This is the part 2 twist.
     * In this case we need to concatenate the current operand with the next.
     * Then we replace the current operand with the concatenated operands
     * Then consume concatenation operator.
     */
    $result .= $operands->current();
    $operands->next();

    return $result;
}

function check(int $goal, array $operators, array $operands, bool $verbose = true): int
{
    /**
     * $operators is of type array<string> where each index
     * is a list of operators to use.
     */
    $attemptIterator = new ArrayIterator($operators);

    /**
     * For every set of operators we need to build and test the resulting formula.
     */
    while ($attemptIterator->current() !== null) {
        /**
         * $operands is of the type array<int> where each
         * integer is an operand in the final formula
         */
        $operandIterator = new ArrayIterator($operands);
        $operandIterator->rewind(); // For each attempt start with the first operand
        $result = $operandIterator->current(); // Stores the attempt's result
        $operandIterator->next();

        /**
         * Now we have to decide what to with that, and for that we need an operator.
         * This will be one of +, *, or |
         *
         * We do this by first breaking the attempt into the type array<string> and creating an
         * iterator for that.
         */
        $operators = new ArrayIterator(str_split($attemptIterator->current()));
        while ($operators->current() !== null) {
            match ($operators->current()) {
                '+' => $result = addition($operandIterator, $result),
                '*' => $result = multiplication($operandIterator, $result),
                '|' => $result = concatenate($operandIterator, $result),
            };
            $operators->next();
        }

        if ($result == $goal) {
            // If the result matches the target, we have a valid formula.
            if ($verbose) {echo "Got one! $result\n";}
            // Can stop here, we only need one valid formula per line
            return $result;
        } else {
//            echo "$result is not $goal! \n";
        }

        /**
         * Move on, somewhat dejectedly, to the next attempt.
         */
        $attemptIterator->next();
    }

    return 0;
}

verifyPartOne($inputStream);

$sum = 0;
$line = 0;
while (!feof($inputStream)) {
    echo 'Working on line '.++$line." ";
    $input = explode(':', fgets($inputStream));
    $target = $input[0];
    $operands = explode(' ', trim($input[1]));
    echo 'with ' . count($operands) . " operands\n";

    $sum += check($target, buildOperators(['+', '*', '|'], count($operands) - 1), $operands);
}

echo "$sum\n";

if ($sum <= 6083024090805) {
    echo "TOO LOW!\n";
}
<?php

declare(strict_types=1);

$input = fopen('input.txt', 'r+');
//$input = fopen('example.txt', 'r+');

$lines = [];
while (!feof($input)) {
    $lines[] = rtrim(fgets($input), "\t\r\n\0\x0B");
}
$operators = preg_split('/\s+/', trim(array_pop($lines)), flags: PREG_SPLIT_OFFSET_CAPTURE);

$results = [];
foreach ($operators as $problem => $operator) {
    $operands = array_map(fn($operand) => substr($operand, $operator[1],
        array_key_last($operators) === $problem ? null : ($operators[$problem + 1][1] - $operator[1] -1)), $lines);
    $results[] = match ($operator[0]) {
        '+' => array_sum(cephalopod($operands)),
        '*' => array_product(cephalopod($operands)),
        default => throw new Exception('Invalid operator.')
    };
}

/**
 * @param  list<string>  $operands
 * @return array
 */
function cephalopod(array $operands): array
{
    $cephalopodOperands = [];

    $operands = array_map(function (string $operand) {
        return array_map(fn (string $segment) => $segment !== ' ' ? $segment : null, str_split($operand));
    }, $operands);
    $longest = max(array_map('count', $operands));
    for ($i = $longest - 1; $i >= 0; $i--) {
        $cephalopodOperands[] = implode('', array_column($operands, $i));
    }

    return $cephalopodOperands;
}

echo "Total: ".array_sum($results)."\n";

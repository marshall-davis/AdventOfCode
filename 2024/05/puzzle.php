<?php

$input = fopen('input.txt', 'r+');

class Rule implements Stringable
{
    public function __construct(public int $first, public int $second)
    {
    }

    public function matches(string $update): bool
    {
        if (($second = strpos($update, $this->second)) === false) {
            return true;
        }
        return strpos($update, $this->first) <= $second;
    }

    public function __toString(): string
    {
        return "{$this->first}|{$this->second}";
    }
}

$rules = [];

while (!feof($input)) {
        $line = trim(fgets($input));
        if ($line === '') {
            break;
        }
        $rules[]=new Rule(...explode("|", $line));
}

$correct = [];
$sum =0;
while (!feof($input)) {
    $update = trim(fgets($input));
    /** @var Rule $rule */
    foreach ($rules as $rule) {
        if (!$rule->matches($update)) {
            echo "$update did not match $rule\n";
            continue 2;
        }
    }
    echo "$update is correct\n";
    $correct[] = $update;
    $updateArray = explode(",", $update);
    $added = (int)$updateArray[intval(count($updateArray)/2)];
    echo 'Adding ' . $added . "\n";
    $sum += $added;

}
echo print_r($correct, true).PHP_EOL;
echo $sum.PHP_EOL;
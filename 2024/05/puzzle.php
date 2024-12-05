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
$wrong = [];
$sum =0;
while (!feof($input)) {
    $update = trim(fgets($input));
    /** @var Rule $rule */
    foreach ($rules as $rule) {
        if (!$rule->matches($update)) {
            $wrong[] = $update;

            continue 2;
        }
    }
}

function fix(array &$update, array $rules): void
{
    do {
        $changes = false;
        foreach ($rules as $rule) {
            if (!$rule->matches(implode(',', $update))) {
                $second = array_search($rule->second, $update);
                $first = array_search($rule->first, $update);

                $update[$first] = $rule->second;
                $update[$second] = $rule->first;
                $changes = true;
            }
        }
    } while ($changes);
}

foreach ($wrong as $update) {
    $update = explode(',', $update);
    fix($update, $rules);

    $sum +=(int)$update[intval(count($update)/2)];
}

assert($sum < 5423);

echo $sum.PHP_EOL;
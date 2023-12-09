<?php

class Node
{
    public function __construct(
        public readonly string $name,
        public ?Node           $left = null,
        public ?Node           $right = null
    )
    {
    }

    public function attach(string $direction, Node $node): self
    {
        $this->$direction = $node;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

$input = fopen('input.txt', 'r');
// First line is instructions.
$instructions = str_split(trim(fgets($input)));
// Second line is trash.
fgets($input);

// Now we build our network.
echo "Building nodes...\n";
$paths = [];
while (!feof($input)) {
    $definition = trim(fgets($input));

    if (empty($definition)) {
        continue;
    }

    // A definition is name = (left, right)
    $parts = [];
    preg_match('/(?<name>\w+) = \((?<left>\w+), (?<right>\w+)\)/', $definition, $parts);
    foreach ([$parts['name'], $parts['left'], $parts['right']] as $nodeName) {
        $$nodeName ??= new Node($nodeName);
    }
    ${$parts['name']}->attach('left', ${$parts['left']})->attach('right', ${$parts['right']});
    if (str_ends_with($parts['name'], 'A')) {
        $paths[$parts['name']] = ${$parts['name']};
    }
}
assert(count($paths) === 6); // Counted from input.
assert(isset($ZZZ));
echo "Network complete.\n";

$reachedDestination = false;
foreach ($paths as $path => $pathNode) {
    echo "Tracing path {$path}.\n";
    $steps = 0;
    $reachedDestination = false;
    while (!$reachedDestination) {
        foreach ($instructions as $direction) {
            ++$steps;
            $pathNode = $direction === 'R' ? $pathNode->right : $pathNode->left;
            if (str_ends_with($pathNode->name, 'Z')) {
                echo "{$path} has {$steps} steps.\n";
                $paths[$path] = $steps;
                $reachedDestination = true;
                break;
            }
        }
    }
}
echo 'Convergence at ' . array_reduce(
    $paths,
    fn (int|GMP $firstFactor, int $secondFactor) => gmp_lcm($firstFactor, $secondFactor),
        $paths[array_key_first($paths)]
    );
echo PHP_EOL;
exit;

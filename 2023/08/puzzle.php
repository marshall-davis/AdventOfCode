<?php

class Node {
    public function __construct(
        public readonly string $name,
        public ?Node $left = null,
        public ?Node $right = null
    )
    {}

    public function attach(string $direction, Node $node): self
    {
        $this->$direction = $node;

        return $this;
    }

    public function __toString(): string
    {
        return "{$this->name} goes left to {$this->left->name} and right to {$this->right->name}.\n";
    }
}

$input = fopen('input.txt', 'r');
// First line is instructions.
$instructions = str_split(trim(fgets($input)));
// Second line is trash.
fgets($input);

// Now we build our network.
echo "Building nodes...\n";
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
}
assert(isset($ZZZ));
echo "Network complete.\n";
$currentNode = $AAA;
$steps = 0;
while ($currentNode->name !== 'ZZZ') {
    foreach ($instructions as $direction) {
        $previousNode = $currentNode;
        $currentNode = $direction === 'R' ? $currentNode->right : $currentNode->left;
        echo "Moving {$direction} from {$previousNode->name} to {$currentNode->name}\n";
        $steps++;
        if ($currentNode->name === 'ZZZ') {
            echo "Made it to ZZZ!\n";
            break;
        }
    }
}
assert($currentNode->name === 'ZZZ');
assert($steps > 293);
assert($steps < 13772);
echo "Steps: {$steps}\n";

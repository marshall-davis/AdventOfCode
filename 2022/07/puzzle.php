<?php

interface Node
{
    public function size(): int;

    public function isDir(): bool;

    public function name(): string;

    public function parent(): ?Dir;
}

trait Linked
{
    private readonly ?Dir $parent;

    public function parent(): ?Dir
    {
        return $this->parent;
    }
}

class File implements Node
{
    use Linked;

    public function __construct(
        private readonly string $name,
        private readonly ?int    $size,
        private readonly ?Dir $parent
    )
    {
    }

    public function size(): int
    {
        return $this->size;
    }

    public function isDir(): bool
    {
        return false;
    }

    public function name(): string
    {
        return $this->name;
    }
}

class Dir implements Node
{
    use Linked;

    public function __construct(
        private readonly string $name,
        private readonly ?Dir $parent,
        private array  $contents = []
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function retrieve(string $name): ?Node
    {
        return $this->contents[$name] ?? null;
    }

    public function store(Node $node): Node
    {
        $this->contents[$node->name()] = $node;

        return $node;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function size(): int
    {
        return 0;
    }

    public function isDir(): bool
    {
        return true;
    }
}

class Disk
{
    public readonly Dir $root;

    public function __construct()
    {
        $this->root = new Dir('/',null);
    }
}

class Filesystem
{
    private readonly Disk $disk;
    private Dir $currentWorkingPath;

    public function __construct(private readonly bool $mutable = false)
    {
        $this->disk = new Disk();
        $this->currentWorkingPath = $this->disk->root;
    }

    public function changeDirectory(string $to): void
    {
        match ($to) {
            '..' => $this->traverseUp(),
            '/' => $this->currentWorkingPath = $this->disk->root,
            default => $this->updateWorkingPath($to)
        };

        echo "New working path {$to}\n";
    }

    public function list(): void
    {
        echo "Listing" . implode("\n\t", $this->currentWorkingPath->contents()) . PHP_EOL;
    }

    protected function traverseUp(): void
    {
        if ($this->currentWorkingPath->parent() === null) {
            return;
        }

        $this->currentWorkingPath = $this->currentWorkingPath->parent();
    }

    protected function updateWorkingPath(string $to): void
    {
        $next = $this->currentWorkingPath->retrieve($to);

        if ($next === null && !$this->mutable) {
            throw new RuntimeException("Invalid path: {$to}\n");
        }

        if ($next?->isDir() === false) {
            throw new RuntimeException("{$to} is not a directory.");
        }

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->currentWorkingPath = $newt ?? $this->currentWorkingPath->store(new Dir($to, $this->currentWorkingPath));
    }

    public function touch(string $name, ?int $size = null): void
    {
        echo "Creating file {$name}\n";
        $this->currentWorkingPath->store(new File($name, $size, $this->currentWorkingPath));
    }

    public function mkdir(string $name): void
    {
        echo "Creating directory {$name}\n";
        $this->currentWorkingPath->store(new Dir($name, $this->currentWorkingPath));
    }

    public function find(Closure $filter, ?Dir $startingIn = null): array
    {
        $results = [];

        $directory = $startingIn ?? $this->disk->root;
        /** @var Node $node */
        foreach ($directory->contents() as $node) {
            if ($node->isDir()) {
                /** @noinspection PhpParamsInspection */
                $results = $results+$this->find($filter, $node);
            }

            if ($filter($node)) {
                $results[] = $node;
            }
        }
    }
}

$log = fopen('input.txt', 'r');
$fs = new Filesystem(mutable: true);

while (!feof($log)) {
    $line = trim(fgets($log));
    if (empty($line)) {
        continue;
    }

    if (stripos($line, '$') === 0) {
        $parts = explode(' ', $line);
        $command = $parts[1] ?? null;
        $argument = $parts[2] ?? null;
        match ($command) {
            'cd' => $fs->changeDirectory($argument),
            'ls' => $fs->list()
        };
        continue;
    }

    [$type, $name] = explode(' ', $line);

    if (is_numeric($type)) {
        $fs->touch($name, $type);
        continue;
    }

    $fs->mkdir($name);
}

$results = $fs->find(fn(Node $node)=> $node->isDir() && $node->size() >= 10000);

<?php

readonly class Part
{
    public function __construct(
        public int $number,
        public int $line,
        public int $offset
    )
    {
        echo "Creating Part {$this->number} at ({$this->line},{$this->offset}).\n";
    }

    public function touches(Gear $gear): bool
    {
        if (!in_array($gear->line, [$this->line, $this->line - 1, $this->line + 1])) {
            return false;
        }

        if ($this->offset - 1 > $gear->offset || $this->offset + strlen($this->number) < $gear->offset) {
            return false;
        }

        return true;
    }
}

class Gear
{
    public array $sprockets = [];

    public function __construct(
        public readonly int $line,
        public readonly int $offset
    )
    {
        echo "Creating Gear at ({$this->line},{$this->offset}).\n";
    }

    public function attach(Part $part): void
    {
        echo 'Attaching Part ' . $part->number . ' to Gear (' . implode(',', [$this->line, $this->offset]) . ')' . PHP_EOL;
        $this->sprockets[] = $part;
    }

    public function ratio(): int
    {
        return array_reduce($this->sprockets, fn($carry, $sprocket) => $carry * $sprocket->number, 1);
    }
}

class Engine
{
    private $schematic;
    private array $lines = [];
    private array $parts = [];

    private array $gears = [];

    private bool $parsed = false;

    public function __construct(string $schematic)
    {
        $this->schematic = fopen($schematic, 'r');
        while (!feof($this->schematic)) {
            $this->lines[] = rtrim(fgets($this->schematic));
        }
    }

    public function partsList(): array
    {
        $this->parsed === true ?: $this->parse();

        return $this->parts;
    }

    private function parse(): void
    {
        $partStart = null;
        foreach ($this->lines as $line => $content) {
            foreach (mb_str_split($content) as $offset => $character) {
                if (is_numeric($character)) {
                    $partStart ??= $offset;
                } elseif ($partStart !== null) {
                    if ($part = $this->isPart($line, $partStart, $offset - $partStart)) {
                        $this->parts[] = $part;
                        $part = null;
                    }
                    $partStart = null;
                }

                if ($character === '*') {
                    $this->gears[] = new Gear($line, $offset);
                }
            }

            if ($partStart) {
                if ($part = $this->isPart($line, $partStart)) {
                    $this->parts[] = $part;
                    $part = null;
                }
                $partStart = null;
            }
        }

        $this->parts = array_filter($this->parts);
        foreach ($this->gears as $gear) {
            foreach ($this->parts as $part) {
                if ($part->touches($gear)) {
                    $gear->attach($part);
                }
            }
        }
        $this->gears = array_filter($this->gears, fn(Gear $gear) => count($gear->sprockets) === 2);
        $this->parsed = true;
    }

    private function isPart(int $line, int $offset, ?int $length = null): Part|false
    {
        $pattern = '/[^\w\s\d.]/';
        $above = substr($this->lines[$line - 1] ?? '', $offset < 1 ? $offset : $offset - 1, $length === null ? null : $length + 2);
        $below = substr($this->lines[$line + 1] ?? '', $offset < 1 ? $offset : $offset - 1, $length === null ? null : $length + 2);
        $before = $offset < 1 ? null : substr($this->lines[$line], $offset - 1, 1);
        $after = $length === null ? null : substr($this->lines[$line], $offset + $length, 1);
        if (preg_match($pattern, $above) === 1) {
            return new Part((int)substr($this->lines[$line], $offset, $length), $line, $offset);
        }
        if (preg_match($pattern, $below) === 1) {
            return new Part((int)substr($this->lines[$line], $offset, $length), $line, $offset);
        }
        if (preg_match($pattern, $before ?? '') === 1) {
            return new Part((int)substr($this->lines[$line], $offset, $length), $line, $offset);
        }
        if (preg_match($pattern, $after ?? '') === 1) {
            return new Part((int)substr($this->lines[$line], $offset, $length), $line, $offset);
        }

        return false;
    }

    public function gears(): array
    {
        return $this->gears;
    }
}

$engine = new Engine('input.txt');
assert(539713 === array_reduce($engine->partsList(), fn($carry, $item) => $carry + $item->number));

echo array_reduce($engine->gears(), fn($carry, $gear) => $carry + $gear->ratio(), 0) . PHP_EOL;

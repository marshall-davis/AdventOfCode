<?php

class Engine
{
    private $schematic;
    private array $lines = [];
    private array $parts = [];

    private bool $parsed = false;

    public function __construct(string $schematic)
    {
        $this->schematic = fopen($schematic, 'r');
        while (!feof($this->schematic)) {
            $this->lines[] = rtrim(fgets($this->schematic));
        }
    }

    private function parse(): void
    {
        $partStart = null;
        foreach ($this->lines as $line => $content) {
            echo "LINE ".($line+1).PHP_EOL;
            foreach (mb_str_split($content) as $offset => $character) {
                if (is_numeric($character)) {
                    $partStart ??= $offset;
                } elseif ($partStart !== null) {
                    $this->parts[] = $this->isPart($line, $partStart, $offset - $partStart);
                    $partStart = null;
                }
            }

            if ($partStart) {
                $this->parts[] = $this->isPart($line, $partStart);
                $partStart = null;
            }
        }

        $this->parts = array_filter($this->parts);
        $this->parsed = true;
    }

    private function isPart(int $line, int $offset, ?int $length = null): int|false
    {
        $pattern = '/[^\w\s\d.]/';
        $above = substr($this->lines[$line - 1] ?? '', $offset < 1 ? $offset : $offset - 1, $length === null ? null:$length + 2);
        $below = substr($this->lines[$line + 1] ?? '', $offset < 1 ? $offset : $offset - 1, $length === null ? null:$length + 2);
        $before=$offset < 1 ? null : substr($this->lines[$line], $offset - 1, 1);
        $after=$length === null ? null:substr($this->lines[$line], $offset +$length, 1);
        $box = $above.PHP_EOL.$before.substr($this->lines[$line], $offset, $length).$after.PHP_EOL.$below. PHP_EOL;
        if (preg_match($pattern, $above) === 1) {
            return (int)substr($this->lines[$line], $offset, $length);
        }
        if (preg_match($pattern, $below) === 1) {
            return (int)substr($this->lines[$line], $offset, $length);
        }
        if (preg_match($pattern, $before ?? '') === 1) {
            return (int)substr($this->lines[$line], $offset, $length);
        }
        if (preg_match($pattern, $after ?? '') === 1) {
            return (int)substr($this->lines[$line], $offset, $length);
        }

        echo '--------'.PHP_EOL.$box.'--------'.PHP_EOL;
        return false;
    }

    public function partsList(): array
    {
        $this->parsed === true ?: $this->parse();

        return $this->parts;
    }
}

$engine = new Engine('input.txt');
echo array_sum($engine->partsList()).PHP_EOL;

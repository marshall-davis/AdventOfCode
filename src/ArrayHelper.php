<?php

namespace Marshall\AdventOfCode;

class ArrayHelper
{
    public static function contains(mixed $needle, array $haystack): bool
    {
        foreach ($haystack as $item) {
            if ($needle === $item) {
                return true;
            }

            if (is_array($item)) {
                if (self::contains($needle, $item)) {
                    return true;
                }
            }
        }

        return false;
    }
}

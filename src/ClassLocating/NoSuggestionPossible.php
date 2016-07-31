<?php
namespace Dkplus\Formica\ClassLocating;

use InvalidArgumentException;

final class NoSuggestionPossible extends InvalidArgumentException
{
    public static function forClass(string $className) : NoSuggestionPossible
    {
        return new static("Could not suggest a file for class '$className'");
    }
}

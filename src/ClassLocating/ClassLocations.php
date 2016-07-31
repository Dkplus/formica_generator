<?php
namespace Dkplus\Formica\ClassLocating;

interface ClassLocations
{
    public function locate(string $class) : array;

    public function suggestFile(string $class) : string;
}

<?php

namespace Dkplus\Formica\ClassLocating;

final class ComposerClassLocationDetector
{
    public function detectFromDirectory(string $directory) : Psr4ClassLocations
    {
        $psr4 = [];
        if (is_dir($directory) && is_file("$directory/composer.json")) {
            $composer = array_merge_recursive(
                ['autoload' => ['psr-4' => []], 'autoload-dev' => ['psr-4' => []]],
                json_decode(file_get_contents("$directory/composer.json"), true) ?: []
            );
            $psr4 = array_merge(
                $psr4,
                $composer['autoload']['psr-4'],
                $composer['autoload-dev']['psr-4']
            );
        }
        return new Psr4ClassLocations($directory, $psr4);
    }
}

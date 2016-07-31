<?php
namespace Dkplus\Formica\ClassLocating;

final class Psr4ClassLocations implements ClassLocations
{
    /** @var string[] */
    private $psr4;

    /**
     * @param string $basePath
     * @param string[] $psr4Paths Prefix as key, directory as value
     */
    public function __construct(string $basePath, array $psr4Paths)
    {
        $this->psr4 = array_map(function (string $eachPath) use ($basePath) {
            return "$basePath/$eachPath";
        }, $psr4Paths);
    }

    public function locate(string $class) : array
    {
        $psr4 = array_filter($this->psr4, function (string $prefix) use ($class) {
            return strpos($class, $prefix) === 0;
        }, ARRAY_FILTER_USE_KEY);

        return array_map(function (string $prefix, string $directory) use ($class) {
            return $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        }, array_keys($psr4), $psr4);
    }

    public function suggestFile(string $class) : string
    {
        $locations = $this->locate($class);
        if (! $locations) {
            throw NoSuggestionPossible::forClass($class);
        }
        return current($locations);
    }
}

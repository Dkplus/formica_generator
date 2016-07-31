<?php
namespace spec\Dkplus\Formica;

use Dkplus\Reflections\Builder;
use Dkplus\Reflections\ClassReflection as ReflectionClassReflection;
use InvalidArgumentException;
use Memio\Memio\Config\Build;
use Memio\Model\Contract;
use Memio\Model\File;
use Memio\Model\Object;

class ClassReflection
{
    public static function fromFile(File $file): ReflectionClassReflection
    {
        $structure = $file->getStructure();
        if (! $structure instanceof Object && ! $structure instanceof Contract) {
            throw new InvalidArgumentException(
                "There is neither a class nor an interface within " . $file->getFilename()
            );
        }

        $fileName = sys_get_temp_dir() . '/' . uniqid();
        file_put_contents($fileName, Build::prettyPrinter()->generateCode($file));

        /* @var $reflector \Dkplus\Reflections\AutoloadingReflector */
        $reflector = Builder::create()->reflector(Builder::create()->typeFactory());
        $reflector->addClassInFile($structure->getFullyQualifiedName(), $fileName);

        return $reflector->reflectClass($structure->getFullyQualifiedName());
    }
}

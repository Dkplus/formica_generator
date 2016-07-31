<?php
namespace Dkplus\Formica;

use Memio\PrettyPrinter\PrettyPrinter;
use Memio\Model\File;

class FileWriter
{
    /** @var PrettyPrinter */
    private $prettyPrinter;

    public function __construct(PrettyPrinter $prettyPrinter)
    {
        $this->prettyPrinter = $prettyPrinter;
    }

    public function write(File $file)
    {
        $code = $this->prettyPrinter->generateCode($file);
        $directory = dirname($file->getFilename());
        if (! is_dir($directory)) {
            mkdir(dirname($file->getFilename()), 0777, true);
        }
        file_put_contents($file->getFilename(), $code);
    }
}

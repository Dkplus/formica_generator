<?php
namespace spec\Dkplus\Formica;

use Dkplus\Formica\FileWriter;
use Memio\Model\File;
use Memio\PrettyPrinter\PrettyPrinter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FileWriter
 */
class FileWriterSpec extends ObjectBehavior
{
    function let(PrettyPrinter $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileWriter::class);
    }

    function it_writes_the_files_into_directories(PrettyPrinter $generator)
    {
        $file = __DIR__ . '/testbox/Test.php';
        if (file_exists($file)) {
            unlink($file); // clean up old tests
        }

        $code = <<<CODE
class Test
{
}
CODE
;
        $generator->generateCode(Argument::type(File::class))->willReturn($code);
        $this
            ->write(File::make($file))
            ->shouldWriteCodeToFile($code, $file);
    }

    function getMatchers()
    {
        return [
            'writeCodeToFile' => function ($subject, $code, $file) {
                return file_get_contents($file) === $code;
            },
        ];
    }
}

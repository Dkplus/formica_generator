<?php

namespace spec\Dkplus\Formica\ClassLocating;

use Dkplus\Formica\ClassLocating\Psr4ClassLocations;
use Dkplus\Formica\ClassLocating\ComposerClassLocationDetector;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ComposerClassLocationDetector
 */
class ComposerClassLocationDetectorSpec extends ObjectBehavior
{
    const DIR_PREFIX = __DIR__ . '/assets/ComposerClassLocationDetector';

    function it_is_initializable()
    {
        $this->shouldHaveType(ComposerClassLocationDetector::class);
    }

    function it_extracts_the_psr4_paths_from_composer_json()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/psr4')
            ->shouldLocateClass('Acme\\Test', [self::DIR_PREFIX . '/psr4/src/Test.php']);
    }

    function it_extracts_psr4_paths_from_autoloading_dev_also()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/autoload_dev')
            ->shouldLocateClass('Test\\Acme\\TestLoader', [self::DIR_PREFIX . '/autoload_dev/test/TestLoader.php']);
    }

    function it_does_not_fail_on_invalid_composer_json()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/invalid_composer_json')
            ->shouldReturnAnInstanceOf(Psr4ClassLocations::class);
    }

    function it_does_not_fail_on_missing_autoload_section()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/no_psr4_autoload_section')
            ->shouldReturnAnInstanceOf(Psr4ClassLocations::class);
    }

    function it_does_not_fail_on_not_existing_directory()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/not_existing_directory')
            ->shouldReturnAnInstanceOf(Psr4ClassLocations::class);
    }

    function it_does_not_fail_on_missing_composer_json()
    {
        $this
            ->detectFromDirectory(self::DIR_PREFIX . '/missing_composer_json')
            ->shouldReturnAnInstanceOf(Psr4ClassLocations::class);
    }

    public function getMatchers()
    {
        return [
            'locateClass' => function (Psr4ClassLocations $locations, string $class, array $expectedPaths) {
                if ($locations->locate($class) != $expectedPaths) {
                    throw new FailureException(
                        "Class $class has been located in "
                        . json_encode($locations->locate($class))
                        . " but was expected to be located in "
                        . json_encode($expectedPaths));
                }
                return true;
            }
        ];
    }
}

<?php
namespace spec\Dkplus\Formica\ClassLocating;

use Dkplus\Formica\ClassLocating\ClassLocations;
use Dkplus\Formica\ClassLocating\NoSuggestionPossible;
use Dkplus\Formica\ClassLocating\Psr4ClassLocations;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Psr4ClassLocations
 */
class Psr4ClassLocationsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/var/www', ['Psr4Test\\' => 'src/psr4/']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Psr4ClassLocations::class);
    }

    function its_a_class_locations()
    {
        $this->shouldImplement(ClassLocations::class);
    }

    function it_locates_psr4_classes()
    {
        $this->locate('Psr4Test\\MyTestClass')->shouldReturn(['/var/www/src/psr4/MyTestClass.php']);
        $this->locate('Psr4Test\\MyTest\\MyClass')->shouldReturn(['/var/www/src/psr4/MyTest/MyClass.php']);
    }

    function it_suggests_a_file_for_a_class()
    {
        $this->suggestFile('Psr4Test\\MyTestClass')->shouldReturn('/var/www/src/psr4/MyTestClass.php');
    }

    function it_throws_an_exception_if_it_could_not_suggest_a_file_for_a_class()
    {
        $this->shouldThrow(NoSuggestionPossible::forClass('My\\Test'))->during('suggestFile', ['My\\Test']);
    }
}

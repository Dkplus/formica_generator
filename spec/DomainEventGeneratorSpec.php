<?php
namespace spec\Dkplus\Formica;

use Dkplus\Annotations\DDD\DomainEvent;
use Dkplus\Formica\ClassLocating\ClassLocations;
use Dkplus\Formica\DomainEventGenerator;
use Dkplus\Formica\FileWriter;
use Memio\Model\File;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin DomainEventGenerator
 */
class DomainEventGeneratorSpec extends ObjectBehavior
{
    function let(FileWriter $writer, ClassLocations $locations)
    {
        $this->beConstructedWith($writer, $locations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DomainEventGenerator::class);
    }

    function it_generates_the_class_in_the_file_suggested_by_the_class_locations(
        FileWriter $writer,
        ClassLocations $locations
    ) {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->inFile('/var/www/src/UserHasBeenCreated.php'))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", []);
    }

    function it_generates_a_final_class(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->finalClass())->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", []);
    }


    function it_generates_a_occurredOn_property(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->classWithProperty('occurredOn', 'DateTimeImmutable'))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", []);
    }

    function it_generates_user_defined_properties(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->classWithProperty('email', 'string'))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", ['email' => 'string']);
    }

    function it_initializes_user_defined_properties_with_constructor_args(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->classWithPublicConstructor(['id' => 'int', 'email' => 'string']))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", ['id' => 'int', 'email' => 'string']);
    }

    function it_generates_a_occurredOn_method(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->publicMethodWithReturnType('occurredOn', 'DateTimeImmutable'))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", []);
    }

    function it_generates_a_getter_method_for_each_user_defined_property(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->publicMethodWithReturnType('email', 'string'))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", ['email' => 'string']);
    }

    function it_annotates_the_class_with_a_documenting_annotation(FileWriter $writer, ClassLocations $locations)
    {
        $locations->suggestFile('Acme\\UserHasBeenCreated')->willReturn('/var/www/src/UserHasBeenCreated.php');
        $writer->write($this->classAnnotatedWith(DomainEvent::class))->shouldBeCalled();

        $this->generate("Acme\\UserHasBeenCreated", []);
    }

    private function inFile(string $fileName) : Argument\Token\TokenInterface
    {
        return Argument::that(function (File $file) use ($fileName) {
            return $file->getFilename() === $fileName;
        });
    }

    private function finalClass() : Argument\Token\TokenInterface
    {
        return Argument::that(function (File $file) {
            return ClassReflection::fromFile($file)->isFinal();
        });
    }

    private function classWithProperty(string $property, string $type) : Argument\Token\TokenInterface
    {
        return Argument::that(function (File $file) use ($property, $type) {
            $properties = ClassReflection::fromFile($file)->properties();
            return $properties->contains($property)
                && $properties->named($property)->type() == $type;
        });
    }

    private function publicMethodWithReturnType(string $method, string $returnType) : Argument\Token\TokenInterface
    {
        return Argument::that(function (File $file) use ($method, $returnType) {
            $methods = ClassReflection::fromFile($file)->methods();
            return $methods->contains($method)
                && $methods->named($method)->isPublic()
                && $methods->named($method)->returnType() == $returnType;
        });
    }

    private function classWithPublicConstructor(array $arguments) : Argument\Token\TokenInterface
    {
        return Argument::that(function (File $file) use ($arguments) {
            $methods = ClassReflection::fromFile($file)->methods();
            if (! $methods->contains('__construct')
                || ! $methods->named('__construct')->isPublic()
                || $methods->named('__construct')->countParameters() !== count($arguments)
            ) {
                return false;
            }

            $parameters = $methods->named('__construct')->parameters();
            foreach (array_keys($arguments) as $i => $each) {
                if ($parameters->atPosition($i)->name() != $each) {
                    return false;
                }
                if ($arguments[$each] != $parameters->atPosition($i)->type()) {
                    return false;
                }
            }
            return true;
        });
    }

    private function classAnnotatedWith(string $fqcn)
    {
        return Argument::that(function (File $file) use ($fqcn) {
            return ClassReflection::fromFile($file)->annotations()->contains($fqcn);
        });
    }
}

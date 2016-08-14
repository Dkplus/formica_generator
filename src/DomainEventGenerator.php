<?php
declare(strict_types=1);
namespace Dkplus\Formica;

use Dkplus\Annotations\DDD\DomainEvent;
use Dkplus\Formica\ClassLocating\ClassLocations;
use Memio\Model\Argument;
use Memio\Model\File;
use Memio\Model\FullyQualifiedName;
use Memio\Model\Method;
use Memio\Model\Object;
use Memio\Model\Phpdoc\Description;
use Memio\Model\Phpdoc\PropertyPhpdoc;
use Memio\Model\Phpdoc\StructurePhpdoc;
use Memio\Model\Phpdoc\VariableTag;
use Memio\Model\Property;

class DomainEventGenerator
{
    /** @var FileWriter */
    private $fileWriter;

    /** @var ClassLocations */
    private $locations;

    public function __construct(FileWriter $fileWriter, ClassLocations $locations)
    {
        $this->fileWriter = $fileWriter;
        $this->locations = $locations;
    }

    public function generate($class, array $properties)
    {
        $classModel = Object::make($class);
        $file = File::make($this->locations->suggestFile($class))->setStructure($classModel);

        $constructor = Method::make('__construct');
        $classModel->addMethod($constructor);

        $file->addFullyQualifiedName(FullyQualifiedName::make(DomainEvent::class));
        $classModel->setPhpdoc(StructurePhpdoc::make()->setDescription(Description::make('@DomainEvent')));

        $additionalProperties = ['occurredOn' => 'DateTimeImmutable'];
        foreach (array_merge($properties, $additionalProperties) as $eachName => $eachType) {
            if (class_exists($eachType)) {
                $file->addFullyQualifiedName(FullyQualifiedName::make($eachType));
            }
            $classModel->addProperty(
                Property::make($eachName)
                    ->setPhpdoc(PropertyPhpdoc::make()->setVariableTag(VariableTag::make($eachType)))
            );

            $classModel->addMethod(
                Method::make($eachName)
                    ->setBody(str_pad('', 8, ' ') . "return \$this->$eachName;")
                    ->setReturnType($eachType)
            );
        }
        foreach ($properties as $eachName => $eachType) {
            $constructor->addArgument(Argument::make($eachType, $eachName));
            $constructor->setBody(
                $constructor->getBody()
                . str_pad('', 8, ' ')
                . "\$this->{$eachName} = \${$eachName};\n"
            );
        }

        $constructor->setBody(
            $constructor->getBody()
            . str_pad('', 8, ' ')
            . "\$this->occurredOn = new DateTimeImmutable();"
        );

        $this->fileWriter->write($file);
    }
}

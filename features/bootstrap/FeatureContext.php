<?php
declare(strict_types=1);
namespace Feature\Dkplus\Formica;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Dkplus\Formica\ClassLocating\ComposerClassLocationDetector;
use Dkplus\Formica\DomainEventGenerator;
use Dkplus\Formica\FileWriter;
use Dkplus\Reflections\AutoloadingReflector;
use Dkplus\Reflections\Builder;
use Error;
use Memio\Memio\Config\Build;

class FeatureContext implements SnippetAcceptingContext
{
    const TEST_DIR = __DIR__ . '/testbox';

    /** @var AutoloadingReflector */
    private $reflector;

    /** @var FileWriter */
    private $fileWriter;

    public function __construct()
    {
        $this->fileWriter = new FileWriter(Build::prettyPrinter());
    }

    /** @beforeScenario */
    public function setUpClassLoader()
    {
        $this->reflector = Builder::create()->reflector(Builder::create()->typeFactory());
    }

    private function classLocations()
    {
        return (new ComposerClassLocationDetector())->detectFromDirectory(self::TEST_DIR);
    }

    /**
     * @Given the namespace :namespace is placed in the directory :directory
     */
    public function theNamespaceIsPlacedInTheDirectory(string $namespace, string $directory)
    {
        $namespace = rtrim($namespace, '\\') . '\\';
        $directory = rtrim($directory, '/') . '/';
        file_put_contents(
            self::TEST_DIR . '/composer.json',
            json_encode(['autoload' => ['psr-4' => [$namespace => $directory]]], JSON_UNESCAPED_SLASHES)
        );
        $this->reflector->addPsr4Path($namespace, self::TEST_DIR . '/' . $directory);
    }

    /**
     * @When I generate a domain event :class with the following properties:
     */
    public function iGenerateADomainEventWithTheFollowingProperties(string $class, TableNode $properties)
    {
        $generator = new DomainEventGenerator($this->fileWriter, $this->classLocations());
        $generator->generate(
            $class,
            array_combine(
                array_column($properties->getColumnsHash(), 'property'),
                array_column($properties->getColumnsHash(), 'type')
            )
        );
    }

    /**
     * @Then the class :class should exist in the file :filePath
     * @throws Error
     */
    public function theClassShouldExistInTheFile(string $class, string $filePath)
    {
        if (! is_file(self::TEST_DIR . '/' . $filePath)) {
            throw new Error("File $filePath does not exist");
        }
        if (! $this->reflector->reflectClass($class)->fileName() === $filePath) {
            throw new Error("Class $class is not located in $filePath");
        }
    }

    /**
     * @Then the class :class should be annotated with :annotation
     * @throws Error
     */
    public function theClassShouldBeAnnotatedWith(string $class, string $annotation)
    {
        $annotations = $this->reflector->reflectClass($class)->annotations();
        foreach ($annotations as $each) {
            if ($each instanceof $annotation) {
                return;
            }
        }
        throw new Error("Class $class is not annotated with $annotation");
    }

    /**
     * @Then the class :class should be constructed with :count parameters:
     * @throws Error
     */
    public function theClassShouldBeConstructedWithParameters($class, $count, TableNode $table)
    {
        $classReflection = $this->reflector->reflectClass($class);
        if (! $classReflection->methods()->contains('__construct')) {
            throw new Error("Class $class does not have a constructor");
        }

        $constructor = $classReflection->methods()->named('__construct');
        if ((int) $count !== $constructor->countParameters()) {
            throw new Error(
                "Constructor of class $class was expected to have $count parameters but has "
                . $constructor->countParameters() . ' parameters'
            );
        }

        foreach ($table->getColumnsHash() as $eachArgument) {
            $eachArgument = array_merge(['type' => ''], $eachArgument);
            $parameter = $constructor->parameters()->named($eachArgument['parameter']);
            if (! $parameter) {
                throw new Error("Constructor of class $class does not define a parameter {$eachArgument['parameter']}");
            }

            if ($parameter->type() != $eachArgument['type']) {
                throw new Error(
                    "Argument {$eachArgument['parameter']} of class $class's constructor should be of type "
                    . "{$eachArgument['type']} but is of type " . $parameter->type()
                );
            }
        }
    }

    /**
     * @Then the class :class should have :count methods:
     * @throws Error
     */
    public function theClassShouldHaveMethods($className, $count, TableNode $table)
    {
        $class = $this->reflector->reflectClass($className);
        $methods = $class->methods();
        if ($methods->contains('__construct')) {
            ++$count;
        }
        if ($methods->size() !== $count) {
            throw new Error(
                "Class $className was expected to have $count methods but has " . $methods->size() . ' methods'
            );
        }
        foreach (array_column($table->getColumnsHash(), 'method') as $eachMethodName) {
            if (! $methods->named($eachMethodName)) {
                throw new Error("Class $className was expected to define a method $eachMethodName but did not");
            }
        }
    }

    /**
     * @Given the class :class should be final
     * @throws Error
     */
    public function theClassShouldBeFinal($className)
    {
        if (! $this->reflector->reflectClass($className)->isFinal()) {
            throw new Error("Class $className is not final");
        }
    }

    /**
     * @Given domain events are configured to implement :interfaceName
     */
    public function domainEventsAreConfiguredToImplement($interfaceName)
    {
        file_put_contents(self::TEST_DIR, <<<"YAML"
domain_events:
    implement: ["$interfaceName"]
YAML
        );
    }

    /**
     * @When I generate a domain event :className
     */
    public function iGenerateADomainEvent($className)
    {
        $generator = new DomainEventGenerator($this->fileWriter, $this->classLocations());
        $generator->generate($className, []);
    }

    /**
     * @Then the class :className should implement :interfaceName
     * @throws Error
     */
    public function theClassShouldImplement($className, $interfaceName)
    {
        if (! $this->reflector->reflectClass($className)->implementsInterface($interfaceName)) {
            throw new Error("Class $className does not implement $interfaceName");
        }
    }

    /**
     * @Given domain events are configured to extend :className
     */
    public function domainEventsAreConfiguredToExtend($className)
    {
        throw new PendingException();
    }

    /**
     * @Then the class :className should extend :extendedClassName
     */
    public function theClassShouldExtend($className, $extendedClassName)
    {
        throw new PendingException();
    }
}

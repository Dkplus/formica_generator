<?php
namespace Dkplus\Formica;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Composer\Autoload\ClassLoader;
use Dkplus\Annotations\DDD\DomainEvent;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\DoctrineAnnotationParser;
use Zend\Code\NameInformation;
use Zend\Code\Scanner\AnnotationScanner;
use Zend\Code\Scanner\FileScanner;

require_once __DIR__ . '/vendor/autoload.php';

$autoloader = new ClassLoader();
$autoloader->addPsr4('Acme\\', __DIR__ . '/features/bootstrap/testbox/src');
$reflector = new ClassReflector(new ComposerSourceLocator($autoloader));

$reflection = $reflector->reflect('Acme\\UserHasBeenCreated');

$doctrineParser = new DoctrineAnnotationParser();
$doctrineParser->registerAnnotation(DomainEvent::class);

$manager = new AnnotationManager();
$manager->attach($doctrineParser);
$scanner = new AnnotationScanner(
    $manager,
    $reflection->getDocComment(),
    new NameInformation($reflection->getNamespaceName(), [DomainEvent::class])
);
print_r($reflection->getDocComment());
print "\n\n\n";
print_r(iterator_to_array($scanner));
print "\n\n\n";

$fileScanner = new FileScanner($reflection->getFileName());
print_r($fileScanner->getUses());

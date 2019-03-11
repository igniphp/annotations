<?php declare(strict_types=1);
require_once '../vendor/autoload.php';

use Igni\Annotation\Context;
use Igni\Annotation\Parser;
use IgniTest\Annotation\Fixtures\Annotations\AnnotatedClass;

$reflection = new ReflectionClass(AnnotatedClass::class);
$parser = new Parser();
$annotations = $parser->parse($reflection->getDocComment(), Context::fromReflectionClass($reflection));

print_r($annotations);

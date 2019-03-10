<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\Context;
use Igni\Annotation\Parser;
use IgniTest\Annotation\Fixtures\Annotations\SimpleAnnotation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ParserTest extends TestCase
{
    public function testParseAnnotation() : void
    {
        $reflection = new ReflectionClass(SimpleAnnotation::class);
        $parser = new Parser();
        $annotations = $parser->parse($reflection->getDocComment(), Context::fromReflectionClass($reflection));
        $a = 1;
    }
}
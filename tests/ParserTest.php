<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\Context;
use Igni\Annotation\Parser;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function IgniTest\OpenApi\Fixtures\getPet;

final class ParserTest extends TestCase
{
    public function testParseAnnotation() : void
    {
        $reflection = new ReflectionMethod(PetShopApplication::class, 'getPet');
        $parser = new Parser();
        $annotations = $parser->parse($reflection->getDocComment(), Context::fromReflectionMethod($reflection));
        $a = 1;
    }

}

<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\ReflectorImports;
use IgniTest\Annotation\Fixtures\Annotations\MetaClass;
use IgniTest\Annotation\Fixtures\Annotations\SimpleAnnotation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

final class ReflectorImportsTest extends TestCase
{
    public function testReflectionClassImports() : void
    {
       $imports = new ReflectorImports(new ReflectionClass(MetaClass::class));
       self::assertSame(
           [
               'Annotation' => 'Igni\Annotation\Annotation',
               'NoValidate' => 'Igni\Annotation\NoValidate',
               'Target' => 'Igni\Annotation\Target',
           ],
           $imports->getImports()
       );
    }

    public function testReflectionPropertyImports() : void
    {
        $imports = new ReflectorImports(new ReflectionProperty(MetaClass::class, 'properties'));
        self::assertSame(
            [
                'Annotation' => 'Igni\Annotation\Annotation',
                'NoValidate' => 'Igni\Annotation\NoValidate',
                'Target' => 'Igni\Annotation\Target',
            ],
            $imports->getImports()
        );
    }

    public function testReflectionMethodImports() : void
    {
        $imports = new ReflectorImports(new ReflectionMethod(SimpleAnnotation::class, 'getAttribute'));
        self::assertSame(
            [
                'Annotation' => 'Igni\Annotation\Annotation',
                'Enum' => 'Igni\Annotation\Enum',
                'NoValidate' => 'Igni\Annotation\NoValidate',
                'Target' => 'Igni\Annotation\Target',
            ],
            $imports->getImports()
        );
    }
}

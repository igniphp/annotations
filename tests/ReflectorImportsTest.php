<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\ReflectorImports;
use IgniTest\Annotation\Fixtures\Annotations\EnumExample;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

final class ReflectorImportsTest extends TestCase
{
    public function testReflectionClassImports() : void
    {
       $imports = new ReflectorImports(new ReflectionClass(EnumExample::class));
       self::assertSame(
           [
               'Annotation' => 'Igni\Annotation\Annotation',
               'Enum' => 'Igni\Annotation\Enum',
               'Target' => 'Igni\Annotation\Target',
           ],
           $imports->getImports()
       );
    }

    public function testReflectionPropertyImports() : void
    {
        $imports = new ReflectorImports(new ReflectionProperty(EnumExample::class, 'enum'));
        self::assertSame(
            [
                'Annotation' => 'Igni\Annotation\Annotation',
                'Enum' => 'Igni\Annotation\Enum',
                'Target' => 'Igni\Annotation\Target',
            ],
            $imports->getImports()
        );
    }

    public function testReflectionMethod() : void
    {
        $imports = new ReflectorImports(new ReflectionMethod(EnumExample::class, 'getValues'));
        self::assertSame(
            [
                'Annotation' => 'Igni\Annotation\Annotation',
                'Enum' => 'Igni\Annotation\Enum',
                'Target' => 'Igni\Annotation\Target',
            ],
            $imports->getImports()
        );
    }
}

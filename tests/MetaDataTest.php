<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\MetaData\MetaData;
use IgniTest\Annotation\Fixtures\Annotations\MetaClass;
use IgniTest\Annotation\Fixtures\Annotations\MetaProperty;
use IgniTest\Annotation\Fixtures\Annotations\SimpleAnnotation;
use PHPUnit\Framework\TestCase;

final class MetaDataTest extends TestCase
{
    public function testSimpleAnnotation() : void
    {
        $meta = new MetaData(SimpleAnnotation::class);
        self::assertTrue($meta->isAnnotation());
        self::assertCount(1, $meta->getAttributes());
        self::assertSame('string', $meta->getAttribute('attribute')->getType());
    }

    public function testExtendedAnnotation() : void
    {
        $meta = new MetaData(MetaClass::class);
        self::assertTrue($meta->hasAttribute('properties'));
        self::assertCount(1, $meta->getAttributes());
        self::assertSame(MetaProperty::class . '[]', $meta->getAttribute('properties')->getType());
    }

}

<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\MetaData\MetaData;
use Igni\Annotation\Target;
use IgniTest\Annotation\Fixtures\Annotations\MetaClass;
use IgniTest\Annotation\Fixtures\Annotations\MetaProperty;
use IgniTest\Annotation\Fixtures\Annotations\SimpleAnnotation;
use PHPUnit\Framework\TestCase;

final class MetaDataTest extends TestCase
{
    public function testCollectFromSimpleAnnotation() : void
    {
        $meta = new MetaData(SimpleAnnotation::class);
        self::assertSame(SimpleAnnotation::class, $meta->getClass());
        self::assertTrue($meta->isAnnotation());
        self::assertCount(1, $meta->getAttributes());
        self::assertSame('string', $meta->getAttribute('attribute')->getType());
    }

    public function testCollectFromExtendedAnnotation() : void
    {
        $meta = new MetaData(MetaProperty::class);

        self::assertCount(3, $meta->getAttributes());
        self::assertTrue($meta->hasAttribute('name'));
        self::assertTrue($meta->hasAttribute('type'));
        self::assertTrue($meta->hasAttribute('default'));

        $name = $meta->getAttribute('name');
        self::assertTrue($name->isRequired());
        self::assertFalse($name->isEnum());
        self::assertSame('string', $name->getType());

        $type = $meta->getAttribute('type');
        self::assertFalse($type->isRequired());
        self::assertTrue($type->isEnum());
        self::assertSame('string', $type->getType());

        $default = $meta->getAttribute('default');
        self::assertFalse($default->isRequired());
        self::assertFalse($default->isEnum());
        self::assertFalse($default->isEnum());
        self::assertSame(MetaClass::class . '[]', $default->getType());
    }

    public function testValidateTargetAll() : void
    {
        $meta = new MetaData(SimpleAnnotation::class);
        self::assertTrue($meta->validateTarget(Target::TARGET_ALL));
        self::assertTrue($meta->validateTarget(Target::TARGET_CLASS));
        self::assertTrue($meta->validateTarget(Target::TARGET_METHOD));
        self::assertTrue($meta->validateTarget(Target::TARGET_PROPERTY));
        self::assertTrue($meta->validateTarget(Target::TARGET_FUNCTION));
        self::assertTrue($meta->validateTarget(Target::TARGET_ANNOTATION));
    }

    public function testValidateTarget() : void
    {
        $meta = new MetaData(MetaProperty::class);
        self::assertFalse($meta->validateTarget(Target::TARGET_ALL));
        self::assertFalse($meta->validateTarget(Target::TARGET_CLASS));
        self::assertFalse($meta->validateTarget(Target::TARGET_METHOD));
        self::assertTrue($meta->validateTarget(Target::TARGET_PROPERTY));
        self::assertFalse($meta->validateTarget(Target::TARGET_FUNCTION));
        self::assertTrue($meta->validateTarget(Target::TARGET_ANNOTATION));
    }

    public function testValidateAttributes() : void
    {
        $meta = new MetaData(SimpleAnnotation::class);
        self::assertTrue($meta->validateAttributes([]));
        self::assertFalse($meta->validateAttributes(['attribute' => 1]));
        self::assertTrue($meta->validateAttributes(['attribute' => 'a']));
        self::assertTrue($meta->validateAttributes(['attribute' => 'b']));
        self::assertTrue($meta->validateAttributes(['attribute' => 'c']));
    }
}

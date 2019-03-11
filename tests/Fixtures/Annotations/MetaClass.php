<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_CLASS)
 * @NoValidate()
 */
class MetaClass
{
    /**
     * @var MetaProperty[]
     */
    public $properties = [];
}

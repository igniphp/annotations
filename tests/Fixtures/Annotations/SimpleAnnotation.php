<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_ALL)
 * @Enum(1,2,3)
 */
class SimpleAnnotation
{
    /**
     * @var string
     */
    public $attribute = '';
}

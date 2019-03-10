<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_ALL)
 */
class SimpleAnnotation
{
    /**
     * @var string
     */
    public $attribute = '';
}


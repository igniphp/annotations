<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_ALL)
 * @NoValidate()
 */
class SimpleAnnotation
{
    /**
     * @Enum('a', 'b', 'c')
     * @var string
     */
    public $attribute = '';

    public function getAttribute() : string
    {
        return $this->attribute;
    }
}

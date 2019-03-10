<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_CLASS)
 */
class EnumExample
{
    /**
     * @Enum(1, 2, 3)
     * @var int[]
     */
    public $enum = [];

    public function getValues() : array
    {
        return $this->enum;
    }
}


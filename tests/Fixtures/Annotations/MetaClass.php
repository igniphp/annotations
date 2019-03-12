<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Required;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_CLASS)
 * @NoValidate()
 */
class MetaClass
{
    /**
     * @Required
     * @var MetaProperty[]
     */
    public $properties = [];

    /**
     * @var string
     */
    public $name = 'DefaultName';
}

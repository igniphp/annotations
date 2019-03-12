<?php declare(strict_types=1);

namespace IgniTest\Annotation\Fixtures\Annotations;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Required;
use Igni\Annotation\Target;

/**
 * @Annotation()
 * @Target(Target::TARGET_ANNOTATION, Target::TARGET_PROPERTY)
 */
class MetaProperty
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @Enum("int", 'string', "float", "boolean")
     * @var string
     */
    public $type = 'string';

    /**
     * @NoValidate
     * @var MetaClass[]
     */
    public $default = [];
}

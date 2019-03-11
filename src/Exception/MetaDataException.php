<?php declare(strict_types=1);

namespace Igni\Annotation\Exception;

use Igni\Annotation\Context;
use Igni\Annotation\MetaData\MetaData;
use Igni\Exception\LogicException;

final class MetaDataException extends LogicException implements AnnotationException
{
    public static function forInvalidTarget($target, Context $context) : self
    {
        return new self("Invalid target {$target} passed in {$context}");
    }

    public static function forUndefinedAttribute(MetaData $metaData, string $attribute) : self
    {
        return new self("Annotation class {$metaData->getClass()} defines no attribute {$attribute}.");
    }
}

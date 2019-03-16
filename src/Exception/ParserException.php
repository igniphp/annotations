<?php declare(strict_types=1);

namespace Igni\Annotation\Exception;

use Igni\Annotation\Context;
use Igni\Annotation\MetaData\Attribute;
use Igni\Annotation\Token;
use Igni\Exception\LogicException;

final class ParserException extends LogicException implements AnnotationException
{
    public static function forUnexpectedToken(Token $token, Context $context) : self
    {
        $context = $context->getSymbol() ?: (string) $context;
        $message = "Unexpected `{$token}` in {$context} at index: {$token->getPosition()}.";

        return new self($message);
    }

    public static function forUnknownAnnotationClass(string $name, Context $context) : self
    {
        $message = "Could not find annotation class {$name} used in {$context}." .
            "Please check your composer settings, or use Parser::registerNamespace.";

        return new self($message);
    }

    public static function forUsingNonAnnotationClassAsAnnotation(string $class, Context $context) : self
    {
        $message = "Used {$class} as annotation - class is not marked as annotation. Used in {$context}." .
            "Please add `@Annotation` annotation to mark class as annotation class.";

        return new self($message);
    }

    public static function forUndefinedConstant(Context $context, string $name) : self
    {
        $message = "Using undefined constant `{$name}` in {$context}";
        return new self($message);
    }

    public static function forInvalidAttributeValue(Context $context, string $annotationClass, Attribute $failedAttribute) : self
    {
        return new self("Failed to validate `{$failedAttribute->getName()}` attribute in @{$annotationClass} used in {$context}");
    }

    public static function forInvalidTarget(Context $context, string $target, string $annotationClass) : self
    {
        return new self("Invalid target `{$target}`` for annotation `@{$annotationClass}` used in {$context}");
    }
}

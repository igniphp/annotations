<?php declare(strict_types=1);

namespace Igni\Annotation\MetaData;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Required;
use Igni\Annotation\Target;
use Igni\Annotation\Context;
use Igni\Annotation\Parser;
use Igni\Annotation\Exception\ParserException;
use ReflectionClass;
use ReflectionProperty;

class MetaData
{
    public const BUILT_IN = [
        Annotation::class => 1,
        Target::class => 1,
        Required::class => 1,
        Enum::class => 1,
        NoValidate::class => 1,
    ];
    private $parser;
    private $context;
    private $target = [Target::TARGET_ALL];
    private $validate = true;
    private $hasConstructor = false;
    private $isAnnotation = false;
    private $className;

    public function __construct(string $class, Parser $parser = null)
    {
        $this->className = $class;
        $this->parser = $parser ?? new Parser();
        $reflection = new ReflectionClass($class);
        $this->context = Context::fromReflectionClass($reflection);

        // Skip collecting built in annotations meta data, its not needed.
        if (isset(self::BUILT_IN[$class])) {
            return;
        }

        $this->collect($reflection);
    }

    private function collect(ReflectionClass $class) : void
    {
        $this->className = $class->getName();
        $this->classNamespace = $class->getNamespaceName();
        $this->hasConstructor = $class->getConstructor() !== null;

        $this->collectClassMeta($class);
    }

    private function collectClassMeta(ReflectionClass $class)
    {
        $annotations = $this->parser->parse($class->getDocComment(), $this->context);
        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case Annotation ::class:
                    $this->isAnnotation = true;
                    break;
                case Target::class:
                    $valid = false;
                    foreach ($annotation->value as $target) {
                        if (in_array($target, Target::TARGETS)) {
                            $valid = true;
                        }
                    }
                    if (!$valid) {
                        throw ParserException::forPropertyValidationFailure(
                            $this->context,
                            ['enum' => Target::TARGETS],
                            $annotation->value
                        );
                    }
                    $metaData['target'] = $annotation->value;
                    break;
                case NoValidate::class:
                    $metaData['validate'] = (bool) $annotation->value;
                    break;
            }
        }

        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $this->collectPropertyMeta($property);
        }
    }

    private function collectPropertyMeta(ReflectionProperty $property) : void
    {
        $propertyContext = Context::fromReflectionProperty($property);
        $docComment = $property->getDocComment();
        $name = $property->getName();
        $type = $this->parseDeclaredType($docComment, $this->context);
        $required = false;
        $validate = true;
        $enum = null;

        $annotations = $this->parser->parse($docComment, $propertyContext);
        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case Enum::class:
                    $enum = $annotation->value;
                    break;
                case Required::class:
                    $required = (bool) $annotation->value;
                    break;
                case NoValidate::class:
                    $validate = (bool) $annotation->value;
                    break;
            }
        }

        $attribute = new Attribute($name, $type, $required);
        if (!$validate) {
            $attribute->disableValidation();
        }
        if ($enum) {
            $attribute->enumerate($enum);
        }
        $metaData['properties'][$name] = $attribute;
    }


    private function parseDeclaredType(string $docComment, Context $context)
    {
        preg_match('/\@var\s+?([^\[\n\*]+)(\[\s*?\])?/', $docComment, $matches);
        $type = trim($matches[1]);
        $isArray = isset($matches[2]);
        switch (true) {
            // @var annotation contains multiple viable types for the property so it is mixed, we dont care about it
            case strstr($type, '|') !== false:
                $type = 'mixed';
                break;
            // Primitive types
            case in_array($type, ['float', 'double', 'bool', 'boolean', 'string', 'object', 'mixed']):
                if ($isArray) {
                    $type = [$type];
                }
                break;
            // If class like that exists
            case class_exists($type):
                if ($isArray) {
                    $type = [$type];
                }
                break;
            case ($class = $context->resolveClassName($type)) !== null:
                $type = $class;
                if ($isArray) {
                    $type = [$class];
                }
                break;
            // Fallback to mixed
            default:
                $type = 'mixed';
                break;
        }

        return $type;
    }
}

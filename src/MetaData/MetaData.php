<?php declare(strict_types=1);

namespace Igni\Annotation\MetaData;

use Igni\Annotation\Annotation;
use Igni\Annotation\Enum;
use Igni\Annotation\Exception\MetaDataException;
use Igni\Annotation\NoValidate;
use Igni\Annotation\Required;
use Igni\Annotation\Target;
use Igni\Annotation\Context;
use Igni\Annotation\Parser;
use ReflectionClass;
use ReflectionProperty;

final class MetaData
{
    public const BUILT_IN = [
        Annotation::class => 1,
        Target::class => 1,
        Required::class => 1,
        Enum::class => 1,
        NoValidate::class => 1,
    ];

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $validTargets = [Target::TARGET_ALL];

    /**
     * @var bool
     */
    private $validate = true;

    /**
     * @var bool
     */
    private $hasConstructor = false;

    /**
     * @var bool
     */
    private $isAnnotation = true;

    /**
     * @var string
     */
    private $className;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @var Attribute|null
     */
    private $lastFailedAttribute;

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

    public function getClass() : string
    {
        return $this->className;
    }

    public function isAnnotation() : bool
    {
        return $this->isAnnotation;
    }

    public function hasConstructor() : bool
    {
        return $this->hasConstructor;
    }

    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]);
    }

    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name) : Attribute
    {
        if (!$this->hasAttribute($name)) {
            throw MetaDataException::forUndefinedAttribute($this, $name);
        }

        return $this->attributes[$name];
    }

    public function validateTarget(string $target) : bool
    {
        return in_array(Target::TARGET_ALL, $this->validTargets) || in_array($target, $this->validTargets);
    }

    public function validateAttributes(array $data) : bool
    {
        if (!$this->validate) {
            return true;
        }

        $this->lastFailedAttribute = null;

        foreach ($this->attributes as $name => $attribute) {
            if (!isset($data[$name])) {
                if ($attribute->isRequired()) {
                    $this->lastFailedAttribute = $attribute;
                    return false;
                }
                continue;
            }

            if (!$attribute->validate($data[$name])) {
                $this->lastFailedAttribute = $attribute;
                return false;
            }
        }
        return true;
    }

    public function hasFailedAttribute() : bool
    {
        return null !== $this->lastFailedAttribute;
    }

    public function getFailedAttribute() : Attribute
    {
        if (!$this->hasFailedAttribute()) {
            throw MetaDataException::forUnresolvableFailedAttribute($this);
        }

        return $this->lastFailedAttribute;
    }

    private function collect(ReflectionClass $class) : void
    {
        $this->className = $class->getName();
        $this->hasConstructor = $class->getConstructor() !== null;

        $this->collectClassMeta($class);
    }

    private function collectClassMeta(ReflectionClass $class)
    {
        $this->isAnnotation = false;
        $annotations = $this->parser->parse($class->getDocComment(), $this->context);
        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case Annotation::class:
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
                        throw MetaDataException::forInvalidTarget(
                            $annotation->value,
                            $this->context
                        );
                    }
                    $this->validTargets = $annotation->value;
                    break;
                case NoValidate::class:
                    $this->validate = false;
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
                    $required = true;
                    break;
                case NoValidate::class:
                    $validate = false;
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

        $this->attributes[$name] = $attribute;
    }


    private function parseDeclaredType(string $docComment, Context $context)
    {
        preg_match('/@var\s+([^\*\n\[]+)\s*?(\[\s*?\])?/', $docComment, $matches);
        if (!isset($matches[1])) {
            return 'mixed';
        }

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

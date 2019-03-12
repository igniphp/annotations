<?php declare(strict_types=1);

namespace Igni\Annotation;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

final class Context
{
    private const BUILT_IN_ANNOTATIONS = [
        'Annotation' => Annotation::class,
        'Enum' => Enum::class,
        'Required' => Required::class,
        'Target' => Target::class,
        'NoValidate' => NoValidate::class,
    ];

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var string[]
     */
    private $imports = [];

    private $target;

    private $namespace;

    public function __construct(
        string $target = Target::TARGET_ALL,
        string $namespace = '',
        string $symbol = ''
    ) {
        $this->target = $target;
        $this->symbol = $symbol;
        $this->namespace = $namespace;
    }

    public function getNamespace() : string
    {
        return $this->namespace;
    }

    public function getSymbol() : string
    {
        return $this->symbol;
    }

    public function addImport(string $name, string $alias = '') : void
    {
        $this->imports[$alias] = $name;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function getImports() : array
    {
        return $this->imports;
    }

    public function resolveClassName(string $identifier) : ?string
    {
        if (isset(self::BUILT_IN_ANNOTATIONS[$identifier])) {
            return self::BUILT_IN_ANNOTATIONS[$identifier];
        }

        if (class_exists($identifier)) {
            return $identifier;
        }

        if (class_exists($this->getNamespace() . '\\' . $identifier)) {
            return $this->getNamespace() . '\\' . $identifier;
        }

        $identifier = explode('\\', $identifier);
        $imports = $this->getImports();
        if (isset($imports[$identifier[0]])) {
            $identifier = array_merge(explode('\\', $imports[$identifier[0]]), array_slice($identifier, 1));
        }
        $identifier = implode('\\', $identifier);
        if (class_exists($identifier)) {
            return $identifier;
        }

        return null;
    }

    public function __toString() : string
    {
        return $this->symbol;
    }

    public static function fromReflectionClass(ReflectionClass $class) : self
    {
        $instance = new self(
            Target::TARGET_CLASS,
            $class->getNamespaceName(),
            $class->getName()
        );
        $imports = new ReflectorImports($class);
        $instance->imports = $imports->getImports();

        return $instance;
    }

    public static function fromReflectionMethod(ReflectionMethod $method) : self
    {
        $instance = new self(
            Target::TARGET_METHOD,
            $method->getDeclaringClass()->getNamespaceName(),
            "{$method->getDeclaringClass()->getName()}::{$method->getName()}()"
        );
        $imports = new ReflectorImports($method);
        $instance->imports = $imports->getImports();

        return $instance;
    }

    public static function fromReflectionProperty(ReflectionProperty $property) : self
    {
        $instance = new self(
            Target::TARGET_PROPERTY,
            $property->getDeclaringClass()->getNamespaceName(),
            "{$property->getDeclaringClass()->getName()}::\${$property->getName()}"
        );
        $imports = new ReflectorImports($property);
        $instance->imports = $imports->getImports();

        return $instance;
    }

    public static function fromReflectionFunction(ReflectionFunction $function) : self
    {
        $instance = new self(
            Target::TARGET_FUNCTION,
            $function->getNamespaceName(),
            "{$function->getName()}()"
        );
        $imports = new ReflectorImports($function);
        $instance->imports = $imports->getImports();

        return $instance;
    }
}

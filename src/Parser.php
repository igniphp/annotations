<?php declare(strict_types=1);

namespace Igni\Annotation;

use Igni\Annotation\Exception\ParserException;
use ReflectionClass;

class Parser
{
    private const PHP_ANNOTATIONS = [
        // PHP Documentator
        'api',
        'author',
        'category',
        'copyright',
        'deprecated',
        'example',
        'filesource',
        'global',
        'ignore',
        'internal',
        'license',
        'link',
        'method',
        'package',
        'param',
        'property',
        'property-read',
        'property-write',
        'return',
        'see',
        'since',
        'source',
        'subpackage',
        'throws',
        'todo',
        'uses',
        'used-by',
        'var',
        'version',

        // PHP Unit
        'codeCoverageIgnore',
        'codeCoverageIgnoreEnd',
        'codeCoverageIgnoreStart',

        //PhpStorm
        'noinspection',

        //PhpCodeSniffer
        'codingStandardsIgnoreStart',
        'codingStandardsIgnoreEnd',

        // PEAR
        'package_version',

    ];

    private $ignoreNotImported = false;
    private $ignored = [];
    private $metaData = [];

    public function __construct()
    {

    }

    public function addIgnore(string $name) : void
    {
        $this->ignored[] = $name;
    }

    public function ignoreNotImportedAnnotations(bool $ignore = true) : void
    {
        $this->ignoreNotImported = $ignore;
    }

    /**
     * @param string $docBlock
     * @param Context $context
     * @return array
     * @throws
     */
    public function parse(string $docBlock, Context $context = null): array
    {
        if ($context === null) {
            $context = new Context(Target::TARGET_ALL, self::class . '::' . __METHOD__ . '()');
        }

        $tokenizer = new Tokenizer($docBlock);

        // Lets search for fist annotation occurrence in docblock
        if (!$tokenizer->seek(Token::T_AT)) {
            // No annotations in docblock.
            return [];
        }
        $annotations = [];

        while ($tokenizer->valid() && $tokenizer->seek(Token::T_AT)) {

            // Annotation must be preceded by a new line token, otherwise it should be ignored
            if ($tokenizer->key() > 1 && $tokenizer->at($tokenizer->key() - 1)->getType() !== Token::T_EOL) {
                $tokenizer->next();
                continue;
            }
            // Skip @
            $tokenizer->next();
            $annotation = $this->parseAnnotation($tokenizer, $context);
            if ($annotation === null) {
                continue;
            }
            $annotations[] = $annotation;
        }

        return $annotations;
    }

    private function parseAnnotation(Tokenizer $tokenizer, Context $context, $nested = false)
    {
        $identifier = $tokenizer->current()->getValue();
        $tokenizer->next();
        // Ignore one-line utility annotations
        if (in_array($identifier, self::PHP_ANNOTATIONS, true)) {
            return null;
        }

        $arguments = $this->parseArguments($tokenizer, $context);

        // Other ignored annotations have to be parsed before we ignore them.
        if (in_array($identifier, $this->ignored, true)) {
            return null;
        }

        $annotationClass = $context->resolveClassName($identifier);

        if ($annotationClass === null) {
            if ($this->ignoreNotImported) {
                return null;
            }
            throw ParserException::forUnknownAnnotationClass($identifier, $context);
        }


        $metaData = $this->getMetaData($annotationClass, $context);


        if (!$metaData['has_constructor']) {
            $annotation = new $annotationClass();
            $valueArgs = [];
            foreach ($arguments as $key => $value) {
                if (is_numeric($key)) {
                    $valueArgs[] = $value;
                    continue;
                }
                if (property_exists($annotation, $key)) {
                    $annotation->{$key} = $value;
                }
            }
            if (property_exists($annotation, 'value')) {
                $annotation->value = $valueArgs;
            }
        } else {
            $annotation = new $annotationClass($arguments);
        }

        return $annotation;
    }

    private function parseArguments(Tokenizer $tokenizer, Context $context) : array
    {
        $arguments = [];

        if ($tokenizer->current()->getType() !== Token::T_OPEN_PARENTHESIS) {
            return $arguments;
        }

        $this->expect(Token::T_OPEN_PARENTHESIS, $tokenizer, $context);
        $tokenizer->next();

        $this->parseArgument($tokenizer, $context, $arguments);

        while ($this->match($tokenizer, Token::T_COMMA)) {
            $tokenizer->next();
            $this->parseArgument($tokenizer, $context, $arguments);
        }

        $this->expect(Token::T_CLOSE_PARENTHESIS, $tokenizer, $context);
        $tokenizer->next();

        return $arguments;
    }


    private function parseArgument(Tokenizer $tokenizer, Context $context, array &$arguments) : void
    {
        $this->ignoreEndOfLine($tokenizer);
        // There was a comma with no value afterwards
        if ($this->match($tokenizer, Token::T_CLOSE_PARENTHESIS)) {
            return;
        }

        // key / value pair
        if ($tokenizer->at($tokenizer->key() + 1)->getType() === Token::T_EQUALS) {
            $key = $tokenizer->current()->getValue();
            $this->skip(2, $tokenizer);
            $arguments[$key] = $this->parseValue($tokenizer, $context);
            return;
        }

        // Just value
        $arguments[] = $this->parseValue($tokenizer, $context);
    }

    private function parseValue(Tokenizer $tokenizer, Context $context)
    {
        $token = $tokenizer->current();
        $tokenizer->next();

        // Resolve annotation
        if ($token->getType() === Token::T_AT) {
            return $this->parseAnnotation($tokenizer, $context, true);
        }

        if ($token->getType() === Token::T_OPEN_BRACKET) {
            $tokenizer->next();
            return $this->parseArray($tokenizer, $context);
        }

        // Resolve primitives
        switch ($token->getType()) {
            case Token::T_STRING:
                return $token->getValue();

            case Token::T_INTEGER:
                return (int) $token->getValue();

            case Token::T_FLOAT:
                return (float) $token->getValue();

            case Token::T_NULL:
                return null;

            case Token::T_FALSE:
                return false;

            case Token::T_TRUE:
                return true;
        }

        $constant = $token->getValue();

        // Class constant
        if (strpos($constant, '::') !== false) {
            $constant = explode('::', $constant);

            $class = $context->resolveClassName($constant[0]);
            if ($constant[1] === 'class' && $class) {
                return $class;
            }
            $constant = $class . '::' . $constant[1];
        }

        if (!defined($constant)) {
            throw ParserException::forUndefinedConstant($context, $token->getValue());
        }

        return constant($constant);
    }

    private function parseArray(Tokenizer $tokenizer, Context $context) : array
    {
        $array = [];
        // Empty array
        if ($tokenizer->current()->getType() === Token::T_CLOSE_BRACKET) {
            return $array;
        }
        $array[] = $this->parseValue($tokenizer, $context);
        while ($tokenizer->valid() && $this->match($tokenizer, Token::T_COMMA)) {
            $tokenizer->next();
            $this->ignoreEndOfLine($tokenizer);
            if ($tokenizer->current()->getType() === Token::T_CLOSE_BRACKET) {
                break;
            }
            $array[] = $this->parseValue($tokenizer, $context);
        }
        $this->expect(Token::T_CLOSE_BRACKET, $tokenizer, $context);
        $tokenizer->next();

        return $array;
    }

    private function getMetaData(string $annotationClass, Context $context) : array
    {
        if (isset($this->metaData[$annotationClass])) {
            return $this->metaData[$annotationClass];
        }

        $annotationReflection = new ReflectionClass($annotationClass);
        if (strpos($annotationReflection->getDocComment(), '@Annotation') === false) {
            throw ParserException::forUsingNonAnnotationClassAsAnnotation($annotationClass, $context);
        }

        return $this->metaData[$annotationClass] = $this->metaDataExtractor->extract($annotationReflection, $context);
    }

    private function match(Tokenizer $tokenizer, int $type) : bool
    {
        return $tokenizer->current()->getType() === $type;
    }

    private function expect(int $expectedType, Tokenizer $tokenizer, Context $context) : void
    {
        $this->ignoreEndOfLine($tokenizer);
        if ($expectedType !== $tokenizer->current()->getType()) {
            throw ParserException::forUnexpectedToken($tokenizer->current(), $context);
        }
    }

    private function ignoreEndOfLine(Tokenizer $tokenizer) : bool
    {
        if ($tokenizer->current()->getType() === Token::T_EOL) {
            $this->skip(1, $tokenizer);
            return true;
        }

        return false;
    }

    private function skip(int $length, Tokenizer $tokenizer) : void
    {
        for (;$length > 0; $length--) {
            $tokenizer->next();
            if (!$tokenizer->valid()) {
                return;
            }
        }
    }
}

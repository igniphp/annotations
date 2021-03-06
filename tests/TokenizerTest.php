<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\Token;
use Igni\Annotation\Tokenizer;
use PHPUnit\Framework\TestCase;

use function count;

final class TokenizerTest extends TestCase
{
    /**
     * @param string $stream
     * @param array $expected
     * @dataProvider provideTokens
     */
    public function testGetTokens(string $stream, array $expected) : void
    {
        $tokenizer = new Tokenizer($stream);
        $tokens = $tokenizer->getTokens();
        self::assertCount(count($expected), $tokens);

        $i = 0;
        foreach ($expected as $criteria) {
            self::assertSame($criteria['value'], $tokens[$i]->getValue());
            self::assertSame($criteria['type'], $tokens[$i]->getType());
            $i++;
        }
    }

    public function testCatchNewLinesTokens() : void
    {
        $tokenizer = new Tokenizer("/** \n */");
        $tokens = $tokenizer->getTokens();
        self::assertCount(3, $tokens);
    }

    public function testTokenizeEmptyDocBlock() : void
    {
        $tokenizer = new Tokenizer('/** */');
        $tokens = $tokenizer->getTokens();
        self::assertCount(2, $tokens);
    }

    public function testTokenizeString() : void
    {
        $tokenizer = new Tokenizer('"Test string with escaped\" and unescaped"');
        $tokens = $tokenizer->getTokens();
        $token = $tokens[0];

        self::assertCount(1, $tokens);
        self::assertSame(Token::T_STRING, $token->getType());
        self::assertSame('Test string with escaped" and unescaped', $token->getValue());
    }

    /**
     * @param string $stream
     * @param string $expected
     * @dataProvider provideIdentifiers
     */
    public function testTokenizeIdentifier(string $stream, string $expected) : void
    {
        $tokenizer = new Tokenizer($stream);
        $tokens = $tokenizer->getTokens();
        $token = $tokens[0];

        self::assertCount(1, $tokens);
        self::assertSame(Token::T_IDENTIFIER, $token->getType());
        self::assertSame($expected, $token->getValue());
    }

    /**
     * @param string $stream
     * @param string $expected
     * @dataProvider provideIntegers
     */
    public function testTokenizeInteger(string $stream, string $expected) : void
    {
        $tokenizer = new Tokenizer($stream);
        $tokens = $tokenizer->getTokens();
        $token = $tokens[0];

        self::assertCount(1, $tokens);
        self::assertSame(Token::T_INTEGER, $token->getType());
        self::assertSame($expected, $token->getValue());
    }

    /**
     * @param string $stream
     * @param string $bool
     * @dataProvider provideBooleans
     */
    public function testTokenizeBool(string $stream, string $bool) : void
    {
        $tokenizer = new Tokenizer($stream);
        $tokens = $tokenizer->getTokens();
        $token = $tokens[0];

        self::assertCount(1, $tokens);

        if ($bool === 'false') {
            self::assertSame(Token::T_FALSE, $token->getType());
        } else {
            self::assertSame(Token::T_TRUE, $token->getType());
        }
    }

    /**
     * @param string $stream
     * @param string $expected
     * @dataProvider provideFloats
     */
    public function testTokenizeFloats(string $stream, string $expected) : void
    {
        $tokenizer = new Tokenizer($stream);
        $tokens = $tokenizer->getTokens();
        $token = $tokens[0];

        self::assertCount(1, $tokens);
        self::assertSame(Token::T_FLOAT, $token->getType());
        self::assertSame($expected, $token->getValue());
    }

    public function provideBooleans() : array
    {
        return [
            ['true', 'true'],
            ['false', 'false'],
            [' true', 'true'],
            [' false ', 'false'],
            ['TRUE ', 'true'],
            ['FALSE ', 'false'],
        ];
    }

    public function provideIntegers() : array
    {
        return [
            ['12', '12'],
            [' 12', '12'],
            [' 12 ', '12'],
            ['12 ', '12']
        ];
    }

    public function provideFloats() : array
    {
        return [
            ['12.21', '12.21'],
            [' 12.21', '12.21'],
            [' 12.21 ', '12.21'],
            ['12.22 ', '12.22']
        ];
    }

    public function provideIdentifiers() : array
    {
        return [
            ['Identifier12 ', 'Identifier12'],
            [' Identifier12 ', 'Identifier12'],
            [' Identifier12', 'Identifier12'],
        ];
    }

    public function provideTokens(): array
    {
        return [
            [
                '(key4 = "aaa", key5=[1, 2])',
                [
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => 'key4',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '=',
                        'type' => Token::T_EQUALS,
                    ],
                    [
                        'value' => 'aaa',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'key5',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '=',
                        'type' => Token::T_EQUALS,
                    ],
                    [
                        'value' => '[',
                        'type' => Token::T_OPEN_BRACKET,
                    ],
                    [
                        'value' => '1',
                        'type' => Token::T_INTEGER,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '2',
                        'type' => Token::T_INTEGER,
                    ],
                    [
                        'value' => ']',
                        'type' => Token::T_CLOSE_BRACKET,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ]
            ],
            [
                'Identifier::class',
                [
                   [
                       'value' => 'Identifier::class',
                       'type' => Token::T_IDENTIFIER,
                   ],
                ],
            ],
            [
                '@Identifier()',
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Identifier',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ]
            ],
            [
                '@\Fully\Qualified\Namespace()',
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => '\Fully\Qualified\Namespace',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ],
            ],
            [
                "@Namespace()\n@Namespace2()",
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Namespace',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                    [
                        'value' => "\n",
                        'type' => Token::T_EOL,
                    ],
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Namespace2',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ],
            ],
            [
                "@Namespace(12, true, false, 34.12)",
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Namespace',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => '12',
                        'type' => Token::T_INTEGER,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'true',
                        'type' => Token::T_TRUE,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'false',
                        'type' => Token::T_FALSE,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '34.12',
                        'type' => Token::T_FLOAT,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ],
            ],
            [
                "[20, 30.21, @Annotation, false]",
                [
                    [
                        'value' => '[',
                        'type' => Token::T_OPEN_BRACKET,
                    ],
                    [
                        'value' => '20',
                        'type' => Token::T_INTEGER,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '30.21',
                        'type' => Token::T_FLOAT,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Annotation',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'false',
                        'type' => Token::T_FALSE,
                    ],
                    [
                        'value' => ']',
                        'type' => Token::T_CLOSE_BRACKET,
                    ],
                ],
            ],
            [
                "@Annotation(null, 34.12, [true, false])",
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Annotation',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => 'null',
                        'type' => Token::T_NULL,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '34.12',
                        'type' => Token::T_FLOAT,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => '[',
                        'type' => Token::T_OPEN_BRACKET
                    ],
                    [
                        'value' => 'true',
                        'type' => Token::T_TRUE,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'false',
                        'type' => Token::T_FALSE,
                    ],
                    [
                        'value' => ']',
                        'type' => Token::T_CLOSE_BRACKET,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ],
            ],
            [
                "@Annotation\n * \n * [true, false]",
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Annotation',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => "\n",
                        'type' => Token::T_EOL,
                    ],
                    [
                        'value' => "\n",
                        'type' => Token::T_EOL,
                    ],
                    [
                        'value' => '[',
                        'type' => Token::T_OPEN_BRACKET
                    ],
                    [
                        'value' => 'true',
                        'type' => Token::T_TRUE,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'false',
                        'type' => Token::T_FALSE,
                    ],
                    [
                        'value' => ']',
                        'type' => Token::T_CLOSE_BRACKET,
                    ],
                ],
            ],
            [
                "@Enum('a', 'b', 'c')",
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Enum',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => 'a',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'b',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'c',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ]
            ],
            [
                '@Enum("a", "b", "c")',
                [
                    [
                        'value' => '@',
                        'type' => Token::T_AT,
                    ],
                    [
                        'value' => 'Enum',
                        'type' => Token::T_IDENTIFIER,
                    ],
                    [
                        'value' => '(',
                        'type' => Token::T_OPEN_PARENTHESIS,
                    ],
                    [
                        'value' => 'a',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'b',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ',',
                        'type' => Token::T_COMMA,
                    ],
                    [
                        'value' => 'c',
                        'type' => Token::T_STRING,
                    ],
                    [
                        'value' => ')',
                        'type' => Token::T_CLOSE_PARENTHESIS,
                    ],
                ]
            ],
        ];
    }
}

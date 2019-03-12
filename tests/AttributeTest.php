<?php declare(strict_types=1);

namespace IgniTest\Annotation;

use Igni\Annotation\MetaData\Attribute;
use Igni\Annotation\Token;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    /**
     * @param Attribute $attribute
     * @param $dataSet
     * @dataProvider provideValidDataSets
     */
    public function testSuccessfulValidate(Attribute $attribute, array $dataSet) : void
    {
        foreach ($dataSet as $unit) {
            self::assertTrue($attribute->validate($unit));
        }
    }

    public function testValidateEnum() : void
    {
        $attribute = new Attribute('testValidateEnum', 'int');
        $attribute->enumerate([1, 2, 3]);
        self::assertTrue($attribute->validate(1));
        self::assertTrue($attribute->validate(3));
        self::assertFalse($attribute->validate('aa'));
        self::assertFalse($attribute->validate(4));
    }

    public function provideValidDataSets() : array
    {
        return [
            [
                new Attribute('testValidInteger', 'int'),
                [1, 2, 3, 1200, 1211],
            ],
            [
                new Attribute('testValidString', 'string'),
                ['a', 'abc', 'sddad']
            ],
            [
                new Attribute('testValidateFloat', 'float'),
                [1.1, 2.0, 3.1],
            ],
            [
                new Attribute('testValidateFloat', 'double'),
                [1.1, 2.0, 3.1],
            ],
            [
                new Attribute('testValidateBoolean', 'bool'),
                [true, false, false, true],
            ],
            [
                new Attribute('testValidateBoolean', 'boolean'),
                [true, false, false, true],
            ],
            [
                new Attribute('testValidateObject', 'object'),
                [new \stdClass(), new Attribute('aa', 'boolean')],
            ],
            [
                new Attribute('testValidateInstanceOf', Token::class),
                [new Token(1, 2, 'v'), new Token(1, 2, 'v')]
            ],
            [
                new Attribute('testValidateArrayOfType', ['int']),
                [[1,2,3], [3,4,5], [7,8,8]]
            ]
        ];
    }
}

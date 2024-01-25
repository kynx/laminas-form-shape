<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\ArrayType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Type\ArrayType
 */
final class ArrayTypeTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(ArrayType $type, string $expected): void
    {
        $actual = $type->getTypeName();
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new ArrayType('foo', []), 'foo'],
            'escaped'  => [new ArrayType('foo bar', []), "'foo bar'"],
            'optional' => [new ArrayType('foo', [], true), 'foo?'],
        ];
    }

    public function testGetTypeStringReturnsPsalmType(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: float|int,
            bar?: string,
        }
        END_OF_EXPECTED;

        $compositeType = new ArrayType('baz', [
            new InputType('foo', [PsalmType::Int, PsalmType::Float]),
            new InputType('bar', [PsalmType::String], true),
        ]);
        $actual        = $compositeType->getTypeString();
        self::assertSame($expected, $actual);
    }
}

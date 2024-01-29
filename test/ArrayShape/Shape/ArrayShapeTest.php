<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Shape;

use Kynx\Laminas\FormCli\ArrayShape\Shape\ArrayShape;
use Kynx\Laminas\FormCli\ArrayShape\Shape\ElementShape;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayShape::class)]
final class ArrayShapeTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(ArrayShape $type, string $expected): void
    {
        $actual = $type->getTypeName();
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new ArrayShape('foo', []), 'foo'],
            'escaped'  => [new ArrayShape('foo bar', []), "'foo bar'"],
            'optional' => [new ArrayShape('foo', [], true), 'foo?'],
        ];
    }

    public function testGetTypeStringReturnsPsalmType(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo:     float|int,
            barbar?: string,
        }
        END_OF_EXPECTED;

        $type   = new ArrayShape('baz', [
            new ElementShape('foo', [PsalmType::Int, PsalmType::Float]),
            new ElementShape('barbar', [PsalmType::String], true),
        ]);
        $actual = $type->getTypeString();
        self::assertSame($expected, $actual);
    }

    public function testGetTypeStringRecursesArrayTypes(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: string,
            bar: array{
                baz: int,
            },
        }
        END_OF_EXPECTED;

        $type   = new ArrayShape('', [
            new ElementShape('foo', [PsalmType::String]),
            new ArrayShape('bar', [
                new ElementShape('baz', [PsalmType::Int]),
            ]),
        ]);
        $actual = $type->getTypeString();
        self::assertSame($expected, $actual);
    }
}

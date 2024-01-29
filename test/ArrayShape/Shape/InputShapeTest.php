<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Shape;

use Kynx\Laminas\FormCli\ArrayShape\Shape\ElementShape;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElementShape::class)]
final class InputShapeTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(ElementShape $type, string $expected): void
    {
        $actual = $type->getTypeName();
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new ElementShape('foo', []), 'foo'],
            'escaped'  => [new ElementShape('foo bar', []), "'foo bar'"],
            'optional' => [new ElementShape('foo', [], true), 'foo?'],
        ];
    }

    #[DataProvider('getTypeStringProvider')]
    public function testGetTypeString(ElementShape $type, string $expected): void
    {
        $actual = $type->getTypeString();
        self::assertSame($expected, $actual);
    }

    public static function getTypeStringProvider(): array
    {
        return [
            'single' => [new ElementShape('foo', [PsalmType::String]), 'string'],
            'sorted' => [new ElementShape('foo', [PsalmType::Int, PsalmType::Float]), 'float|int'],
        ];
    }
}

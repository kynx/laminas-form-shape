<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Type\InputType
 */
final class InputTypeTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(InputType $type, string $expected): void
    {
        $actual = $type->getTypeName();
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new InputType('foo', []), 'foo'],
            'escaped'  => [new InputType('foo bar', []), "'foo bar'"],
            'optional' => [new InputType('foo', [], true), 'foo?'],
        ];
    }

    #[DataProvider('getTypeStringProvider')]
    public function testGetTypeString(InputType $type, string $expected): void
    {
        $actual = $type->getTypeString();
        self::assertSame($expected, $actual);
    }

    public static function getTypeStringProvider(): array
    {
        return [
            'single' => [new InputType('foo', [PsalmType::String]), 'string'],
            'sorted' => [new InputType('foo', [PsalmType::Int, PsalmType::Float]), 'float|int'],
        ];
    }
}

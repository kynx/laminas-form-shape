<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Type\Generic
 */
final class GenericTest extends TestCase
{
    public function testGetTypeStringReturnsType(): void
    {
        $expected    = 'array';
        $genericType = new Generic(PsalmType::Array, []);
        $actual      = $genericType->getTypeString();
        self::assertSame($expected, $actual);
    }

    public function testGetTypeStringReturnsGeneric(): void
    {
        $expected    = 'array<int>';
        $genericType = new Generic(
            PsalmType::Array,
            [PsalmType::Int]
        );
        $actual      = $genericType->getTypeString();
        self::assertSame($expected, $actual);
    }
}

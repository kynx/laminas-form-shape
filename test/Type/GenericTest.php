<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Type;

use Kynx\Laminas\FormShape\Type\Generic;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Generic::class)]
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

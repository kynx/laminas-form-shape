<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Type\ClassString
 */
final class ClassStringTest extends TestCase
{
    public function testGetTypeStringReturnsClassString(): void
    {
        $expected    = '\\' . self::class;
        $classString = new ClassString(self::class);
        $actual      = $classString->getTypeString();
        self::assertSame($expected, $actual);
    }

    public function testGetTypeStringTrimsLeadingBackslash(): void
    {
        $expected = '\\' . self::class;
        /** @psalm-suppress ArgumentTypeCoercion */
        $classString = new ClassString($expected);
        $actual      = $classString->getTypeString();
        self::assertSame($expected, $actual);
    }
}

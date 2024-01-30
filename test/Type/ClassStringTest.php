<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Type;

use Kynx\Laminas\FormShape\Type\ClassString;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(ClassString::class)]
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

    /**
     * @param VisitedArray $types
     */
    #[DataProvider('matchesProvider')]
    public function testMatches(ClassString $classString, array $types, bool $expected): void
    {
        $actual = $classString->matches($types);
        self::assertSame($expected, $actual);
    }

    public static function matchesProvider(): array
    {
        $classString = new ClassString(self::class);
        return [
            'self'            => [$classString, [$classString], true],
            'same class'      => [$classString, [new ClassString(self::class)], true],
            'different class' => [$classString, [new ClassString(stdClass::class)], false],
            'different type'  => [$classString, [PsalmType::Object], false],
            'mixed'           => [$classString, [PsalmType::Null, $classString], true],
        ];
    }
}

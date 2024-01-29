<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
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

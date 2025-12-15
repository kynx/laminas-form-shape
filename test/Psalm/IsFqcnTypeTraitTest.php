<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\IsFqcnTypeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TAnonymousClassInstance;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TNamedObject;

#[CoversClass(IsFqcnTypeTrait::class)]
final class IsFqcnTypeTraitTest extends TestCase
{
    #[DataProvider('isFqcnTypeProvider')]
    public function testIsFqcnType(Atomic $type, bool $expected): void
    {
        $trait  = new class {
            use IsFqcnTypeTrait;

            public function test(Atomic $type): bool
            {
                return $this->isFqcnType($type);
            }
        };
        $actual = $trait->test($type);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Atomic, bool}>
     */
    public static function isFqcnTypeProvider(): array
    {
        return [
            'TClosure'                => [new TClosure('callable'), false],
            'TAnonymousClassInstance' => [new TAnonymousClassInstance('new class {}'), false],
            'TNamedObject'            => [new TNamedObject(self::class), true],
        ];
    }
}

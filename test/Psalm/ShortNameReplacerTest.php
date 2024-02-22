<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ShortNameReplacer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

#[CoversClass(ShortNameReplacer::class)]
final class ShortNameReplacerTest extends TestCase
{
    #[DataProvider('enterNodeProvider')]
    public function testEnterNodeReplacesType(Atomic $type, Atomic $expected): void
    {
        $expected = new Union([$expected]);
        $actual = new Union([$type]);
        $replacer = new ShortNameReplacer([]);

        $replacer->traverse($actual);
        self::assertEquals($expected, $actual);
    }

    public static function enterNodeProvider(): array
    {
        return [
            'TTypeAlias' => [
                new TTypeAlias(self::class, 'TFoo'),
                new TNamedObject('TFoo')
            ],
            'TGenericObject' => [
                new TGenericObject(self::class, [new Union([new TInt()])]),
                new TGenericObject('ShortNameReplacerTest', [new Union([new TInt()])]),
            ],
            'TNamedObject' => [
                new TNamedObject(self::class),
                new TNamedObject('ShortNameReplacerTest'),
            ]
        ];
    }

    public function testEnterNodeReplacesAlias(): void
    {
        $expected = new Union([new TNamedObject('FooAlias')]);
        $actual = new Union([new TNamedObject('Foo')]);
        $replacer = new ShortNameReplacer(['Foo' => 'FooAlias']);

        $replacer->traverse($actual);
        self::assertEquals($expected, $actual);
    }
}
<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PsalmType::class)]
final class PsalmTypeTest extends TestCase
{
    public function testToStringReturnsTag(): void
    {
        $expected = '@psalm-type TFoo = array{foo: string, bar: int}';
        $tag      = new PsalmType('TFoo', 'array{foo: string, bar: int}');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmType('TFoo', 'array{foo: string, bar: int}');
        $actual = $tag->isBefore($match);
        self::assertSame($expected, $actual);
    }

    public static function isBeforeProvider(): array
    {
        return [
            'param'             => [new GenericTag('@param int $foo'), false],
            'psalm-type'        => [new GenericTag('@psalm-type TBar = object'), true],
            'psalm-import-type' => [new GenericTag('@psalm-import-type TBar from Bar'), false],
        ];
    }

    #[DataProvider('matchProvider')]
    public function testMatches(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmType('TFoo', 'array{foo: string, bar: int}');
        $actual = $tag->matches($match);
        self::assertSame($expected, $actual);
    }

    public static function matchProvider(): array
    {
        return [
            'same name'      => [new GenericTag('@psalm-type TFoo = list<string>'), true],
            'different name' => [new GenericTag('@psalm-type TBar = list<string>'), false],
            'different tag'  => [new GenericTag('@param int $foo'), false],
        ];
    }
}

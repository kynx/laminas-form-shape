<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmImportType;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PsalmImportType::class)]
final class PsalmImportTypeTest extends TestCase
{
    public function testToStringReturnsTag(): void
    {
        $expected = '@psalm-import-type TFoo from Bar';
        $tag      = new PsalmImportType('TFoo', 'Bar');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    public function testToStringReturnsTagWithAlias(): void
    {
        $expected = '@psalm-import-type TFoo from Bar as TFooBar';
        $tag      = new PsalmImportType('TFoo', 'Bar', 'TFooBar');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmImportType('TFoo', 'Bar');
        $actual = $tag->isBefore($match);
        self::assertSame($expected, $actual);
    }

    public static function isBeforeProvider(): array
    {
        return [
            'psalm-type' => [new PsalmType('TFoo', 'array{foo: int}'), true],
            'other'      => [new GenericTag('@internal'), false],
        ];
    }

    #[DataProvider('matchProvider')]
    public function testMatches(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmImportType('TFoo', 'Bar');
        $actual = $tag->matches($match);
        self::assertSame($expected, $actual);
    }

    public static function matchProvider(): array
    {
        return [
            'same tag'       => [new GenericTag('@psalm-import-type TFoo from Bar'), true],
            'whitespace'     => [new GenericTag(" @psalm-import-type\nTFoo\nfrom  Bar"), true],
            'different type' => [new GenericTag('@psalm-import-type TBaz from Bar'), false],
        ];
    }
}

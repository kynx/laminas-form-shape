<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmImplements;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PsalmImplements::class)]
final class PsalmImplementsTest extends TestCase
{
    public function testToStringReturnsTag(): void
    {
        $expected = '@implements FormInterface<TFormArray>';
        $tag      = new PsalmImplements('FormInterface', 'TFormArray');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmImplements('FormInterface', 'TFormArray');
        $actual = $tag->isBefore($match);
        self::assertSame($expected, $actual);
    }

    public static function isBeforeProvider(): array
    {
        return [
            'internal'   => [new GenericTag('@internal'), false],
            'psalm-type' => [new GenericTag('@psalm-type TFoo = array<int>'), false],
        ];
    }

    #[DataProvider('matchProvider')]
    public function testMatches(TagInterface $match, bool $expected): void
    {
        $tag    = new PsalmImplements('FormInterface', 'TFormArray');
        $actual = $tag->matches($match);
        self::assertSame($expected, $actual);
    }

    public static function matchProvider(): array
    {
        return [
            'extends'    => [new GenericTag('@implements Foo<Bar>'), true],
            'psalm-type' => [new GenericTag('@psalm-type TFoo = array<int>'), false],
        ];
    }
}

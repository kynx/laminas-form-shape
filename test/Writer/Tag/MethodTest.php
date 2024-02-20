<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\Method;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Method::class)]
final class MethodTest extends TestCase
{
    public function testToStringReturnsTag(): void
    {
        $expected = '@method int foo(int $a)';
        $tag      = new Method('foo', ['int $a'], 'int');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(TagInterface $match, bool $expected): void
    {
        $tag    = new Method('foo');
        $actual = $tag->isBefore($match);
        self::assertSame($expected, $actual);
    }

    public static function isBeforeProvider(): array
    {
        return [
            'param'  => [new GenericTag('@param int $foo'), true],
            'return' => [new GenericTag('@return int'), true],
            'psalm'  => [new PsalmType('TFoo', 'array'), true],
            'other'  => [new GenericTag('@internal'), false],
        ];
    }

    #[DataProvider('matchProvider')]
    public function testMatches(TagInterface $match, bool $expected): void
    {
        $tag    = new Method('foo');
        $actual = $tag->matches($match);
        self::assertSame($expected, $actual);
    }

    public static function matchProvider(): array
    {
        return [
            'same method'      => [new GenericTag('@method int foo(int $bar)'), true],
            'whitespace'       => [new GenericTag(" @method array{\nfoo: int,\n} foo ()"), true],
            'different method' => [new GenericTag('@method int bar(int $bar)'), false],
            'different tag'    => [new GenericTag('@return int'), false],
        ];
    }
}

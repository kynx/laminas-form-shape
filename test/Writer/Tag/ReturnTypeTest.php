<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use Kynx\Laminas\FormShape\Writer\Tag\ReturnType;
use Kynx\Laminas\FormShape\Writer\Tag\TagInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReturnType::class)]
final class ReturnTypeTest extends TestCase
{
    public function testToStringReturnsTag(): void
    {
        $expected = '@return list<int>';
        $tag      = new ReturnType('list<int>');
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    public function testIsBeforeReturnsFalse(): void
    {
        $tag    = new ReturnType('int');
        $actual = $tag->isBefore(new GenericTag('@param int $foo'));
        self::assertFalse($actual);
    }

    #[DataProvider('matchProvider')]
    public function testMatches(TagInterface $match, bool $expected): void
    {
        $tag    = new ReturnType('int');
        $actual = $tag->matches($match);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, list{TagInterface, bool}>
     */
    public static function matchProvider(): array
    {
        return [
            'return' => [new GenericTag('@return Foo'), true],
            'param'  => [new GenericTag('@param int $foo'), false],
        ];
    }
}

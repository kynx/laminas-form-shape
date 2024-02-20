<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer\Tag;

use Kynx\Laminas\FormShape\Writer\Tag\GenericTag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenericTag::class)]
final class GenericTagTest extends TestCase
{
    public function testToStringReturnsContent(): void
    {
        $expected = '@param int $foo';
        $tag      = new GenericTag($expected);
        $actual   = (string) $tag;
        self::assertSame($expected, $actual);
    }

    public function testIsBeforeReturnsFalse(): void
    {
        $tag    = new GenericTag('@param int $foo');
        $actual = $tag->isBefore(new GenericTag('@param int $bar'));
        self::assertFalse($actual);
    }

    public function testMatchesReturnsTrue(): void
    {
        $tag    = new GenericTag('@param int $foo');
        $actual = $tag->matches(new GenericTag('@param int $foo'));
        self::assertTrue($actual);
    }
}

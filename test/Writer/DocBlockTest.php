<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\DocBlock;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocBlock::class)]
final class DocBlockTest extends TestCase
{
    public function testEmptyDocBockReturnEmptyString(): void
    {
        $expected = '';
        $actual   = (string) DocBlock::fromDocComment(false);
        self::assertSame($expected, $actual);
    }

    public function testToStringReturnsDocblockUnaltered(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * In Xanadu did Kablia Khan a stately pleasure dome decree
         * 
         * @param River \$river
         * @param int|null \$depth
         */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment($expected);
        self::assertSame($expected, $actual);
    }

    public function testWithTagAddsToEmptyDocBlock(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * @psalm-type TFoo = array<int>
         */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment(false)
            ->withTag(new PsalmType('TFoo', 'array<int>'));
        self::assertSame($expected, $actual);
    }

    public function testWithTagAddsMultilineTag(): void
    {
        $expected   = <<<DOCBLOCK
        /**
         * @psalm-type TFoo = array{array-key, array{
         *     id:   int,
         *     name: string,
         * }
         */
        DOCBLOCK;
        $definition = <<<DEFINITION
        array{array-key, array{
            id:   int,
            name: string,
        }
        DEFINITION;

        $actual = (string) DocBlock::fromDocComment(false)
            ->withTag(new PsalmType('TFoo', $definition));
        self::assertSame($expected, $actual);
    }

    public function testWithTagAppendsTagToExistingDocBlock(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * Foo
         * 
         * @internal
         * @psalm-type TFoo = array<int>
         */
        DOCBLOCK;
        $existing = <<<DOCBLOCK
            /**
             * Foo
             * 
             * @internal
             */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment($existing)
            ->withTag(new PsalmType('TFoo', 'array<int>'));
        self::assertSame($expected, $actual);
    }

    public function testWithTagReplacesExisting(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * Foo
         * 
         * @psalm-type TFoo = array<int>
         * @internal
         */
        DOCBLOCK;
        $existing = <<<DOCBLOCK
            /**
             * Foo
             * 
             * @psalm-type TFoo = array<string>
             * @internal
             */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment($existing)
            ->withTag(new PsalmType('TFoo', 'array<int>'));
        self::assertSame($expected, $actual);
    }

    public function testWithoutTagReturnsUnaltered(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * Foo
         * 
         * @psalm-type TFoo = array<int>
         * @internal
         */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment($expected)
            ->withoutTag(new PsalmType('TBar', 'array<int>'));
        self::assertSame($expected, $actual);
    }

    public function testWithoutTagRemovesMatching(): void
    {
        $expected = <<<DOCBLOCK
        /**
         * Foo
         * 
         * @internal
         */
        DOCBLOCK;
        $existing = <<<DOCBLOCK
            /**
             * Foo
             * 
             * @psalm-type TFoo = array<int>
             * @internal
             */
        DOCBLOCK;

        $actual = (string) DocBlock::fromDocComment($existing)
            ->withoutTag(new PsalmType('TFoo', 'array<string>'));
        self::assertSame($expected, $actual);
    }

    public function testGetContentsReturnsRawString(): void
    {
        $expected = <<<DOCBLOCK
        Foo
        
        @internal
        @psalm-type TFoo = array<int>
        DOCBLOCK;
        $existing = <<<DOCBLOCK
            /**
             * Foo
             * 
             * @internal
             */
        DOCBLOCK;

        $actual = DocBlock::fromDocComment($existing)
            ->withTag(new PsalmType('TFoo', 'array<int>'))
            ->getContents("\n");
        self::assertSame($expected, $actual);
    }
}

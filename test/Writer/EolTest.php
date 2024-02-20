<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\Eol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function str_pad;

use const PHP_EOL;

/**
 * @covers \Kynx\Laminas\FormShape\Writer\Eol
 */
#[CoversClass(Eol::class)]
final class EolTest extends TestCase
{
    #[DataProvider('eolProvider')]
    public function testDetect(string $contents, string $expected): void
    {
        $actual = Eol::detect($contents);
        self::assertSame($expected, $actual);
    }

    public static function eolProvider(): array
    {
        return [
            'no eol' => ['   ', PHP_EOL],
            '\r\n'   => ["abc\r\ndef", "\r\n"],
            '\n'     => ["abc\ndef", "\n"],
            'mixed'  => ["abc\r\ndef\nghi\n", "\n"],
        ];
    }

    public function testDetectUsesSubstring(): void
    {
        $eol      = PHP_EOL === "\r\n" ? "\n" : "\r\n";
        $contents = str_pad('', 4096) . $eol;
        $actual   = Eol::detect($contents);
        self::assertSame(PHP_EOL, $actual);
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\Eol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

/**
 * @covers \Kynx\Laminas\FormShape\Writer\Eol
 */
#[CoversClass(Eol::class)]
final class EolTest extends TestCase
{
    #[DataProvider('eolProvider')]
    public function testDetectEol(string $contents, string $expected): void
    {
        $actual = Eol::detectEol($contents);
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
}

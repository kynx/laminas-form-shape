<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Literal::class)]
final class LiteralTest extends TestCase
{
    public function testGetTypeStringSortsValues(): void
    {
        $expected = "'zebedee'|int|string";
        $literal  = new Literal(["string", "'zebedee'", "int"]);
        $actual   = $literal->getTypeString();
        self::assertSame($expected, $actual);
    }
}

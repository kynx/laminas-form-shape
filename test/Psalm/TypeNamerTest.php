<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(TypeNamer::class)]
final class TypeNamerTest extends TestCase
{
    public function testNameFormatsClassName(): void
    {
        $expected   = 'TTypeNamerTestArray';
        $template   = 'T{shortName}Array';
        $namer      = new TypeNamer($template);
        $reflection = new ReflectionClass($this);

        $actual = $namer->name($reflection);
        self::assertSame($expected, $actual);
    }
}

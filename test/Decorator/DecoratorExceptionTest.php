<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\DecoratorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DecoratorException::class)]
final class DecoratorExceptionTest extends TestCase
{
    public function testFromEmptyUnion(): void
    {
        $expected  = 'Cannot decorate empty union';
        $exception = DecoratorException::fromEmptyUnion();
        self::assertSame($expected, $exception->getMessage());
    }
}

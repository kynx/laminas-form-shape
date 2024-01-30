<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\AbstractDecorator;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractDecorator::class)]
final class AbstractDecoratorTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(ArrayShape|ElementShape $shape, string $expected): void
    {
        $decorator = new MockDecorator();
        $actual    = $decorator->getTypeName($shape);
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new ArrayShape('foo', []), 'foo'],
            'escaped'  => [new ElementShape('foo bar', []), "'foo bar'"],
            'optional' => [new ElementShape('foo', [], true), 'foo?'],
        ];
    }
}

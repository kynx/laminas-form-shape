<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\AbstractInputFilterShapeDecorator;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractInputFilterShapeDecorator::class)]
final class AbstractInputFilterShapeDecoratorTest extends TestCase
{
    #[DataProvider('getTypeNameProvider')]
    public function testGetTypeName(InputFilterShape|InputShape $shape, string $expected): void
    {
        $decorator = new MockInputFilterShapeDecorator();
        $actual    = $decorator->getTypeName($shape);
        self::assertSame($expected, $actual);
    }

    public static function getTypeNameProvider(): array
    {
        return [
            'required' => [new InputFilterShape('foo', []), 'foo'],
            'escaped'  => [new InputShape('foo bar', []), "'foo bar'"],
            'optional' => [new InputShape('foo', [], true), 'foo?'],
        ];
    }
}

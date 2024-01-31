<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecorator;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputFilterShapeDecorator::class)]
final class InputFilterShapeDecoratorTest extends TestCase
{
    public function testDecorateReturnsPsalmType(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo:     float|int,
            barbar?: string,
        }
        END_OF_EXPECTED;

        $shape     = new InputFilterShape('baz', [
            new InputShape('foo', [PsalmType::Int, PsalmType::Float]),
            new InputShape('barbar', [PsalmType::String], true),
        ]);
        $decorator = new InputFilterShapeDecorator();

        $actual = $decorator->decorate($shape);
        self::assertSame($expected, $actual);
    }

    public function testDecorateRecursesArrayShapes(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo: string,
            bar: array{
                baz: int,
            },
        }
        END_OF_EXPECTED;

        $shape     = new InputFilterShape('', [
            new InputShape('foo', [PsalmType::String]),
            new InputFilterShape('bar', [
                new InputShape('baz', [PsalmType::Int]),
            ]),
        ]);
        $decorator = new InputFilterShapeDecorator();

        $actual = $decorator->decorate($shape);
        self::assertSame($expected, $actual);
    }
}

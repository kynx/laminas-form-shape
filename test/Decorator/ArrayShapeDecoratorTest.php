<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\ArrayShapeDecorator;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormShape\Decorator\ArrayShapeDecorator
 */
final class ArrayShapeDecoratorTest extends TestCase
{
    public function testDecorateReturnsPsalmType(): void
    {
        $expected = <<<END_OF_EXPECTED
        array{
            foo:     float|int,
            barbar?: string,
        }
        END_OF_EXPECTED;

        $shape     = new ArrayShape('baz', [
            new ElementShape('foo', [PsalmType::Int, PsalmType::Float]),
            new ElementShape('barbar', [PsalmType::String], true),
        ]);
        $decorator = new ArrayShapeDecorator();

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

        $shape     = new ArrayShape('', [
            new ElementShape('foo', [PsalmType::String]),
            new ArrayShape('bar', [
                new ElementShape('baz', [PsalmType::Int]),
            ]),
        ]);
        $decorator = new ArrayShapeDecorator();

        $actual = $decorator->decorate($shape);
        self::assertSame($expected, $actual);
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\CollectionFilterShapeDecorator;
use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecorator;
use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionFilterShapeDecorator::class)]
final class CollectionFilterShapeDecoratorTest extends TestCase
{
    public function testDecorateReturnsNonEmptyArray(): void
    {
        $expected = <<<END_OF_EXPECTED
        non-empty-array<array{
            foo: int,
        }>
        END_OF_EXPECTED;

        $inputFilter      = new InputFilterShape('', [new InputShape('foo', [PsalmType::Int])]);
        $collectionFilter = new CollectionFilterShape('', $inputFilter);
        $decorator        = new CollectionFilterShapeDecorator(new InputFilterShapeDecorator());

        $actual = $decorator->decorate($collectionFilter, 0);
        self::assertSame($expected, $actual);
    }

    public function testDecorateReturnsStandardArray(): void
    {
        $expected = <<<END_OF_EXPECTED
        array<array{
            foo: int,
        }>
        END_OF_EXPECTED;

        $inputFilter      = new InputFilterShape('', [new InputShape('foo', [PsalmType::Int])]);
        $collectionFilter = new CollectionFilterShape('', $inputFilter, true, false);
        $decorator        = new CollectionFilterShapeDecorator(new InputFilterShapeDecorator());

        $actual = $decorator->decorate($collectionFilter, 0);
        self::assertSame($expected, $actual);
    }

    public function testDecorateNestedCollection(): void
    {
        $expected = <<<END_OF_EXPECTED
        array<array<array{
            foo: int,
        }>>
        END_OF_EXPECTED;

        $inputFilter      = new InputFilterShape('', [new InputShape('foo', [PsalmType::Int])]);
        $nestedCollection = new CollectionFilterShape('', $inputFilter, true, false);
        $collectionFilter = new CollectionFilterShape('', $nestedCollection, true, false);
        $decorator        = new CollectionFilterShapeDecorator(new InputFilterShapeDecorator());

        $actual = $decorator->decorate($collectionFilter, 0);
        self::assertSame($expected, $actual);
    }
}

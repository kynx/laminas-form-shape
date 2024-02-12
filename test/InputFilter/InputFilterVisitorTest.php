<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\OptionalInputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(InputFilterVisitor::class)]
final class InputFilterVisitorTest extends TestCase
{
    private InputFilterVisitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $inputVisitor        = new InputVisitor([], []);
        $inputVisitorManager = new InputVisitorManager([Input::class => $inputVisitor]);
        $this->visitor       = new InputFilterVisitor($inputVisitorManager);
    }

    public function testVisitReturnsUnion(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $inputFilter->add(new Input('bar'));

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('collectionProvider')]
    public function testVisitReturnsCollectionUnion(bool $required, int $count, TArray $array): void
    {
        $expected = new Union([$array], ['possibly_undefined' => ! $required]);

        $collectionFilter = new InputFilter();
        $collectionFilter->add(new Input('foo'));
        $inputFilter = new CollectionInputFilter();
        $inputFilter->setIsRequired($required);
        $inputFilter->setCount($count);
        $inputFilter->setInputFilter($collectionFilter);

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    public static function collectionProvider(): array
    {
        $union = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        return [
            'required, 0 count'     => [
                true,
                0,
                new TNonEmptyArray([Type::getArrayKey(), $union], null, 1),
            ],
            'required, 2 count'     => [
                true,
                2,
                new TNonEmptyArray([Type::getArrayKey(), $union], null, 2),
            ],
            'not required, 0 count' => [
                false,
                0,
                new TArray([Type::getArrayKey(), $union]),
            ],
            'not required, 1 count' => [
                false,
                1,
                new TNonEmptyArray([Type::getArrayKey(), $union], null, 1),
            ],
        ];
    }

    public function testVisitNestedInputFilterReturnsNestedUnion(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TKeyedArray([
                        'bar' => new Union([new TString(), new TNull()]),
                        'baz' => new Union([new TString(), new TNull()]),
                    ]),
                ]),
            ]),
        ]);

        $childFilter = new InputFilter();
        $childFilter->add(new Input('bar'));
        $childFilter->add(new Input('baz'));
        $inputFilter = new InputFilter();
        $inputFilter->add($childFilter, 'foo');

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    public function testVisitFilterWithNoInputsReturnsMixedArray(): void
    {
        $expected = new Union([
            new TArray([Type::getArrayKey(), Type::getMixed()]),
        ]);

        $inputFilter = new InputFilter();

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsPossiblyUndefinedUnion(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ], ['possibly_undefined' => true]);

        $inputFilter = new OptionalInputFilter();
        $inputFilter->add(new Input('foo'));

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }
}

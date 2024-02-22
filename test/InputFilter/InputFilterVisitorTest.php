<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
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
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

#[CoversClass(InputFilterVisitor::class)]
final class InputFilterVisitorTest extends TestCase
{
    private InputFilterVisitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $inputVisitor  = new InputVisitor([], []);
        $this->visitor = new InputFilterVisitor([$inputVisitor]);
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

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('collectionProvider')]
    public function testVisitReturnsCollectionUnion(bool $required, TArray $array): void
    {
        $expected = new Union([$array], ['possibly_undefined' => ! $required]);

        $collectionFilter = new InputFilter();
        $collectionFilter->add(new Input('foo'));
        $inputFilter = new CollectionInputFilter();
        $inputFilter->setIsRequired($required);
        $inputFilter->setInputFilter($collectionFilter);

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
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
            'required'     => [
                true,
                new TNonEmptyArray([Type::getArrayKey(), $union]),
            ],
            'not required' => [
                false,
                new TArray([Type::getArrayKey(), $union]),
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

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitFilterWithNoInputsReturnsMixedArray(): void
    {
        $expected = new Union([
            new TArray([Type::getArrayKey(), Type::getMixed()]),
        ], ['possibly_undefined' => true]);

        $inputFilter = new InputFilter();

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsPossiblyUndefinedUnion(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ], ['possibly_undefined' => true]);

        $input = new Input('foo');
        $input->setRequired(true);
        $inputFilter = new OptionalInputFilter();
        $inputFilter->add($input);

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsPossiblyUndefinedUnionWhenAllElementsUndefined(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()], ['possibly_undefined' => true]),
                'bar' => new Union([new TString(), new TNull()], ['possibly_undefined' => true]),
            ]),
        ], ['possibly_undefined' => true]);

        $inputFilter = new InputFilter();
        $inputFilter->add((new Input('foo'))->setRequired(false));
        $inputFilter->add((new Input('bar'))->setRequired(false));

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsRequiredUnionWhenOneElementRequired(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()], ['possibly_undefined' => true]),
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add((new Input('foo'))->setRequired(false));
        $inputFilter->add(new Input('bar'));

        $actual = $this->visitor->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsMatchingImportedType(): void
    {
        $expected    = new Union([new TTypeAlias('self', 'TImportType')]);
        $importUnion = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $importType = new ImportType(new TTypeAlias('self', 'TImportType'), $importUnion);

        $actual = $this->visitor->visit($inputFilter, $importType);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsCalculatedTypeForNonMatchingImportType(): void
    {
        $expected    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $importUnion = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $inputFilter->add(new Input('bar'));
        $importType = new ImportType(new TTypeAlias('self', 'TImportType'), $importUnion);

        $actual = $this->visitor->visit($inputFilter, $importType);
        self::assertEquals($expected, $actual);
    }

    public function testVisitReturnsNestedImportType(): void
    {
        $expected    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TTypeAlias('self', 'TImportType')]),
            ]),
        ]);
        $importUnion = new Union([
            new TKeyedArray([
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $childFilter = new InputFilter();
        $childFilter->add(new Input('bar'));
        $inputFilter = new InputFilter();
        $inputFilter->add($childFilter, 'foo');
        $importType = new ImportType(new TTypeAlias('self', 'TImportType'), $importUnion);

        $actual = $this->visitor->visit($inputFilter, new ImportTypes(['foo' => $importType]));
        self::assertEquals($expected, $actual);
    }

    public function testVisitNoValidInputVisitorThrowsException(): void
    {
        $expected     = "No input visitor configured for '" . Input::class . "'";
        $arrayVisitor = new ArrayInputVisitor(new InputVisitor([], []));
        $visitor      = new InputFilterVisitor([$arrayVisitor]);
        $inputFilter  = new InputFilter();
        $inputFilter->add(new Input());

        self::expectException(InputVisitorException::class);
        self::expectExceptionMessage($expected);
        $visitor->visit($inputFilter, new ImportTypes([]));
    }
}

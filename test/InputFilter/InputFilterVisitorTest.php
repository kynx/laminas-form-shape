<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\OptionalInputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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

    public function testVisitReturnsInputShape(): void
    {
        $expected = new InputFilterShape('', [
            new InputShape('foo', [PsalmType::Null, PsalmType::String], false),
            new InputShape('bar', [PsalmType::Null, PsalmType::String], false),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $inputFilter->add(new Input('bar'));

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('collectionProvider')]
    public function testVisitReturnsCollectionShape(bool $required, int $count, bool $optional, bool $nonEmpty): void
    {
        $expected = new CollectionFilterShape(
            '',
            new InputFilterShape('', [new InputShape('foo', [PsalmType::Null, PsalmType::String])], false),
            $optional,
            $nonEmpty
        );

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
        return [
            'required, 0 count'     => [true, 0, false, false],
            'required, 1 count'     => [true, 1, false, true],
            'not required, 0 count' => [false, 0, true, false],
            'not required, 1 count' => [false, 1, false, true],
        ];
    }

    public function testVisitRecursesInputFilter(): void
    {
        $expected = new InputFilterShape('', [
            new InputFilterShape('foo', [
                new InputShape('bar', [PsalmType::Null, PsalmType::String], false),
                new InputShape('baz', [PsalmType::Null, PsalmType::String], false),
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

    public function testVisitReturnsOptionalInputFilterShape(): void
    {
        $expected = new InputFilterShape('', [], true);

        $inputFilter = new OptionalInputFilter();

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }
}

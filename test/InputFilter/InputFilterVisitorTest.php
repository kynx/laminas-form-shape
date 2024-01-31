<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\OptionalInputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
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

    public function testGetArrayTypeRecursesInputFilter(): void
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

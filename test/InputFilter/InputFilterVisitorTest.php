<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
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

    public function testGetArrayTypeReturnsInputTypes(): void
    {
        $expected = new ArrayShape('', [
            new ElementShape('foo', [PsalmType::Null, PsalmType::String], false),
            new ElementShape('bar', [PsalmType::Null, PsalmType::String], false),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $inputFilter->add(new Input('bar'));

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    public function testGetArrayTypeRecursesInputFilter(): void
    {
        $expected = new ArrayShape('', [
            new ArrayShape('foo', [
                new ElementShape('bar', [PsalmType::Null, PsalmType::String], false),
                new ElementShape('baz', [PsalmType::Null, PsalmType::String], false),
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
}

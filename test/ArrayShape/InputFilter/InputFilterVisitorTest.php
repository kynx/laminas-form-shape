<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormCli\ArrayShape\Type\ArrayType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
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
        $expected = new ArrayType('', [
            new InputType('foo', [PsalmType::String], false),
            new InputType('bar', [PsalmType::String], false),
        ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));
        $inputFilter->add(new Input('bar'));

        $actual = $this->visitor->visit($inputFilter);
        self::assertEquals($expected, $actual);
    }

    public function testGetArrayTypeRecursesInputFilter(): void
    {
        $expected = new ArrayType('', [
            new ArrayType('foo', [
                new InputType('bar', [PsalmType::String], false),
                new InputType('baz', [PsalmType::String], false),
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

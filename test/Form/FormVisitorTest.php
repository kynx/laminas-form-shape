<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(FormVisitor::class)]
final class FormVisitorTest extends TestCase
{
    private FormVisitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $inputVisitor        = new InputVisitor([], []);
        $inputVisitorManager = new InputVisitorManager([Input::class => $inputVisitor]);
        $this->visitor       = new FormVisitor(new InputFilterVisitor($inputVisitorManager));
    }

    public function testVisitReturnsInputFilterShape(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()], ['possibly_undefined' => true]),
            ]),
        ]);

        $form = new Form();
        $form->add(new Text('foo'));

        $actual = $this->visitor->visit($form);
        self::assertEquals($expected, $actual);
    }
}

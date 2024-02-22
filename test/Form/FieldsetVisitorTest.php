<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Form\FieldsetVisitor;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(FieldsetVisitor::class)]
final class FieldsetVisitorTest extends TestCase
{
    public function testVisitReturnsFieldsetUnion(): void
    {
        $expected = new Union([new TKeyedArray([
            'foo' => new Union([new TString(), new TNull()], ['possibly_undefined' => true]),
            'bar' => new Union([new TString(), new TNull()], ['possibly_undefined' => true])
        ])], ['possibly_undefined' => true]);
        $formVisitor = new FormVisitor(new InputFilterVisitor([new InputVisitor([], [])]));
        $fieldsetVisitor = new FieldsetVisitor($formVisitor);

        $fieldset = new Fieldset();
        $fieldset->add(new Text('foo'));
        $fieldset->add(new Text('bar'));

        $actual = $fieldsetVisitor->visit($fieldset, []);
        self::assertEquals($expected, $actual);
    }
}

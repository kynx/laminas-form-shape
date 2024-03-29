<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use KynxTest\Laminas\FormShape\Form\Asset\InputFilterFieldset;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Text;
use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;

use function array_keys;

#[CoversClass(FormVisitor::class)]
final class FormVisitorTest extends TestCase
{
    private FormVisitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigLoader::load();

        $inputVisitor      = new InputVisitor([], []);
        $arrayInputVisitor = new ArrayInputVisitor([], []);
        $this->visitor     = new FormVisitor(new InputFilterVisitor([$arrayInputVisitor, $inputVisitor]));
    }

    public function testVisitSingleElement(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);

        $form = new Form();
        $form->add(new Text('foo'));

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCollectionWithFieldsetTargetElement(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([
                        Type::getArrayKey(),
                        new Union([
                            new TKeyedArray([
                                'baz' => new Union([new TString(), new TNull()]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        $form          = new Form();
        $collection    = new Collection('foo');
        $targetElement = new Fieldset('bar');
        $targetElement->add(new Text('baz'));
        $collection->setTargetElement($targetElement);
        $form->add($collection);

        $clone = clone $form;
        $clone->setData([]);
        self::assertTrue($clone->isValid());

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);

        $inputFilter = $form->getInputFilter();
        $inputFilter->setData([]);
        self::assertTrue($inputFilter->isValid());
    }

    public function testVisitCollectionWithInputFilterProviderTargetElement(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([
                        Type::getArrayKey(),
                        new Union([
                            new TKeyedArray([
                                'first'  => new Union([new TString(), new TNull()]),
                                'second' => new Union([new TString(), new TNull()]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        $form          = new Form();
        $collection    = new Collection('foo');
        $targetElement = new InputFilterFieldset('bar');
        $collection->setTargetElement($targetElement);
        $form->add($collection);

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);
    }

    public function testVisitNonEmptyCollection(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TNonEmptyArray([
                        Type::getArrayKey(),
                        new Union([
                            new TKeyedArray([
                                'baz' => new Union([new TString(), new TNull()]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        $form       = new Form();
        $collection = new Collection('foo');
        $collection->setCount(3);
        $collection->setAllowRemove(false);
        $targetElement = new Fieldset('bar');
        $targetElement->add(new Email('baz'));
        $collection->setTargetElement($targetElement);
        $form->add($collection);

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCollectionWithTextTargetElement(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([
                        Type::getArrayKey(),
                        new Union([new TString(), new TNull()]),
                    ]),
                ]),
            ]),
        ]);

        $form       = new Form();
        $collection = new Collection('foo');
        $collection->setTargetElement(new Text());
        $form->add($collection);

        $clone = clone $form;
        $clone->setData([]);
        $clone->isValid();
        /** @var array $data */
        $data = $clone->getData();
        self::assertArrayHasKey('foo', $data);

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);
    }

    public function testVisitNestedCollection(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([
                        Type::getArrayKey(),
                        new Union([
                            new TKeyedArray([
                                'bar' => new Union([
                                    new TArray([
                                        Type::getArrayKey(),
                                        new Union([new TString(), new TNull()]),
                                    ]),
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        $form          = new Form();
        $collection    = new Collection('foo');
        $targetElement = new Fieldset();
        $nested        = new Collection('bar');
        $nested->setTargetElement(new Text());
        $targetElement->add($nested);
        $collection->setTargetElement($targetElement);
        $form->add($collection);

        $clone = clone $form;
        $clone->setData([]);
        self::assertTrue($clone->isValid());

        $actual = $this->visitor->visit($form, []);
        self::assertEquals($expected, $actual);

        $inputFilter = $form->getInputFilter();
        $inputFilter->setData([]);
        self::assertTrue($inputFilter->isValid());
    }

    public function testVisitWithImportTypes(): void
    {
        $typeAlias   = new TTypeAlias(Fieldset::class, 'TFoo');
        $unusedAlias = new TTypeAlias(self::class, 'TUnused');
        $expected    = new Union([
            new TKeyedArray([
                'foo' => new Union([$typeAlias]),
            ]),
        ]);

        $form     = new Form();
        $fieldset = new Fieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $importType = new ImportType($typeAlias, new Union([
            new TKeyedArray([
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]));
        $unusedType = new ImportType($unusedAlias, new Union([new TInt()]));

        $actual = $this->visitor->visit($form, [
            Fieldset::class => $importType,
            self::class     => $unusedType,
        ]);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCollectionWithImportTypes(): void
    {
        $typeAlias = new TTypeAlias(Fieldset::class, 'TFoo');
        $expected  = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TArray([Type::getArrayKey(), new Union([$typeAlias])]),
                ]),
            ]),
        ]);

        $form       = new Form();
        $collection = new Collection('foo');
        $fieldset   = new Fieldset();
        $fieldset->add(new Text('bar'));
        $collection->setTargetElement($fieldset);
        $form->add($collection);

        $importType = new ImportType($typeAlias, new Union([
            new TKeyedArray([
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]));

        $actual = $this->visitor->visit($form, [Fieldset::class => $importType]);
        self::assertEquals($expected, $actual);
    }

    public function testVisitPreservesInputOrderWhenInputIsRequired(): void
    {
        $expected = ['first', 'second'];

        $form = new Form();
        $form->add(new InputFilterFieldset('foo'));

        $formArray = $this->visitor->visit($form, [])->getSingleAtomic();
        self::assertInstanceOf(TKeyedArray::class, $formArray);
        $foo = $formArray->properties['foo'] ?? null;
        self::assertInstanceOf(Union::class, $foo);
        $fooArray = $foo->getSingleAtomic();
        self::assertInstanceOf(TKeyedArray::class, $fooArray);

        $actual = array_keys($fooArray->properties);
        self::assertSame($expected, $actual);
    }

    public function testVisitPopulatesFormWithStrings(): void
    {
        $element = self::createMock(ElementInterface::class);
        $element->method('getName')
            ->willReturn('foo');
        $element->method('setValue')
            ->willReturnCallback(static function (mixed $value) {
                self::assertSame('', $value);
            });

        $form = new Form();
        $form->add($element);
        $this->visitor->visit($form, []);
    }
}

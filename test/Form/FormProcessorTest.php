<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Form\FormProcessor;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use KynxTest\Laminas\FormShape\Form\Asset\ChildFieldset;
use KynxTest\Laminas\FormShape\Form\Asset\CustomFieldset;
use KynxTest\Laminas\FormShape\Form\Asset\CustomForm;
use KynxTest\Laminas\FormShape\Form\Asset\IgnoredFieldset;
use KynxTest\Laminas\FormShape\Form\Asset\TestFieldset;
use KynxTest\Laminas\FormShape\Writer\MockWriter;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;
use ReflectionClass;

#[CoversClass(FormProcessor::class)]
final class FormProcessorTest extends TestCase
{
    private FormLocatorInterface&Stub $formLocator;
    private MockWriter $fileWriter;
    private MockProgressListener $listener;
    private FormProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formLocator = self::createStub(FormLocatorInterface::class);
        $this->fileWriter  = new MockWriter();
        $this->listener    = new MockProgressListener();

        $inputVisitor      = new InputVisitor([], []);
        $collectionVisitor = new CollectionInputVisitor($inputVisitor);

        $this->processor = new FormProcessor(
            $this->formLocator,
            new FormVisitor(new InputFilterVisitor([$collectionVisitor, $inputVisitor])),
            $this->fileWriter
        );
    }

    public function testProcessNoFilesReturnsEarly(): void
    {
        $this->formLocator->method('locate')
            ->willReturn([]);
        $this->processor->process(['foo'], $this->listener);
        self::assertSame(0, $this->listener->processed);
    }

    public function testProcessContinuesAfterError(): void
    {
        $errors = [
            "Error processing " . TestFieldset::class . ": Cannot get type for 'bar'",
            "Error processing " . Form::class . ": Cannot get type for 'bar'",
        ];

        $first    = new Form();
        $fieldset = new TestFieldset('foo');
        $fieldset->add(new Text('bar'));
        $first->add($fieldset);

        $second = new Form();
        $second->add(new Text('foo'));

        $this->formLocator->method('locate')
            ->willReturn([
                new FormFile(new ReflectionClass($first), $first),
                new FormFile(new ReflectionClass($second), $second),
            ]);

        $inputVisitor = self::createStub(InputVisitorInterface::class);
        $inputVisitor->method('visit')
            ->willReturnCallback(static function (Input $input): Union {
                if ($input->getName() === 'bar') {
                    throw InputVisitorException::cannotGetInputType($input);
                }
                return new Union([new TString()]);
            });

        $processor = new FormProcessor(
            $this->formLocator,
            new FormVisitor(new InputFilterVisitor([$inputVisitor])),
            $this->fileWriter
        );

        $processor->process(['foo'], $this->listener);

        self::assertSame($errors, $this->listener->errors);
        self::assertCount(1, $this->listener->success);
    }

    public function testProcessFieldsetWritesAndAddsType(): void
    {
        $fieldsetUnion  = new Union([
            new TKeyedArray([
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $typeAlias      = new TTypeAlias(TestFieldset::class, 'TTestFieldsetArray');
        $formUnion      = new Union([
            new TKeyedArray([
                'foo' => new Union([$typeAlias]),
            ]),
        ]);
        $expectedImport = new ImportType($typeAlias, $fieldsetUnion);

        $form     = new Form();
        $fieldset = new TestFieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);

        $written = $this->fileWriter->written;
        self::assertCount(2, $written);
        $first  = $written[0];
        $second = $written[1];

        self::assertSame($fieldset::class, $first['reflection']->getName());
        self::assertEquals($fieldsetUnion, $first['type']);
        self::assertSame([], $first['imports']);
        self::assertFalse($first['remove-getdata']);

        self::assertSame($reflection, $second['reflection']);
        self::assertEquals($formUnion, $second['type']);
        self::assertEquals([TestFieldset::class => $expectedImport], $second['imports']);
        self::assertTrue($second['remove-getdata']);

        self::assertCount(2, $this->listener->success);
        self::assertEquals($fieldset::class, $this->listener->success[0]->getName());
        self::assertEquals($reflection, $this->listener->success[1]);
        self::assertSame(1, $this->listener->processed);
    }

    public function testProcessFieldsetProcessesCollection(): void
    {
        $fieldsetUnion = new Union([
            new TKeyedArray([
                'bar' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $typeAlias     = new TTypeAlias(TestFieldset::class, 'TTestFieldsetArray');
        $expected      = new ImportType($typeAlias, $fieldsetUnion);

        $form       = new Form();
        $collection = new Collection('foo');
        $fieldset   = new TestFieldset();
        $fieldset->add(new Text('bar'));
        $collection->setTargetElement($fieldset);
        $form->add($collection);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);

        $written = $this->fileWriter->written;
        self::assertCount(2, $written);

        $second = $written[1];
        self::assertEquals([TestFieldset::class => $expected], $second['imports']);
    }

    public function testProcessFieldsetSkipsLaminasFieldset(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([
                    new TKeyedArray([
                        'bar' => new Union([new TString(), new TNull()]),
                    ]),
                ]),
            ]),
        ]);

        $form     = new Form();
        $fieldset = new Fieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);

        self::assertCount(1, $this->fileWriter->written);
        $written = $this->fileWriter->written[0];
        self::assertEquals($expected, $written['type']);
        self::assertCount(0, $written['imports']);
    }

    public function testProcessFieldsetImportsChildFieldset(): void
    {
        $childUnion    = new Union([
            new TKeyedArray([
                'baz' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $typeAlias     = new TTypeAlias(ChildFieldset::class, 'TChildFieldsetArray');
        $fieldsetUnion = new Union([
            new TKeyedArray([
                'bar' => new Union([$typeAlias]),
            ]),
        ]);

        $form     = new Form();
        $fieldset = new TestFieldset('foo');
        $child    = new ChildFieldset('bar');
        $child->add(new Text('baz'));
        $fieldset->add($child);
        $form->add($fieldset);

        $expected = [
            ChildFieldset::class => new ImportType(
                $typeAlias,
                $childUnion
            ),
            TestFieldset::class  => new ImportType(
                new TTypeAlias(TestFieldset::class, 'TTestFieldsetArray'),
                $fieldsetUnion
            ),
        ];

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);

        self::assertCount(3, $this->fileWriter->written);
        $third = $this->fileWriter->written[2];
        self::assertEquals($expected, $third['imports']);
    }

    public function testProcessDoesNotProcessFieldset(): void
    {
        $form     = new Form();
        $fieldset = new TestFieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener, false);
        self::assertCount(1, $this->fileWriter->written);
    }

    public function testProcessDoesNotRemoveGetDataReturn(): void
    {
        $form = new Form();
        $form->add(new Text('bar'));

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener, true, false);
        self::assertCount(1, $this->fileWriter->written);
        $written = $this->fileWriter->written[0];
        self::assertFalse($written['remove-getdata']);
    }

    public function testProcessDoesNotWriteCustomisedForm(): void
    {
        $form = new CustomForm();
        $form->add(new Text('bar'));

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener, true, false);
        self::assertCount(0, $this->fileWriter->written);
    }

    public function testProcessDoesNotProcessIgnoredFieldset(): void
    {
        $form     = new Form();
        $fieldset = new IgnoredFieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);
        self::assertCount(1, $this->fileWriter->written);
    }

    public function testProcessUsesCustomType(): void
    {
        $expected = new Union([
            new TKeyedArray([
                'foo' => new Union([new TTypeAlias(CustomFieldset::class, 'TCustomType')]),
            ]),
        ]);
        $form     = new Form();
        $fieldset = new CustomFieldset('foo');
        $fieldset->add(new Text('bar'));
        $form->add($fieldset);

        $reflection = new ReflectionClass($form);
        $this->formLocator->method('locate')
            ->willReturn([new FormFile($reflection, $form)]);

        $this->processor->process(['foo'], $this->listener);
        self::assertCount(1, $this->fileWriter->written);
        $written = $this->fileWriter->written[0];
        self::assertEquals($expected, $written['type']);
    }
}

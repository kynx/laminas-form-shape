<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use KynxTest\Laminas\FormShape\ValinorAssertionTrait;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Text;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FormCollectionSmokeTest extends TestCase
{
    use ValinorAssertionTrait;

    private FormVisitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

        $inputVisitor           = new InputVisitor([], [new NotEmptyVisitor()]);
        $collectionInputVisitor = new CollectionInputVisitor($inputVisitor);
        $inputFilterVisitor     = new InputFilterVisitor([
            $collectionInputVisitor,
            $inputVisitor,
        ]);

        $this->visitor = new FormVisitor($inputFilterVisitor);
    }

    #[DataProvider('validationMatchesProvider')]
    public function testValidationMatches(ElementInterface $element, array $data, bool $isValid): void
    {
        $fieldset = new Fieldset('bar');
        $element->setName('baz');
        $fieldset->add($element);

        $collection = new Collection('foo');
        $collection->setTargetElement($fieldset);

        $form = new Form();
        $form->add($collection);

        $union = $this->visitor->visit($form, []);

        $type = (new PrettyPrinter())->decorate($union);

        $form->setData($data);
        $isFormValid = $form->isValid();
        /** @var array $formData */
        $formData = $form->getData();

        self::assertSame($isValid, $isFormValid, "Form::isValid() returned " . ($isValid ? 'false' : 'true'));
        self::assertValinorValidates($isValid, $type, $formData);
    }

    public static function validationMatchesProvider(): array
    {
        ConfigLoader::load();

        return [
            'not required, no data'          => [new Text(), [], true],
            'not required, empty collection' => [new Text(), ['foo' => []], true],
            'not required, empty fieldset'   => [new Text(), ['foo' => [[]]], true],
            'not required, empty data'       => [new Text(), ['foo' => [['baz' => '']]], true],
            'not required, data'             => [new Text(), ['foo' => [['baz' => 'abc']]], true],
            'required, no data'              => [new Email(), [], true],
            'required, empty collection'     => [new Email(), ['foo' => []], true],
            'required, empty fieldset'       => [new Email(), ['foo' => [[]]], false],
            'required, empty data'           => [new Email(), ['foo' => [['baz' => '']]], false],
            'required, data'                 => [new Email(), ['foo' => [['baz' => 'slarty@example.com']]], true],
        ];
    }

    /**
     * The only way to create a collection that _isn't_ `possibly_undefined` is to set `$allowRemove` to false. But this
     * throws an exception in `Collection::populateValues()`, so can't be handled by validation alone :\
     */
    #[DataProvider('countAllowRemoveProvider')]
    public function testCountAllowRemove(array $data, int $count, bool $allowRemove, bool $isValid): void
    {
        $element    = new Text('bar');
        $collection = new Collection('foo');
        $collection->setTargetElement($element);
        $collection->setCount($count);
        $collection->setAllowRemove($allowRemove);

        $form = new Form();
        $form->add($collection);

        $union = $this->visitor->visit($form, []);

        $type = (new PrettyPrinter())->decorate($union);
        self::assertValinorValidates($isValid, $type, $data);

        if ($isValid) {
            $form->setData($data);
            $isFormValid = $form->isValid();
            self::assertTrue($isFormValid);
            return;
        }

        self::expectException(DomainException::class);
        self::expectExceptionMessage('There are fewer elements than specified');
        $form->setData($data);
    }

    public static function countAllowRemoveProvider(): array
    {
        return [
            'empty, no count, allow remove'    => [['foo' => []], 0, true, true],
            'empty, no count, disallow remove' => [['foo' => []], 0, false, true],
            'count, allow remove'              => [['foo' => []], 42, true, true],
            'count, disallow remove, invalid'  => [['foo' => []], 1, false, false],
            'count, disallow remove, valid'    => [['foo' => ['abc']], 1, false, true],
        ];
    }

    public function testElementAsTargetElementValidates(): void
    {
        $expected = <<<EOT
        array{
            foo?: array<array-key, non-empty-string>,
        }
        EOT;
        $data     = ['foo' => ['slarty@example.com']];

        $collection = new Collection('foo');
        $collection->setTargetElement(new Email());
        $form = new Form();
        $form->add($collection);

        $union = $this->visitor->visit($form, []);

        $form->setData($data);
        $isValid = $form->isValid();
        self::assertTrue($isValid);

        $actual = (new PrettyPrinter())->decorate($union);
        self::assertValinorValidates(true, $actual, $data);

        self::assertSame($expected, $actual);
    }
}

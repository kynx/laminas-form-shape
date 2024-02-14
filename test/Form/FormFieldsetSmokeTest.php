<?php

declare(strict_types=1);

namespace Form;

use Kynx\Laminas\FormShape\Decorator\UnionDecorator;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use KynxTest\Laminas\FormShape\ValinorAssertionTrait;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Text;
use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FormFieldsetSmokeTest extends TestCase
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

    /**
     * @param array<ElementInterface> $elements
     */
    #[DataProvider('validationMatchProvider')]
    public function testValidationMatches(array $elements, array $data, bool $isValid): void
    {
        $fieldset = new Fieldset('foo');
        foreach ($elements as $element) {
            $fieldset->add($element);
        }
        $form = new Form();
        $form->add($fieldset);

        $union = $this->visitor->visit($form);
        $type  = (new UnionDecorator())->decorate($union);

        $form->setData($data);
        $formValid = $form->isValid();

        self::assertSame($isValid, $formValid, "Form::isValid() returned " . ($isValid ? 'false' : 'true'));
        self::assertValinorValidates($isValid, $type, $data);
    }

    public static function validationMatchProvider(): array
    {
        ConfigLoader::load();

        $nested = new Fieldset('bar');
        $nested->add(new Email('baz'));

        return [
            'not required, empty fieldset' => [[new Text('bar')], [], true],
            'not required, empty element'  => [[new Text('bar')], ['foo' => []], true],
            'not required, all data'       => [[new Text('bar')], ['foo' => ['bar' => 'abc']], true],
            'required, empty fieldset'     => [[new Email('bar')], [], false],
            'required, empty element'      => [[new Email('bar')], ['foo' => []], false],
            'required, all data'           => [[new Email('bar')], ['foo' => ['bar' => 'foo@example.com']], true],
            'mixed, empty fieldset'        => [[new Text('bar'), new Email('baz')], [], false],
            'nested, empty data'           => [[$nested], [], false],
            'nested, empty fieldset'       => [[$nested], ['foo' => []], false],
            'nested, empty child'          => [[$nested], ['foo' => ['bar' => []]], false],
            'nested, all data'             => [[$nested], ['foo' => ['bar' => ['baz' => 'foo@example.com']]], true],
        ];
    }
}

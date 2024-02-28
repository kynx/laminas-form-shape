<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Captcha;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Color;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Image;
use Laminas\Form\Element\Month;
use Laminas\Form\Element\MonthSelect;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Range;
use Laminas\Form\Element\Search;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Tel;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Time;
use Laminas\Form\Element\Url;
use Laminas\Form\Element\Week;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function is_array;
use function json_encode;
use function sprintf;

#[CoversNothing]
final class FormElementSmokeTest extends TestCase
{
    /**
     * @param class-string<ElementInterface> $element
     * @param list<list{array|scalar|null, bool}> $tests
     */
    #[DataProvider('defaultElementProvider')]
    public function testDefaultElements(string $element, array $tests, string $expected): void
    {
        $expectedString = <<<END_OF_EXPECTED
        array{
            $expected,
        }
        END_OF_EXPECTED;

        $form = new Form();
        $form->add([
            'name' => 'test',
            'type' => $element,
        ]);

        /** @var ContainerInterface $container */
        $container   = include __DIR__ . '/../container.php';
        $visitor     = $container->get(InputFilterVisitorInterface::class);
        $inputFilter = $form->getInputFilter();
        $union       = $visitor->visit($inputFilter, new ImportTypes());

        $decorator = new PrettyPrinter();
        /** @psalm-suppress PossiblyInvalidArgument */
        $actualString = $decorator->decorate($union);

        foreach ($tests as $expectation) {
            [$data, $valid] = $expectation;
            $formData       = is_array($data) ? $data : ['test' => $data];

            $inputFilter->setData($formData);
            $actual = $inputFilter->isValid();
            self::assertSame($valid, $actual, sprintf(
                "Validation expectation failed: %s is not %s",
                $data === null ? 'null' : json_encode($data),
                $valid ? 'valid' : 'invalid'
            ));
        }

        self::assertSame($expectedString, $actualString);
    }

    public static function defaultElementProvider(): array
    {
        ConfigLoader::load();

        return [
            'button'   => [
                Button::class,
                [[[], true], [null, true], ['', true], [' ', false], ['a', true]],
                'test: null|string',
            ],
            'captcha'  => [
                Captcha::class,
                [[[], false], [null, false], ['', false], [' ', false], ['a', true]],
                'test: non-empty-string',
            ],
            'checkbox' => [
                Checkbox::class,
                [[[], false], [null, false], ['', false], [' ', false], ['0', true], ['1', true]],
                "test: '0'|'1'",
            ],
            'color'    => [
                Color::class,
                [[[], false], [null, false], ['', false], [' ', false], ['a', false], ['#ffffff', true]],
                "test: non-empty-string",
            ],
//            'csrf' => [
//                Csrf::class,
//                [[null, false], ['', false], [' ', false], ['a', true]],
//                "test: non-empty-string"
//            ],
            'date'           => [
                Date::class,
                [[[], false], [null, false], ['', false], [' ', false], ['2024-01-28', true]],
                "test: non-empty-string",
            ],
            'dateselect'     => [
                DateSelect::class,
                [[[], true], [null, true], ['', true], [' ', false], ['2024-01-28', true]],
                "test: null|string",
            ],
            'datetimelocal'  => [
                DateTimeLocal::class,
                [[[], false], [null, false], ['', false], [' ', false], ['2024-01-28T12:53', true]],
                "test: non-empty-string",
            ],
            'datetimeselect' => [
                DateTimeLocal::class,
                [[[], false], [null, false], ['', false], [' ', false], ['2024-01-28T12:54', true]],
                "test: non-empty-string",
            ],
            'email'          => [
                Email::class,
                [[[], false], [null, false], ['', false], [' ', false], ['foo@example.com', true]],
                'test: non-empty-string',
            ],
//            'file' => [
//                File::class,
//                [[null, false], ['', false], [' ', false], ['/path/to/file', true]],
//                'test: non-empty-string'
//            ],
            'hidden'        => [
                Hidden::class,
                [[[], true], [null, true], ['', true], ['a', true]],
                'test: null|string',
            ],
            'image'         => [
                Image::class,
                [[[], true], [null, true], ['', true], ['a', true]],
                'test: null|string',
            ],
            'month'         => [
                Month::class,
                [[[], false], [null, false], ['', false], [' ', false], ['2024-01', true]],
                "test: non-empty-string",
            ],
            'monthselect'   => [
                MonthSelect::class,
                [[[], true], [null, true], ['', true], [' ', false], ['2024-01', true]],
                "test: null|string",
            ],
            'multicheckbox' => [
                MultiCheckbox::class,
                [[[], false], [null, false], ['', false], [' ', false]],
                "test: non-empty-string", // no haystack by default
            ],
            'number'        => [
                Number::class,
                [[[], false], [null, false], ['', false], [' ', false], ['a', false], ['123', true]],
                "test: numeric-string",
            ],
            'password'      => [
                Password::class,
                [[[], true], [null, true], ['', true], ['a', true]],
                'test: null|string',
            ],
            'radio'         => [
                Radio::class,
                [[[], false], [null, false], ['', false], [' ', false]],
                "test: non-empty-string", // no haystack by default
            ],
            'range'         => [
                Range::class,
                [[[], false], [null, false], ['', false], [' ', false], ['a', false], ['42', true]],
                "test: numeric-string",
            ],
            'search'        => [
                Search::class,
                [[[], true], [null, true], ['', true], ['a', true]],
                'test: null|string',
            ],
            'select'        => [
                Select::class,
                [[[], false], [null, false], ['', false], [' ', false]],
                "test: non-empty-string", // no haystack by default
            ],
            'submit'        => [
                Submit::class,
                [[[], true], [null, true], ['', true], ['a', true]],
                'test: null|string',
            ],
            'tel'           => [
                Tel::class,
                [[[], false], [null, false], ['', false], [' ', false], ['a', true]],
                'test: non-empty-string',
            ],
            'text'          => [
                Text::class,
                [[[], true], [null, true], ['', true], [' ', false], ['a', true]],
                'test: null|string',
            ],
            'time'          => [
                Time::class,
                [[[], false], [null, false], ['', false], [' ', false], ['10:25:00', true]],
                "test: non-empty-string",
            ],
            'url'           => [
                Url::class,
                [[[], false], [null, false], ['', false], [' ', false], ['http://example.com', true]],
                "test: non-empty-string",
            ],
            'week'          => [
                Week::class,
                [[[], false], [null, false], ['', false], [' ', false], ['2024-W05', true]],
                "test: non-empty-string",
            ],
        ];
    }

    public function testMultiCheckboxValidatesSingleString(): void
    {
        $form          = new Form();
        $multiCheckbox = new MultiCheckbox('foo', ['value_options' => [1 => 'a', 2 => 'b']]);
        $form->add($multiCheckbox);

        $form->setData(['foo' => '1']);
        $isValid = $form->isValid();
        self::assertTrue($isValid);
        $data = $form->getData();
        self::assertSame(['foo' => '1'], $data);

        $form->setData(['foo' => ['1', '2']]);
        $isValid = $form->isValid();
        self::assertTrue($isValid);
    }
}

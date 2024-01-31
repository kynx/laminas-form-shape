<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecorator;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
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

use function sprintf;

#[CoversNothing]
final class FormElementSmokeTest extends TestCase
{
    /**
     * @param class-string<ElementInterface> $element
     * @param list<list{scalar|null, bool}> $tests
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
        $shape       = $visitor->visit($inputFilter);

        $decorator = new InputFilterShapeDecorator();
        /** @psalm-suppress PossiblyInvalidArgument */
        $actualString = $decorator->decorate($shape);

        foreach ($tests as $expectation) {
            [$data, $valid] = $expectation;
            $inputFilter->setData(['test' => $data]);
            $actual = $inputFilter->isValid();
            self::assertSame($valid, $actual, sprintf(
                "Validation expectation failed: %s is not %s",
                $data === null ? 'null' : "'$data'",
                $valid ? 'valid' : 'invalid'
            ));
        }

        self::assertSame($expectedString, $actualString);
    }

    public static function defaultElementProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'button'   => [Button::class, [[null, true], ['', true], [' ', false], ['a', true]], 'test?: null|string'],
            'captcha'  => [Captcha::class, [[null, false], ['', false], [' ', false], ['a', true]], 'test: non-empty-string'],
            'checkbox' => [Checkbox::class, [[null, false], ['', false], [' ', false], ['0', true], ['1', true]], "test: '0'|'1'"],
            'color'    => [Color::class, [[null, false], ['', false], [' ', false], ['a', false], ['#ffffff', true]], "test: non-empty-string"],
//            'csrf' => [Csrf::class, [[null, false], ['', false], [' ', false], ['a', true]], "test: non-empty-string"],
            'date'           => [Date::class, [[null, false], ['', false], [' ', false], ['2024-01-28', true]], "test: non-empty-string"],
            'dateselect'     => [DateSelect::class, [[null, true], ['', true], [' ', false], ['2024-01-28', true]], "test?: null|string"],
            'datetimelocal'  => [DateTimeLocal::class, [[null, false], ['', false], [' ', false], ['2024-01-28T12:53', true]], "test: non-empty-string"],
            'datetimeselect' => [DateTimeLocal::class, [[null, false], ['', false], [' ', false], ['2024-01-28T12:54', true]], "test: non-empty-string"],
            'email'          => [Email::class, [[null, false], ['', false], [' ', false], ['foo@example.com', true]], 'test: non-empty-string'],
//            'file' => [File::class, [[null, false], ['', false], [' ', false], ['/path/to/file', true]], 'test: non-empty-string'],
            'hidden'        => [Hidden::class, [[null, true], ['', true], ['a', true]], 'test?: null|string'],
            'image'         => [Image::class, [[null, true], ['', true], ['a', true]], 'test?: null|string'],
            'month'         => [Month::class, [[null, false], ['', false], [' ', false], ['2024-01', true]], "test: non-empty-string"],
            'monthselect'   => [MonthSelect::class, [[null, true], ['', true], [' ', false], ['2024-01', true]], "test?: null|string"],
            'multicheckbox' => [MultiCheckbox::class, [[null, false], ['', false], [' ', false]], "test: non-empty-string"], // no haystack by default
            'number'        => [Number::class, [[null, false], ['', false], [' ', false], ['a', false], ['123', true]], "test: numeric-string"],
            'password'      => [Password::class, [[null, true], ['', true], ['a', true]], 'test?: null|string'],
            'radio'         => [Radio::class, [[null, false], ['', false], [' ', false]], "test: non-empty-string"], // no haystack by default
//            'range' => [Range::class, [[null, false], ['', false], [' ', false], ['a', false], ['1.23', true]], "test: numeric-string"],
            'search' => [Search::class, [[null, true], ['', true], ['a', true]], 'test?: null|string'],
            'select' => [Select::class, [[null, false], ['', false], [' ', false]], "test: non-empty-string"], // no haystack by default
            'submit' => [Submit::class, [[null, true], ['', true], ['a', true]], 'test?: null|string'],
            'tel'    => [Tel::class, [[null, false], ['', false], [' ', false], ['a', true]], 'test: non-empty-string'],
            'text'   => [Text::class, [[null, true], ['', true], [' ', false], ['a', true]], 'test?: null|string'],
            'time'   => [Time::class, [[null, false], ['', false], [' ', false]], "test: non-empty-string"], /*['10:25:33', true] default does not validate :( */
            'url'    => [Url::class, [[null, false], ['', false], [' ', false], ['http://example.com', true]], "test: non-empty-string"],
            'week'   => [Week::class, [[null, false], ['', false], [' ', false], ['2024-W05', true]], "test: non-empty-string"],
        ];
        // phpcs:enable
    }
}

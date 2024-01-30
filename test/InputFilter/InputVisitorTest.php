<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use Kynx\Laminas\FormShape\ArrayShapeException;
use Kynx\Laminas\FormShape\Decorator\ElementShapeDecorator;
use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Filter\ToFloatVisitor;
use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\Literal;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToFloat;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function sprintf;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(InputVisitor::class)]
final class InputVisitorTest extends TestCase
{
    public function testVisitCallsFilter(): void
    {
        $expected = new ElementShape('foo', [PsalmType::Null, PsalmType::String, PsalmType::Int]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new ToInt());
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitSkipsCallableFilters(): void
    {
        $expected = new ElementShape('foo', [PsalmType::Null, PsalmType::String]);
        $filter   = static fn (): never => self::fail("Should not be called");
        $input    = new Input('foo');
        $input->getFilterChain()->attach($filter);
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCallsValidator(): void
    {
        $expected = new ElementShape('foo', [PsalmType::NumericString]);
        $input    = new Input('foo');
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param VisitedArray $expected
     */
    #[DataProvider('addNotEmptyProvider')]
    public function testVisitAddsNotEmptyValidator(
        bool $continueIfEmpty,
        bool $allowEmpty,
        bool $required,
        array $expected
    ): void {
        $expected = new ElementShape('foo', $expected, ! $required);
        $input    = new Input('foo');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);
        $input->getFilterChain()->attach(new AllowList(['list' => ['bar']]));
        $visitor = new InputVisitor([new AllowListVisitor(false, 0)], [new NotEmptyVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public static function addNotEmptyProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            "continue, allow, required"                 => [true, true, true, [PsalmType::String, PsalmType::Null]],
            "continue, allow, not required"             => [true, true, false, [PsalmType::String, PsalmType::Null]],
            "continue, don't allow, required"           => [true, false, true, [PsalmType::String, PsalmType::Null]],
            "continue, don't allow, not required"       => [true, false, false, [PsalmType::String, PsalmType::Null]],
            "don't continue, allow, required"           => [false, true, true, [PsalmType::Null, PsalmType::String]],
            "don't continue, allow, not required"       => [false, true, false, [PsalmType::Null, PsalmType::String]],
            "don't continue, don't allow, required"     => [false, false, true, [PsalmType::NonEmptyString]],
            "don't continue, don't allow, not required" => [false, false, false, [PsalmType::Null, PsalmType::String]],
        ];
        // phpcs:enable
    }

    public function testVisitAllowEmptyReplacesNotEmptyString(): void
    {
        $expected         = new ElementShape('foo', [PsalmType::String, PsalmType::Null], true);
        $validatorVisitor = $this->createMock(ValidatorVisitorInterface::class);
        $validatorVisitor->expects(self::once())
            ->method('visit')
            ->willReturn([PsalmType::NonEmptyString]);
        $input = new Input('foo');
        $input->setRequired(false);
        $input->setAllowEmpty(true);
        $input->getValidatorChain()->attach($this->createStub(ValidatorInterface::class));
        $visitor = new InputVisitor([], [$validatorVisitor]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param VisitedArray $expected
     */
    #[DataProvider('addFallbackProvider')]
    public function testVisitAddsFallback(mixed $fallback, array $expected): void
    {
        $expected = new ElementShape('foo', $expected, true);
        $input    = new Input('foo');
        $input->setFallbackValue($fallback);
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public static function addFallbackProvider(): array
    {
        return [
            'string' => ['bar', [new Literal(["bar"])]],
            'int'    => [123, [new Literal([123])]],
            'true'   => [true, [PsalmType::True]],
            'false'  => [false, [PsalmType::False]],
            'float'  => [1.23, [PsalmType::Float]],
        ];
    }

    public function testVisitReturnsUniqueTypes(): void
    {
        $expected = new ElementShape('foo', [PsalmType::Null, PsalmType::String, PsalmType::Float], true);
        $input    = new Input('foo');
        $input->setFallbackValue(1.23);
        $input->getFilterChain()->attach(new ToFloat());
        $visitor = new InputVisitor([new ToFloatVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitEmptyTypesThrowsException(): void
    {
        $input = new Input('foo');
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        self::expectException(ArrayShapeException::class);
        self::expectExceptionMessage("Cannot get type for 'foo'");
        $visitor->visit($input);
    }

    #[DataProvider('correctTypeProvider')]
    public function testVisitReturnsCorrectType(
        bool $continueIfEmpty,
        bool $allowEmpty,
        bool $required,
        mixed $data,
        bool $valid,
        string $expectedShape
    ): void {
        $input = new Input('test');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);
        $visitor = new InputVisitor([], [new NotEmptyVisitor()]);

        $elementShape = $visitor->visit($input);

        $decorator   = new ElementShapeDecorator();
        $name        = $elementShape->optional ? "{$elementShape->name}?" : $elementShape->name;
        $actualShape = $name . ': ' . $decorator->decorate($elementShape);
        $arrayShape  = 'array{' . $actualShape . '}';
        $array       = $data === 'absent' ? [] : ['test' => $data];

        self::assertInputFilterValidates($input, $array, $valid);
        self::assertDataMatchesType($arrayShape, $array, $valid);
        self::assertSame($expectedShape, $actualShape);
    }

    public static function correctTypeProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            "continue, allow, required, null"                   => [true, true, true, null, true, 'test: null|string'],
            "continue, allow, required, string"                 => [true, true, true, 'a', true, 'test: null|string'],
            "continue, allow, required, empty"                  => [true, true, true, '', true, 'test: null|string'],
            "continue, allow, required, absent"                 => [true, true, true, 'absent', false, 'test: null|string'],
            "continue, allow, not required, null"               => [true, true, false, null, true, 'test?: null|string'],
            "continue, allow, not required, string"             => [true, true, false, 'a', true, 'test?: null|string'],
            "continue, allow, not required, empty"              => [true, true, false, '', true, 'test?: null|string'],
            "continue, allow, not required, absent"             => [true, true, false, 'absent', true, 'test?: null|string'],
            "continue, don't allow, required, null"             => [true, false, true, null, true, 'test: null|string'],
            "continue, don't allow, required, string"           => [true, false, true, 'a', true, 'test: null|string'],
            "continue, don't allow, required, empty"            => [true, false, true, '', true, 'test: null|string'],
            "continue, don't allow, required, absent"           => [true, false, true, 'absent', false, 'test: null|string'],
            "continue, don't allow, not required, null"         => [true, false, false, null, true, 'test?: null|string'],
            "continue, don't allow, not required, string"       => [true, false, false, 'a', true, 'test?: null|string'],
            "continue, don't allow, not required, empty"        => [true, false, false, '', true, 'test?: null|string'],
            "continue, don't allow, not required, absent"       => [true, false, false, 'absent', true, 'test?: null|string'],
            "don't continue, allow, required, null"             => [false, true, true, null, true, 'test: null|string'],
            "don't continue, allow, required, string"           => [false, true, true, 'a', true, 'test: null|string'],
            "don't continue, allow, required, empty"            => [false, true, true, '', true, 'test: null|string'],
            "don't continue, allow, required, absent"           => [false, true, true, 'absent', false, 'test: null|string'],
            "don't continue, allow, not required, null"         => [false, true, false, null, true, 'test?: null|string'],
            "don't continue, allow, not required, string"       => [false, true, false, 'a', true, 'test?: null|string'],
            "don't continue, allow, not required, empty"        => [false, true, false, '', true, 'test?: null|string'],
            "don't continue, allow, not required, absent"       => [false, true, false, 'absent', true, 'test?: null|string'],
            "don't continue, don't allow, required, null"       => [false, false, true, null, false, 'test: non-empty-string'],
            "don't continue, don't allow, required, string"     => [false, false, true, 'a', true, 'test: non-empty-string'],
            "don't continue, don't allow, required, empty"      => [false, false, true, '', false, 'test: non-empty-string'],
            "don't continue, don't allow, required, absent"     => [false, false, true, 'absent', false, 'test: non-empty-string'],
            "don't continue, don't allow, not required, null"   => [false, false, false, null, true, 'test?: null|string'],
            "don't continue, don't allow, not required, string" => [false, false, false, 'a', true, 'test?: null|string'],
            "don't continue, don't allow, not required, empty"  => [false, false, false, '', true, 'test?: null|string'],
            "don't continue, don't allow, not required, absent" => [false, false, false, 'absent', true, 'test?: null|string'],
        ];
        // phpcs:enable
    }

    private function assertInputFilterValidates(Input $input, array $data, bool $expected): void
    {
        $inputFilter = new InputFilter();
        $inputFilter->add($input);
        $inputFilter->setData($data);
        $actual   = $inputFilter->isValid();
        $messages = $inputFilter->getMessages();

        self::assertSame($expected, $actual, sprintf(
            "Input filter returned %s for '%s' %s",
            $expected ? 'false' : 'true',
            json_encode($data),
            $messages === [] ? '' : json_encode($messages)
        ));
    }

    private function assertDataMatchesType(string $type, array $data, bool $valid): void
    {
        try {
            /** @var array{test: mixed} $array */
            $array = (new MapperBuilder())->mapper()->map($type, $data);
            self::assertTrue($valid, sprintf(
                "Invalid data '%s' should not match type '%s'",
                json_encode($data),
                $type
            ));
            self::assertSame($data, $array);
        } catch (MappingError $e) {
            self::assertFalse($valid, $e->getMessage());
        }
    }
}

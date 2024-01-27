<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToFloatVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\NotEmptyVisitor;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToFloat;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(InputVisitor::class)]
final class InputVisitorTest extends TestCase
{
    public function testGetInputTypeParsesFilter(): void
    {
        $expected = new InputType('foo', [PsalmType::String, PsalmType::Int]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new ToInt());
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    public function testGetInputTypeSkipsCallableFilters(): void
    {
        $expected = new InputType('foo', [PsalmType::String]);
        $filter   = static fn (): never => self::fail("Should not be called");
        $input    = new Input('foo');
        $input->getFilterChain()->attach($filter);
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    public function testGetInputTypeParsesValidator(): void
    {
        $expected = new InputType('foo', [PsalmType::NumericString]);
        $input    = new Input('foo');
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([], [new DigitsVisitor()]);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    public function testGetInputTypeAddsNotEmptyValidator(): void
    {
        $expected = new InputType('foo', [PsalmType::NonEmptyString], false);
        $input    = new Input('foo');
        $input->setContinueIfEmpty(false);
        $input->getFilterChain()->attach(new AllowList(['list' => ['bar']]));
        $visitor = new InputVisitor([new AllowListVisitor(false, 0)], [new NotEmptyVisitor()]);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param VisitedArray $expected
     */
    #[DataProvider('addNullProvider')]
    public function testGetInputTypeAddsNull(
        bool $continueIfEmpty,
        bool $allowEmpty,
        bool $required,
        array $expected
    ): void {
        $expected = new InputType('foo', $expected, ! $required);
        $input    = new Input('foo');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);
        $visitor = new InputVisitor([], []);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    public static function addNullProvider(): array
    {
        return [
            "don't continue, allow empty"  => [false, true, true, [PsalmType::String, PsalmType::Null]],
            "don't continue, not required" => [false, false, false, [PsalmType::String, PsalmType::Null]],
            "continue"                     => [true, true, false, [PsalmType::String]],
        ];
    }

    /**
     * @param VisitedArray $expected
     */
    #[DataProvider('addFallbackProvider')]
    public function testGetInputTypeAddsFallback(mixed $fallback, array $expected): void
    {
        $expected = new InputType('foo', $expected, true);
        $input    = new Input('foo');
        $input->setFallbackValue($fallback);
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->getInputType($input);
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

    public function testGetInputTypeReturnsUniqueTypes(): void
    {
        $expected = new InputType('foo', [PsalmType::String, PsalmType::Float], true);
        $input    = new Input('foo');
        $input->setFallbackValue(1.23);
        $input->getFilterChain()->attach(new ToFloat());
        $visitor = new InputVisitor([new ToFloatVisitor()], []);

        $actual = $visitor->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    public function testGetInputTypeEmptyTypesThrowsException(): void
    {
        $input = new Input('foo');
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        self::expectException(ArrayShapeException::class);
        self::expectExceptionMessage("Cannot parse type for 'foo'");
        $visitor->getInputType($input);
    }
}

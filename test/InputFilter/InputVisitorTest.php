<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(InputVisitor::class)]
final class InputVisitorTest extends TestCase
{
    public function testVisitCallsFilter(): void
    {
        $expected = new Union([new TNull(), new TString(), new TInt()]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new ToInt());
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitSkipsCallableFilters(): void
    {
        $expected = new Union([new TNull(), new TString()]);
        $filter   = static fn (): never => self::fail("Should not be called");
        $input    = new Input('foo');
        $input->getFilterChain()->attach($filter);
        $visitor = new InputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCallsValidator(): void
    {
        $expected = new Union([new TNumericString()]);
        $input    = new Input('foo');
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    /**
     * @param non-empty-array<Atomic> $expected
     */
    #[DataProvider('addNotEmptyProvider')]
    public function testVisitAddsNotEmptyValidator(
        bool $continueIfEmpty,
        bool $allowEmpty,
        bool $required,
        array $expected
    ): void {
        $expected = new Union($expected, ['possibly_undefined' => ! $required]);
        $input    = new Input('foo');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);
        $visitor = new InputVisitor([], [new NotEmptyVisitor()]);

        $actual = $visitor->visit($input);

        self::assertEquals($expected, $actual);
    }

    public static function addNotEmptyProvider(): array
    {
        ConfigLoader::load();

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            "continue, allow, required"                 => [true, true, true, [new TString(), new TNull()]],
            "continue, allow, not required"             => [true, true, false, [new TString(), new TNull()]],
            "continue, don't allow, required"           => [true, false, true, [new TString(), new TNull()]],
            "continue, don't allow, not required"       => [true, false, false, [new TString(), new TNull()]],
            "don't continue, allow, required"           => [false, true, true, [new TNull(), new TString()]],
            "don't continue, allow, not required"       => [false, true, false, [new TNull(), new TString()]],
            "don't continue, don't allow, required"     => [false, false, true, [new TNonEmptyString()]],
            "don't continue, don't allow, not required" => [false, false, false, [new TNull(), new TString()]],
        ];
        // phpcs:enable
    }

    public function testVisitAllowEmptyReplacesNotEmptyString(): void
    {
        $expected         = new Union([new TString(), new TNull()], ['possibly_undefined' => true]);
        $validatorVisitor = $this->createMock(ValidatorVisitorInterface::class);
        $validatorVisitor->expects(self::once())
            ->method('visit')
            ->willReturn(new Union([new TNonEmptyString()]));
        $input = new Input('foo');
        $input->setRequired(false);
        $input->setAllowEmpty(true);
        $input->getValidatorChain()->attach($this->createStub(ValidatorInterface::class));
        $visitor = new InputVisitor([], [$validatorVisitor]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitAddsFallback(): void
    {
        $expected = new Union([new TLiteralFloat(1.23)], ['possibly_undefined' => true]);
        $input    = new Input('foo');
        $input->setFallbackValue(1.23);
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        $actual = $visitor->visit($input);

        self::assertEquals($expected, $actual);
    }

    public function testVisitEmptyTypesThrowsException(): void
    {
        $input = new Input('foo');
        $input->getFilterChain()->attach(new Boolean());
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new InputVisitor([new BooleanVisitor()], [new DigitsVisitor()]);

        self::expectException(InputVisitorException::class);
        self::expectExceptionMessage("Cannot get type for 'foo'");
        $visitor->visit($input);
    }
}

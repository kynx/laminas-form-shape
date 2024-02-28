<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\AbstractInputVisitor;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use KynxTest\Laminas\FormShape\InputFilter\MockAbstractInputVisitor;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(AbstractInputVisitor::class)]
final class AbstractInputVisitorTest extends TestCase
{
    public function testVisitCallsFilter(): void
    {
        $expected = new Union([new TNull(), new TString(), new TInt()]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new ToInt());
        $visitor = new MockAbstractInputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitSkipsCallableFilters(): void
    {
        $expected = new Union([new TNull(), new TString()]);
        $filter   = static fn (): never => self::fail("Should not be called");
        $input    = new Input('foo');
        $input->getFilterChain()->attach($filter);
        $visitor = new MockAbstractInputVisitor([new ToIntVisitor()], []);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testVisitCallsValidator(): void
    {
        $expected = new Union([new TNumericString()]);
        $input    = new Input('foo');
        $input->getValidatorChain()->attach(new Digits());
        $visitor = new MockAbstractInputVisitor([], [new DigitsVisitor()]);

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
        $expected = new Union($expected);
        $input    = new Input('foo');
        $input->setContinueIfEmpty($continueIfEmpty);
        $input->setAllowEmpty($allowEmpty);
        $input->setRequired($required);
        $visitor = new MockAbstractInputVisitor([], [new NotEmptyVisitor()]);

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

    public function testVisitInputDoesNotPrependDuplicateNotEmptyVisitor(): void
    {
        ConfigLoader::load();

        $input = new Input('foo');
        $input->setContinueIfEmpty(false);
        $input->setAllowEmpty(false);
        $input->setRequired(true);
        $input->getValidatorChain()->attach(new NotEmpty());

        $mockVisitor = self::createMock(ValidatorVisitorInterface::class);
        $mockVisitor->method('visit')
            ->willReturnCallback(static function (ValidatorInterface $validator, Union $previous) {
                self::assertNotEquals(new Union([new TNonEmptyString()]), $previous);
                return $previous;
            });

        $visitor = new MockAbstractInputVisitor([], [$mockVisitor, new NotEmptyVisitor()]);
        $actual  = $visitor->visit($input);
        self::assertNotNull($actual);
        self::assertEquals(new TNonEmptyString(), $actual->getSingleAtomic());
    }

    public function testVisitAllowEmptyReplacesNotEmptyString(): void
    {
        $expected         = new Union([new TString(), new TNull()]);
        $validatorVisitor = $this->createMock(ValidatorVisitorInterface::class);
        $validatorVisitor->expects(self::once())
            ->method('visit')
            ->willReturn(new Union([new TNonEmptyString()]));
        $input = new Input('foo');
        $input->setRequired(false);
        $input->setAllowEmpty(true);
        $input->getValidatorChain()->attach($this->createStub(ValidatorInterface::class));
        $visitor = new MockAbstractInputVisitor([], [$validatorVisitor]);

        $actual = $visitor->visit($input);
        self::assertEquals($expected, $actual);
    }
}

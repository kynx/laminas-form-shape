<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\CollectionInput;
use Laminas\Filter\FilterChain;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectionInput::class)]
final class CollectionInputTest extends TestCase
{
    public function testFromInputSetsProperties(): void
    {
        $actual = CollectionInput::fromInput(new Input(), 42);
        self::assertSame(42, $actual->getCount());
    }

    public function testFromInputDelegatesProperties(): void
    {
        $input = new Input();
        $input->setName('foo')
            ->setRequired(true)
            ->setAllowEmpty(true)
            ->setContinueIfEmpty(true)
            ->setBreakOnFailure(true)
            ->setFilterChain((new FilterChain())->attach(new ToInt()))
            ->setValidatorChain((new ValidatorChain())->attach(new NotEmpty()));

        $actual = CollectionInput::fromInput($input, 0);

        self::assertSame($input->getName(), $actual->getName());
        self::assertSame($input->isRequired(), $actual->isRequired());
        self::assertSame($input->allowEmpty(), $actual->allowEmpty());
        self::assertSame($input->continueIfEmpty(), $actual->continueIfEmpty());
        self::assertSame($input->breakOnFailure(), $actual->breakOnFailure());
        self::assertSame($input->getFilterChain(), $actual->getFilterChain());
        self::assertSame($input->getValidatorChain(), $actual->getValidatorChain());
    }

    public function testIsValidDelegates(): void
    {
        $input = CollectionInput::fromInput((new Input())->setRequired(true), 0);
        $input->setValue([]);

        $actual = $input->isValid();
        self::assertFalse($actual);
    }
}

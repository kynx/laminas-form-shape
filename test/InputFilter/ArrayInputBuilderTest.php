<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputBuilder;
use Laminas\Filter\AllowList;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use Laminas\Validator\Between;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayInputBuilder::class)]
final class ArrayInputBuilderTest extends TestCase
{
    public function testCreateReturnsArrayInput(): void
    {
        $expected = new ArrayInput('foo');
        $expected->setRequired(false);
        $expected->setAllowEmpty(true);
        $expected->setContinueIfEmpty(true);
        $expected->setFallbackValue(['456']);
        $expected->setValue(['123']);
        $expected->getFilterChain()->attach(new AllowList());
        $expected->getValidatorChain()->attach(new Between(['min' => 1, 'max' => 10]));

        $input = new Input('foo');
        $input->setRequired($expected->isRequired());
        $input->setAllowEmpty($expected->allowEmpty());
        $input->setContinueIfEmpty($expected->continueIfEmpty());
        $input->setFallbackValue($expected->getFallbackValue());
        $input->setValue('123');
        $input->setFilterChain($expected->getFilterChain());
        $input->setValidatorChain($expected->getValidatorChain());

        $actual = ArrayInputBuilder::create($input);
        self::assertEquals($expected, $actual);
    }
}

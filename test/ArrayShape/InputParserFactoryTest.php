<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParser;
use Kynx\Laminas\FormCli\ArrayShape\InputParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser;
use Laminas\Filter\AllowList;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\InputParserFactory
 */
final class InputParserFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([AllowListParser::class], [DigitsParser::class]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InputParserFactory();
        $instance = $factory($container);

        $expected = new InputType('foo', [PsalmType::NumericString]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new AllowList(['list' => [1.23], 'strict' => false]));
        $input->getValidatorChain()->attach(new Digits());

        $actual = $instance->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $filterParsers, array $validatorParsers): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'filter-parsers'    => $filterParsers,
                    'validator-parsers' => $validatorParsers,
                ],
            ],
        ];
    }
}

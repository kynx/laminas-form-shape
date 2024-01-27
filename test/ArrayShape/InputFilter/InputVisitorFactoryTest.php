<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsVisitor;
use Laminas\Filter\AllowList;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(InputVisitorFactory::class)]
final class InputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([AllowListVisitor::class], [DigitsVisitor::class]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InputVisitorFactory();
        $instance = $factory($container);

        $expected = new InputType('foo', [PsalmType::NumericString]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new AllowList(['list' => [1.23], 'strict' => false]));
        $input->getValidatorChain()->attach(new Digits());

        $actual = $instance->getInputType($input);
        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $filterVisitors, array $validatorVisitors): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'filter-visitors'    => $filterVisitors,
                    'validator-visitors' => $validatorVisitors,
                ],
            ],
        ];
    }
}

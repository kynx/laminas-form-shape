<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorFactory;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\AllowList;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(InputVisitorFactory::class)]
final class InputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([AllowListVisitor::class], [DigitsVisitor::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InputVisitorFactory();
        $instance = $factory($container);

        $expected = new ElementShape('foo', [PsalmType::NumericString]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new AllowList(['list' => [1.23], 'strict' => false]));
        $input->getValidatorChain()->attach(new Digits());

        $actual = $instance->visit($input);
        self::assertEquals($expected, $actual);
    }

    public function testInvokeGetsVisitorFromContainer(): void
    {
        $config           = $this->getConfig([], [ValidatorVisitorInterface::class]);
        $validatorVisitor = $this->createMock(ValidatorVisitorInterface::class);
        $container        = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [ValidatorVisitorInterface::class, $validatorVisitor],
            ]);

        $factory  = new InputVisitorFactory();
        $instance = $factory($container);

        $expected = new ElementShape('foo', [PsalmType::Int, PsalmType::Null], true);
        $input    = new Input('foo');
        $input->setRequired(false);
        $input->getValidatorChain()->attach($this->createStub(ValidatorInterface::class));

        $validatorVisitor->expects(self::once())
            ->method('visit')
            ->willReturn([PsalmType::Int]);
        $actual = $instance->visit($input);
        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $filterVisitors, array $validatorVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'filter-visitors'    => $filterVisitors,
                'validator-visitors' => $validatorVisitors,
            ],
        ];
    }
}

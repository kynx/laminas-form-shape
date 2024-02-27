<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorFactory;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(InputVisitorFactory::class)]
final class InputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([ToIntVisitor::class], [DigitsVisitor::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InputVisitorFactory();
        $instance = $factory($container);

        $expected = new Union([new TInt(), new TNumericString()]);
        $input    = new Input('foo');
        $input->getFilterChain()->attach(new ToInt());
        $input->getValidatorChain()->attach(new Digits());

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

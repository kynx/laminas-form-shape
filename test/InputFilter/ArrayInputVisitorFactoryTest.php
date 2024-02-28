<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitorFactory;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\ArrayInput;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psr\Container\ContainerInterface;

#[CoversClass(ArrayInputVisitorFactory::class)]
final class ArrayInputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig([ToIntVisitor::class], [DigitsVisitor::class])],
                [ToIntVisitor::class, new ToIntVisitor()],
                [DigitsVisitor::class, new DigitsVisitor()],
            ]);

        $factory  = new ArrayInputVisitorFactory();
        $instance = $factory($container);
        $input    = new ArrayInput();
        $input->getFilterChain()->attach(new ToInt());
        $input->getValidatorChain()->attach(new Digits());

        $actual = $instance->visit(new ArrayInput());
        self::assertNotNull($actual);
        self::assertInstanceOf(TNonEmptyArray::class, $actual->getSingleAtomic());
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

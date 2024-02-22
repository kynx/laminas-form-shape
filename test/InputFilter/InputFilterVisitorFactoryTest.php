<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(InputFilterVisitorFactory::class)]
final class InputFilterVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([InputVisitor::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [InputVisitor::class, new InputVisitor([], [])],
            ]);

        $factory  = new InputFilterVisitorFactory();
        $instance = $factory($container);

        $expected    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TString(), new TNull()]),
            ]),
        ]);
        $inputFilter = new InputFilter();
        $inputFilter->add(new Input('foo'));

        $actual = $instance->visit($inputFilter, new ImportTypes([]));
        self::assertEquals($expected, $actual);
    }

    public function testInvokeSortsInputVisitors(): void
    {
        $config       = $this->getConfig([InputVisitor::class, ArrayInputVisitor::class]);
        $container    = self::createStub(ContainerInterface::class);
        $inputVisitor = new InputVisitor([], []);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [InputVisitor::class, new InputVisitor([], [])],
                [ArrayInputVisitor::class, new ArrayInputVisitor($inputVisitor)],
            ]);

        $factory  = new InputFilterVisitorFactory();
        $instance = $factory($container);
        $filter   = new InputFilter();
        $filter->add(new ArrayInput(), 'foo');

        $keyedArray = $instance->visit($filter, new ImportTypes([]))->getSingleAtomic();
        self::assertInstanceOf(TKeyedArray::class, $keyedArray);
        $property = $keyedArray->properties['foo'] ?? null;
        self::assertInstanceOf(Union::class, $property);

        $actual = $property->getSingleAtomic();
        self::assertInstanceOf(TArray::class, $actual);
    }

    private function getConfig(array $inputVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'input-visitors' => $inputVisitors,
            ],
        ];
    }
}

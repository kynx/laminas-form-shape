<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\InputFilter\ArrayInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(ArrayInputVisitorFactory::class)]
final class ArrayInputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [InputVisitor::class, new InputVisitor([], [])],
            ]);

        $factory  = new ArrayInputVisitorFactory();
        $instance = $factory($container);

        $actual = $instance->visit(new ArrayInput());
        self::assertInstanceOf(Union::class, $actual);
    }
}

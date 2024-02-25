<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\CollectionInput;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TArray;
use Psr\Container\ContainerInterface;

#[CoversClass(CollectionInputVisitorFactory::class)]
final class CollectionInputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                [InputVisitor::class, new InputVisitor([], [])],
            ]);

        $factory  = new CollectionInputVisitorFactory();
        $instance = $factory($container);
        $input    = CollectionInput::fromInput(new Input(), 0);

        $union = $instance->visit($input);
        self::assertNotNull($union);
        $actual = $union->getSingleAtomic();
        self::assertInstanceOf(TArray::class, $actual);
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\ArrayShapeDecoratorFactory;
use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(ArrayShapeDecoratorFactory::class)]
final class ArrayShapeDecoratorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig("\t");
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new ArrayShapeDecoratorFactory();
        $instance = $factory($container);

        $expected = "array{\n\tfoo: int,\n}";
        $shape    = new ArrayShape('', [new ElementShape('foo', [PsalmType::Int])]);
        $actual   = $instance->decorate($shape);
        self::assertSame($expected, $actual);
    }

    private function getConfig(string $indent): array
    {
        return [
            'laminas-form-shape' => [
                'indent' => $indent,
            ],
        ];
    }
}

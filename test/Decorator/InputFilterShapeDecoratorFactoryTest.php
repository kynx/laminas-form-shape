<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecoratorFactory;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(InputFilterShapeDecoratorFactory::class)]
final class InputFilterShapeDecoratorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig("\t");
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InputFilterShapeDecoratorFactory();
        $instance = $factory($container);

        $expected = "array{\n\tfoo: int,\n}";
        $shape    = new InputFilterShape('', [new InputShape('foo', [PsalmType::Int])]);
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

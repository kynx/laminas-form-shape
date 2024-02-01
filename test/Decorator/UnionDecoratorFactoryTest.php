<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\UnionDecoratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(UnionDecoratorFactory::class)]
final class UnionDecoratorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig("\t");
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new UnionDecoratorFactory();
        $instance = $factory($container);

        $expected = "array{\n\tfoo: int,\n}";
        $union    = new Union([
            new TKeyedArray([
                'foo' => new Union([new TInt()]),
            ]),
        ]);

        $actual = $instance->decorate($union);
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

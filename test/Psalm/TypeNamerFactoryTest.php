<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\TypeNamerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

#[CoversClass(TypeNamerFactory::class)]
final class TypeNamerFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig('T{shortName}Foo');
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new TypeNamerFactory();
        $instance = $factory($container);

        $expected = 'TTypeNamerFactoryTestFoo';
        $actual   = $instance->name(new ReflectionClass($this));
        self::assertSame($expected, $actual);
    }

    private function getConfig(string $typeNameTemplate): array
    {
        return [
            'laminas-form-shape' => [
                'type-name-template' => $typeNameTemplate,
            ],
        ];
    }
}

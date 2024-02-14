<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Decorator\PrettyPrinterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

use function array_merge;

#[CoversClass(PrettyPrinterFactory::class)]
final class PrettyPrinterFactoryTest extends TestCase
{
    public function testInvokeReturnsInstanceWithIndent(): void
    {
        $config    = $this->getConfig(['indent' => "\t"]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new PrettyPrinterFactory();
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

    public function testInvokeReturnsInstanceConfiguredWithLiteralLimit(): void
    {
        $config    = $this->getConfig(['literal-limit' => 1]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new PrettyPrinterFactory();
        $instance = $factory($container);

        $expected = "int<0, max>"; // hrm... psalm could narrow this better ;)
        $union    = new Union([new TLiteralInt(1), new TLiteralInt(2)]);

        $actual = $instance->decorate($union);
        self::assertSame($expected, $actual);
    }

    private function getConfig(array $config): array
    {
        $config = array_merge(['indent' => '    ', 'literal-limit' => null], $config);
        return [
            'laminas-form-shape' => $config,
        ];
    }
}

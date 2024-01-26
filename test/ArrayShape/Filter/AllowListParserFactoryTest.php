<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(AllowListParserFactory::class)]
final class AllowListParserFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new AllowListParserFactory();
        $instance = $factory($container);

        $expected = [PsalmType::Null, new Literal(["a"])];
        $filter   = new AllowList(['list' => ['a'], 'strict' => true]);
        $actual   = $instance->getTypes($filter, [PsalmType::String]);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeConfiguresAllowEmptyList(): void
    {
        $config    = $this->getConfig(['allow-empty-list' => false]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new AllowListParserFactory();
        $instance = $factory($container);

        $expected = [PsalmType::Null];
        $filter   = new AllowList(['list' => []]);
        $actual   = $instance->getTypes($filter, [PsalmType::String]);

        self::assertSame($expected, $actual);
    }

    public function testInvokeConfiguresMaxLiteral(): void
    {
        $config    = $this->getConfig(['max-literals' => 0]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new AllowListParserFactory();
        $instance = $factory($container);

        $expected = [PsalmType::String, PsalmType::Null];
        $filter   = new AllowList(['list' => ['a']]);
        $actual   = $instance->getTypes($filter, [PsalmType::String]);

        self::assertSame($expected, $actual);
    }

    private function getConfig(array $config): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'filter' => [
                        'allow-list' => $config,
                    ],
                ],
            ],
        ];
    }
}

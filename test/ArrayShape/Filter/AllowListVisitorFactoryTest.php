<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(AllowListVisitorFactory::class)]
final class AllowListVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new AllowListVisitorFactory();
        $instance = $factory($container);

        $expected = [PsalmType::Null, new Literal(["a"])];
        $filter   = new AllowList(['list' => ['a'], 'strict' => true]);
        $actual   = $instance->visit($filter, [PsalmType::String]);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeConfiguresAllowEmptyList(): void
    {
        $config    = $this->getConfig(['allow-empty-list' => false]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new AllowListVisitorFactory();
        $instance = $factory($container);

        $expected = [PsalmType::Null];
        $filter   = new AllowList(['list' => []]);
        $actual   = $instance->visit($filter, [PsalmType::String]);

        self::assertSame($expected, $actual);
    }

    public function testInvokeConfiguresMaxLiteral(): void
    {
        $config    = $this->getConfig(['max-literals' => 0]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new AllowListVisitorFactory();
        $instance = $factory($container);

        $expected = [PsalmType::String, PsalmType::Null];
        $filter   = new AllowList(['list' => ['a']]);
        $actual   = $instance->visit($filter, [PsalmType::String]);

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
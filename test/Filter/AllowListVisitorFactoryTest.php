<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\AllowListVisitorFactory;
use Laminas\Filter\AllowList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
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

        $expected = new Union([new TString(), new TNull()]);
        $filter   = new AllowList(['list' => []]);
        $actual   = $instance->visit($filter, new Union([new TString()]));

        self::assertEquals($expected, $actual);
    }

    public function testInvokeConfiguresDisallowsEmptyList(): void
    {
        $config    = $this->getConfig(['allow-empty-list' => false]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new AllowListVisitorFactory();
        $instance = $factory($container);

        $expected = new Union([new TNull()]);
        $filter   = new AllowList(['list' => []]);
        $actual   = $instance->visit($filter, new Union([new TString()]));

        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $config): array
    {
        return [
            'laminas-form-shape' => [
                'filter' => [
                    'allow-list' => $config,
                ],
            ],
        ];
    }
}

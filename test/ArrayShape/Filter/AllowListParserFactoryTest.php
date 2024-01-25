<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParserFactory
 */
final class AllowListParserFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new AllowListParserFactory();
        $instance = $factory($container);

        $expected = [new Literal(["'a'"]), PsalmType::Null];
        $filter   = new AllowList(['list' => ['a']]);
        $actual   = $instance->getTypes($filter, [PsalmType::String]);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = [
            'laminas-form-cli' => [
                'array-shape' => [
                    'filter' => [
                        'allow-list' => [
                            'max-literals' => 0,
                        ],
                    ],
                ],
            ],
        ];
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
}

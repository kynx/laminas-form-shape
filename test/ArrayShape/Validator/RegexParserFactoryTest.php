<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexParserFactory;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(RegexParserFactory::class)]
final class RegexParserFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator' => [
                        'regex' => [
                            'patterns' => [
                                [
                                    'pattern' => '(^-?\d*(\.\d+)?$)',
                                    'types'   => [PsalmType::Int, PsalmType::Float],
                                    'replace' => [[PsalmType::String, PsalmType::NumericString]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new RegexParserFactory();
        $instance = $factory($container);

        $validator = new Regex('(^-?\d*(\.\d+)?$)');
        $actual    = $instance->getTypes($validator, [PsalmType::String]);
        self::assertSame([PsalmType::NumericString], $actual);
    }
}

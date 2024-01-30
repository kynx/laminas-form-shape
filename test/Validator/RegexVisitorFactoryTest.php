<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\RegexVisitorFactory;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(RegexVisitorFactory::class)]
final class RegexVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = [
            'laminas-form-shape' => [
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
        ];
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new RegexVisitorFactory();
        $instance = $factory($container);

        $validator = new Regex('(^-?\d*(\.\d+)?$)');
        $actual    = $instance->visit($validator, [PsalmType::String]);
        self::assertSame([PsalmType::NumericString], $actual);
    }
}

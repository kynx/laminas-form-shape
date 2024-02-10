<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\InvalidValidatorConfigurationException;
use Kynx\Laminas\FormShape\Validator\RegexVisitorFactory;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(RegexVisitorFactory::class)]
final class RegexVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig(['(^-?\d*(\.\d+)?$)' => [TNumericString::class]]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new RegexVisitorFactory();
        $instance = $factory($container);

        $expected  = new Union([new TNumericString()]);
        $validator = new Regex('(^-?\d*(\.\d+)?$)');
        $actual    = $instance->visit($validator, new Union([new TString()]));
        self::assertEquals($expected, $actual);
    }

    public function testInvokeInvalidConfigurationThrowsException(): void
    {
        $config    = $this->getConfig(['(^-?\d*(\.\d+)?$)' => [self::class]]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory = new RegexVisitorFactory();
        self::expectException(InvalidValidatorConfigurationException::class);
        $factory($container);
    }

    private function getConfig(array $patterns): array
    {
        return [
            'laminas-form-shape' => [
                'validator' => [
                    'regex' => [
                        'patterns' => $patterns,
                    ],
                ],
            ],
        ];
    }
}

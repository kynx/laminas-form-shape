<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorVisitorFactory;
use Laminas\Validator\Barcode;
use Laminas\Validator\BusinessIdentifierCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(StringValidatorVisitorFactory::class)]
final class StringValidatorVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new StringValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Barcode();
        $actual    = $instance->getTypes($validator, [PsalmType::String]);
        self::assertSame($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([Barcode::class]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new StringValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Barcode();
        $actual    = $instance->getTypes($validator, [PsalmType::String]);
        self::assertSame($expected, $actual);

        $expected  = [PsalmType::Bool];
        $validator = new BusinessIdentifierCode();
        $actual    = $instance->getTypes($validator, $expected);
        self::assertSame($expected, $actual);
    }

    private function getConfig(array $validators): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator' => [
                        'string' => [
                            'validators' => $validators,
                        ],
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\StringValidatorVisitorFactory;
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
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new StringValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Barcode();
        $actual    = $instance->visit($validator, [PsalmType::String]);
        self::assertSame($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([Barcode::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new StringValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Barcode();
        $actual    = $instance->visit($validator, [PsalmType::String]);
        self::assertSame($expected, $actual);

        $expected  = [PsalmType::Bool];
        $validator = new BusinessIdentifierCode();
        $actual    = $instance->visit($validator, $expected);
        self::assertSame($expected, $actual);
    }

    private function getConfig(array $validators): array
    {
        return [
            'laminas-form-shape' => [
                'validator' => [
                    'string' => [
                        'validators' => $validators,
                    ],
                ],
            ],
        ];
    }
}

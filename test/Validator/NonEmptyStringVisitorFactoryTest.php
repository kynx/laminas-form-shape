<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitorFactory;
use Laminas\Validator\Barcode;
use Laminas\Validator\BusinessIdentifierCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(NonEmptyStringVisitorFactory::class)]
final class NonEmptyStringVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new NonEmptyStringVisitorFactory();
        $instance = $factory($container);

        $expected  = new Union([new TNonEmptyString()]);
        $validator = new Barcode();
        $actual    = $instance->visit($validator, new Union([new TString()]));
        self::assertEquals($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([Barcode::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new NonEmptyStringVisitorFactory();
        $instance = $factory($container);

        $expected  = new Union([new TString()]);
        $validator = new BusinessIdentifierCode();
        $actual    = $instance->visit($validator, $expected);
        self::assertEquals($expected, $actual);
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

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitorFactory;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\File\ExcludeMimeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(FileValidatorVisitorFactory::class)]
final class FileValidatorVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new FileValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Crc32();
        $actual    = $instance->visit($validator, [PsalmType::String]);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([ExcludeMimeType::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new FileValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::Bool];
        $validator = new Crc32();
        $actual    = $instance->visit($validator, [PsalmType::Bool]);

        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $validators): array
    {
        return [
            'laminas-form-shape' => [
                'validator' => [
                    'file' => [
                        'validators' => $validators,
                    ],
                ],
            ],
        ];
    }
}

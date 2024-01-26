<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorParserFactory;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\File\ExcludeMimeType;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorParserFactory
 */
final class FileValidatorParserFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new FileValidatorParserFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::NonEmptyString];
        $validator = new Crc32();
        $actual    = $instance->getTypes($validator, [PsalmType::String]);

        self::assertEquals($expected, $actual);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([ExcludeMimeType::class]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new FileValidatorParserFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::Bool];
        $validator = new Crc32();
        $actual    = $instance->getTypes($validator, [PsalmType::Bool]);

        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $validators): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator' => [
                        'file' => [
                            'validators' => $validators,
                        ],
                    ],
                ],
            ],
        ];
    }
}

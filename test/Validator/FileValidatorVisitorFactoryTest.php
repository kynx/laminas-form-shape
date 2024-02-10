<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\FileValidatorVisitorFactory;
use Laminas\Validator\File\Crc32;
use Laminas\Validator\File\ExcludeMimeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

use function current;

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

        $previous  = new Union([new TArray([Type::getArrayKey(), Type::getMixed()])]);
        $validator = new Crc32();
        $actual    = $instance->visit($validator, $previous);

        self::assertCount(1, $actual->getAtomicTypes());
        $type = current($actual->getAtomicTypes());
        self::assertInstanceOf(TKeyedArray::class, $type);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([ExcludeMimeType::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new FileValidatorVisitorFactory();
        $instance = $factory($container);

        $expected  = new Union([new TBool()]);
        $validator = new Crc32();
        $actual    = $instance->visit($validator, new Union([new TBool()]));

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

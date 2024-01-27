<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayVisitorFactory;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function range;

#[CoversClass(InArrayVisitorFactory::class)]
final class InArrayVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultEmptyHaystackInstance(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new InArrayVisitorFactory();
        $instance = $factory($container);

        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame($types, $actual);
    }

    public function testInvokeReturnsDefaultMaxLiterals(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new InArrayVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::String];
        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => range(0, 10), 'strict' => false]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame($expected, $actual);
    }

    public function testInvokeConfiguresEmptyHaystack(): void
    {
        $config    = $this->getConfig(['allow-empty-haystack' => false]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InArrayVisitorFactory();
        $instance = $factory($container);

        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame([], $actual);
    }

    public function testInvokeConfiguresMaxLiterals(): void
    {
        $config    = $this->getConfig(['max-literals' => 0]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InArrayVisitorFactory();
        $instance = $factory($container);

        $expected  = [PsalmType::String];
        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => ['a']]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame($expected, $actual);
    }

    private function getConfig(array $config): array
    {
        return [
            'laminas-form-cli' => [
                'array-shape' => [
                    'validator' => [
                        'in-array' => $config,
                    ],
                ],
            ],
        ];
    }
}

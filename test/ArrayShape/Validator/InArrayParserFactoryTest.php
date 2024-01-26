<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayParserFactory;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function range;

#[CoversClass(InArrayParserFactory::class)]
final class InArrayParserFactoryTest extends TestCase
{
    public function testInvokeReturnsDefaultEmptyHaystackInstance(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new InArrayParserFactory();
        $instance = $factory($container);

        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame($types, $actual);
    }

    public function testInvokeReturnsDefaultMaxLiterals(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', []]]);

        $factory  = new InArrayParserFactory();
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
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InArrayParserFactory();
        $instance = $factory($container);

        $types     = [PsalmType::String, PsalmType::Null];
        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->getTypes($validator, $types);

        self::assertSame([], $actual);
    }

    public function testInvokeConfiguresMaxLiterals(): void
    {
        $config    = $this->getConfig(['max-literals' => 0]);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([['config', $config]]);

        $factory  = new InArrayParserFactory();
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

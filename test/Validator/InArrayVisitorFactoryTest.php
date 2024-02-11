<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\InArrayVisitorFactory;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TNever;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

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

        $expected  = new Union([new TNever()]);
        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->visit($validator, $expected);

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

        $previous = new Union([new TBool()]);
        $builder  = $previous->getBuilder();
        $builder->removeType('bool');
        $expected = $builder->freeze();

        $validator = new InArray(['haystack' => []]);
        $actual    = $instance->visit($validator, $previous);

        self::assertEquals($expected, $actual);
    }

    private function getConfig(array $config): array
    {
        return [
            'laminas-form-shape' => [
                'validator' => [
                    'in-array' => $config,
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\InvalidValidatorConfigurationException;
use Kynx\Laminas\FormShape\Validator\ValidatorVisitorFactoryTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

use function current;

#[CoversClass(ValidatorVisitorFactoryTrait::class)]
final class ValidatorVisitorFactoryTraitTest extends TestCase
{
    public function testGetValidatorVisitorInvalidClassThrowsException(): void
    {
        $visitors  = [stdClass::class];
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig($visitors)],
            ]);

        $mock = new MockValidatorVisitorFactory();
        self::expectException(InvalidValidatorConfigurationException::class);
        $mock->getVisitors($container);
    }

    public function testGetValidatorVisitorGetsFromContainer(): void
    {
        $visitors  = [DigitsVisitor::class];
        $visitor   = new DigitsVisitor();
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig($visitors)],
                [DigitsVisitor::class, $visitor],
            ]);

        $mock   = new MockValidatorVisitorFactory();
        $actual = $mock->getVisitors($container);
        self::assertSame([$visitor], $actual);
    }

    public function testGetValidatorVisitorReturnsNewInstance(): void
    {
        $visitors  = [DigitsVisitor::class];
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig($visitors)],
            ]);

        $mock   = new MockValidatorVisitorFactory();
        $actual = $mock->getVisitors($container);
        self::assertCount(1, $actual);
        $visitor = current($actual);
        self::assertInstanceOf(DigitsVisitor::class, $visitor);
    }

    public function testGetValidatorVisitorExcludesVisitor(): void
    {
        $visitors  = [DigitsVisitor::class];
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig($visitors)],
            ]);

        $mock   = new MockValidatorVisitorFactory();
        $actual = $mock->getVisitors($container, [DigitsVisitor::class]);
        self::assertCount(0, $actual);
    }

    private function getConfig(array $validatorVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'validator-visitors' => $validatorVisitors,
            ],
        ];
    }
}

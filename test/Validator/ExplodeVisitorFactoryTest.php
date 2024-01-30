<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitorFactory;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(ExplodeVisitorFactory::class)]
final class ExplodeVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig(['item-types' => [PsalmType::String]], [DigitsVisitor::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new ExplodeVisitorFactory();
        $instance = $factory($container);

        $validator = new Explode(['validator' => new Digits()]);
        $types     = $instance->visit($validator, [PsalmType::String]);
        self::assertSame([PsalmType::String, PsalmType::NumericString], $types);
    }

    public function testInvokeGetsValidatorFromContainer(): void
    {
        $config           = $this->getConfig(['item-types' => [PsalmType::String]], [ValidatorVisitorInterface::class]);
        $validatorVisitor = self::createStub(ValidatorVisitorInterface::class);
        $container        = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [ValidatorVisitorInterface::class, $validatorVisitor],
            ]);

        $factory  = new ExplodeVisitorFactory();
        $instance = $factory($container);

        $validatorVisitor->method('visit')
            ->willReturn([PsalmType::Bool]);
        $validator = self::createStub(ValidatorInterface::class);
        $types     = $instance->visit(new Explode(['validator' => $validator]), [PsalmType::Int]);
        self::assertSame([PsalmType::Bool], $types);
    }

    public function testInvokeExcludesExplodeVisitor(): void
    {
        $config         = $this->getConfig(['item-types' => [PsalmType::String]], [ExplodeVisitor::class]);
        $explodeVisitor = self::createMock(ValidatorVisitorInterface::class);
        $container      = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [ExplodeVisitor::class, $explodeVisitor],
            ]);

        $factory  = new ExplodeVisitorFactory();
        $instance = $factory($container);

        $explodeVisitor->expects(self::never())
            ->method('visit');
        $validator = self::createStub(ValidatorInterface::class);
        $types     = $instance->visit(new Explode(['validator' => $validator]), [PsalmType::Int]);
        self::assertSame([PsalmType::Int], $types);
    }

    private function getConfig(array $visitorConfig, array $validatorVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'validator'          => [
                    'explode' => $visitorConfig,
                ],
                'validator-visitors' => $validatorVisitors,
            ],
        ];
    }
}

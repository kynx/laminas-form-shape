<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitorFactory;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic\TNever;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Container\ContainerInterface;

#[CoversClass(ExplodeVisitorFactory::class)]
final class ExplodeVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $config    = $this->getConfig([DigitsVisitor::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $factory  = new ExplodeVisitorFactory();
        $instance = $factory($container);

        $validator = new Explode(['validator' => new Digits(), 'valueDelimiter' => null]);
        $actual    = $instance->visit($validator, new Union([new TString()]));
        self::assertArrayHasKey('numeric-string', $actual->getAtomicTypes());
    }

    public function testInvokeExcludesExplodeVisitor(): void
    {
        $config    = $this->getConfig([ExplodeVisitor::class]);
        $validator = new DigitsVisitor();
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [ExplodeVisitor::class, new ExplodeVisitor([$validator])],
            ]);

        $factory  = new ExplodeVisitorFactory();
        $instance = $factory($container);

        $validator = self::createMock(ValidatorInterface::class);
        $validator->expects(self::never())
            ->method('isValid');
        $actual = $instance->visit(new Explode(['validator' => $validator]), new Union([new TNever()]));
        self::assertArrayHasKey('never', $actual->getAtomicTypes());
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

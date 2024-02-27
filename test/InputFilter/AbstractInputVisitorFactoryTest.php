<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\InputFilter\AbstractInputVisitorFactory;
use Kynx\Laminas\FormShape\Validator\BetweenVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(AbstractInputVisitorFactory::class)]
final class AbstractInputVisitorFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $allowListVisitor = new AllowListVisitor();
        $betweenVisitor   = new BetweenVisitor();
        $container        = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $this->getConfig([AllowListVisitor::class], [BetweenVisitor::class])],
                [AllowListVisitor::class, $allowListVisitor],
                [BetweenVisitor::class, $betweenVisitor],
            ]);

        $factory  = new MockAbstractInputVisitorFactory();
        $instance = $factory($container);

        self::assertEquals([$allowListVisitor], $instance->getFilterVisitors());
        self::assertEquals([$betweenVisitor], $instance->getValidatorVisitors());
    }

    private function getConfig(array $filterVisitors, array $validatorVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'filter-visitors'    => $filterVisitors,
                'validator-visitors' => $validatorVisitors,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\InputVisitorManagerFactory;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(InputVisitorManagerFactory::class)]
final class InputVisitorManagerFactoryTest extends TestCase
{
    public function testInvokeReturnsConfiguredInstance(): void
    {
        $expected = self::createStub(InputVisitorInterface::class);

        $config    = $this->getConfig([Input::class => InputVisitorInterface::class]);
        $container = self::createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [InputVisitorInterface::class, $expected],
            ]);

        $factory  = new InputVisitorManagerFactory();
        $instance = $factory($container);

        $actual = $instance->getVisitor(new Input());
        self::assertSame($expected, $actual);
    }

    private function getConfig(array $inputVisitors): array
    {
        return [
            'laminas-form-shape' => [
                'input-visitors' => $inputVisitors,
            ],
        ];
    }
}

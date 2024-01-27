<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorManagerFactory;
use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
use Laminas\InputFilter\Input;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\InputFilter\InputVisitorManagerFactory
 */
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
            'laminas-form-cli' => [
                'array-shape' => [
                    'input-visitors' => $inputVisitors,
                ],
            ],
        ];
    }
}

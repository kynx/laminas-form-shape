<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorFactory;
use KynxTest\Laminas\FormShape\Locator\Asset\TestForm;
use Laminas\Form\FormElementManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

#[CoversClass(FormLocatorFactory::class)]
final class FormLocatorFactoryTest extends TestCase
{
    public function testInvokeReturnsInstanceConfiguredWithContainerFormElementManager(): void
    {
        $formElementManager = new FormElementManager(require __DIR__ . '/../container.php');
        $container          = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [FormElementManager::class, $formElementManager],
            ]);

        $factory  = new FormLocatorFactory();
        $instance = $factory($container);
        $expected = [
            new FormFile(new ReflectionClass(TestForm::class), $formElementManager->get(TestForm::class)),
        ];

        $actual = $instance->locate([__DIR__ . '/Asset/TestForm.php']);
        self::assertEquals($expected, $actual);
    }
}

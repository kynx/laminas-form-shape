<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\File;

use Kynx\Laminas\FormShape\File\FormFile;
use Kynx\Laminas\FormShape\File\FormReaderFactory;
use KynxTest\Laminas\FormShape\File\Asset\TestForm;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\PluginManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

#[CoversClass(FormReaderFactory::class)]
final class FormReaderFactoryTest extends TestCase
{
    public function testInvokeReturnsElementWithContainerFormElementManager(): void
    {
        $formElementManager = self::createMock(PluginManagerInterface::class);
        $container          = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [FormElementManager::class, $formElementManager],
            ]);

        $factory  = new FormReaderFactory();
        $instance = $factory($container);

        $formElementManager->expects(self::once())
            ->method('get')
            ->with(TestForm::class)
            ->willThrowException(self::createStub(ContainerExceptionInterface::class));

        $actual = $instance->getFormFile(__DIR__ . '/Asset/TestForm.php');
        self::assertNull($actual);
    }

    public function testInvokeReturnsInstanceConfiguredWithDefaultFormManager(): void
    {
        $container = self::createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturn(false);

        $factory  = new FormReaderFactory();
        $instance = $factory($container);

        $actual = $instance->getFormFile(__DIR__ . '/Asset/TestForm.php');
        self::assertInstanceOf(FormFile::class, $actual);
    }
}

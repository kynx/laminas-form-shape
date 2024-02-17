<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Composer\Autoload\ClassLoader;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocator;
use KynxTest\Laminas\FormShape\Locator\Asset\Sub\SubForm;
use KynxTest\Laminas\FormShape\Locator\Asset\TestForm;
use Laminas\Form\Exception\InvalidElementException;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\PluginManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

#[CoversClass(FormLocator::class)]
final class FormLocatorTest extends TestCase
{
    private ClassLoader $loader;
    private FormElementManager $formElementManager;
    private FormLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ClassLoader $loader */
        $loader = require 'vendor/autoload.php';
        /** @var ContainerInterface $container */
        $container = require __DIR__ . '/../container.php';

        $this->loader             = $loader;
        $this->formElementManager = new FormElementManager($container);
        $this->locator            = new FormLocator($this->loader, $this->formElementManager);
    }

    public function testLocateUnreadablePathReturnsEmptyArray(): void
    {
        $actual = $this->locator->locate([__DIR__ . '/nonexistent']);
        self::assertSame([], $actual);
    }

    public function testLocateReturnsFormsFromPaths(): void
    {
        $expected = [
            new FormFile(new ReflectionClass(TestForm::class), $this->formElementManager->get(TestForm::class)),
            new FormFile(new ReflectionClass(SubForm::class), $this->formElementManager->get(SubForm::class)),
        ];

        $actual = $this->locator->locate([__DIR__ . '/Asset/TestForm.php', __DIR__ . '/Asset/Sub/SubForm.php']);
        self::assertEquals($expected, $actual);
    }

    public function testLocateReturnsFormsFromDirectory(): void
    {
        $expected = [
            new FormFile(new ReflectionClass(TestForm::class), $this->formElementManager->get(TestForm::class)),
            new FormFile(new ReflectionClass(SubForm::class), $this->formElementManager->get(SubForm::class)),
        ];

        $actual = $this->locator->locate([__DIR__ . '/Asset']);
        self::assertEquals($expected, $actual);
    }

    public function testLocateFiltersFilesFormElementManagerCannotFind(): void
    {
        $formElementManager = $this->createStub(PluginManagerInterface::class);
        $formElementManager->method('get')
            ->willThrowException(new InvalidElementException());
        $locator = new FormLocator($this->loader, $formElementManager);

        $actual = $locator->locate([__DIR__ . '/Asset/TestForm.php']);
        self::assertSame([], $actual);
    }
}

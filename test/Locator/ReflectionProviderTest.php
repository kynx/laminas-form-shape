<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Composer\Autoload\ClassLoader;
use Kynx\Laminas\FormShape\Locator\ReflectionProvider;
use KynxTest\Laminas\FormShape\Locator\Asset\TestForm;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function dirname;
use function file_exists;
use function str_replace;

#[CoversClass(ReflectionProvider::class)]
final class ReflectionProviderTest extends TestCase
{
    private ClassLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ClassLoader $loader */
        $loader       = require 'vendor/autoload.php';
        $this->loader = $loader;
    }

    public function testGetReflectionNonPhpFileReturnsNull(): void
    {
        $path     = __DIR__ . '/composer.json';
        $provider = new ReflectionProvider($this->loader, FormInterface::class);

        $actual = $provider->getReflection($path);
        self::assertNull($actual);
    }

    public function testGetReflectionNotResolvableReturnsNull(): void
    {
        $projectRoot = $this->getProjectRoot();
        $path        = dirname($projectRoot) . '/nonexistent/Foo.php';
        $provider    = new ReflectionProvider($this->loader, FormInterface::class);

        $actual = $provider->getReflection($path);
        self::assertNull($actual);
    }

    public function testGetReflectionNotClassReturnsNull(): void
    {
        $path     = __DIR__ . '/Asset/NoClass.php';
        $provider = new ReflectionProvider($this->loader, FormInterface::class);

        $actual = $provider->getReflection($path);
        self::assertNull($actual);
    }

    public function testGetReflectionNotInstanceOfReturnsNull(): void
    {
        $path     = __DIR__ . '/Asset/TestForm.php';
        $provider = new ReflectionProvider($this->loader, FieldsetInterface::class, FormInterface::class);

        $actual = $provider->getReflection($path);
        self::assertNull($actual);
    }

    public function testGetReflectionFromPathReturnsReflection(): void
    {
        $expected = new ReflectionClass(TestForm::class);
        $path     = __DIR__ . '/Asset/TestForm.php';
        $provider = new ReflectionProvider($this->loader, FieldsetInterface::class);

        $actual = $provider->getReflection($path);
        self::assertEquals($expected, $actual);
    }

    public function testGetReflectionFromRelativePathReturnsReflection(): void
    {
        $expected = new ReflectionClass(TestForm::class);
        $path     = str_replace($this->getProjectRoot(), '.', __DIR__ . '/Asset/TestForm.php');
        $provider = new ReflectionProvider($this->loader, FieldsetInterface::class);

        $actual = $provider->getReflection($path);
        self::assertEquals($expected, $actual);
    }

    private function getProjectRoot(): string
    {
        $dir = __DIR__;
        do {
            $file = $dir . '/composer.json';
            $dir  = dirname($dir);
        } while ($dir !== '' && ! file_exists($file));

        return dirname($file);
    }
}

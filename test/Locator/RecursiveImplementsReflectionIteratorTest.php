<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator;

use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use Kynx\Laminas\FormShape\Locator\ImplementsReflectionProvider;
use Kynx\Laminas\FormShape\Locator\RecursiveImplementsReflectionIterator;
use KynxTest\Laminas\FormShape\Locator\Asset\Sub\SubForm;
use KynxTest\Laminas\FormShape\Locator\Asset\TestForm;
use Laminas\Form\FormInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

use function array_filter;
use function iterator_to_array;

#[CoversClass(RecursiveImplementsReflectionIterator::class)]
final class RecursiveImplementsReflectionIteratorTest extends TestCase
{
    private ClassLoader $loader;

    protected function setUp(): void
    {
        /** @var ClassLoader $loader */
        $loader       = require 'vendor/autoload.php';
        $this->loader = $loader;
    }

    public function testIteratorRecursesFileInfo(): void
    {
        $expected           = [
            __DIR__ . '/Asset/TestForm.php'    => new ReflectionClass(TestForm::class),
            __DIR__ . '/Asset/Sub/SubForm.php' => new ReflectionClass(SubForm::class),
        ];
        $flags              = FilesystemIterator::SKIP_DOTS;
        $reflectionProvider = new ImplementsReflectionProvider($this->loader, FormInterface::class);
        $directoryIterator  = new RecursiveDirectoryIterator(__DIR__ . '/Asset', $flags);
        $instanceOfIterator = new RecursiveImplementsReflectionIterator($directoryIterator, $reflectionProvider);

        $actual = array_filter(iterator_to_array(new RecursiveIteratorIterator($instanceOfIterator)));
        self::assertEquals($expected, $actual);
    }

    public function testIteratorRecursesStrings(): void
    {
        $expected           = [
            __DIR__ . '/Asset/TestForm.php'    => new ReflectionClass(TestForm::class),
            __DIR__ . '/Asset/Sub/SubForm.php' => new ReflectionClass(SubForm::class),
        ];
        $flags              = FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME;
        $reflectionProvider = new ImplementsReflectionProvider($this->loader, FormInterface::class);
        $directoryIterator  = new RecursiveDirectoryIterator(__DIR__ . '/Asset', $flags);
        $instanceOfIterator = new RecursiveImplementsReflectionIterator($directoryIterator, $reflectionProvider);

        $actual = array_filter(iterator_to_array(new RecursiveIteratorIterator($instanceOfIterator)));
        self::assertEquals($expected, $actual);
    }
}

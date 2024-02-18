<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use AppendIterator;
use ArrayObject;
use CallbackFilterIterator;
use Composer\Autoload\ClassLoader;
use EmptyIterator;
use FilesystemIterator;
use Iterator;
use Laminas\Form\Exception\InvalidElementException;
use Laminas\Form\FormInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

use function array_filter;
use function array_map;
use function is_dir;
use function is_file;
use function is_readable;
use function iterator_to_array;
use function usort;

final readonly class FormLocator implements FormLocatorInterface
{
    private InstanceOfReflectionProvider $reflectionProvider;

    public function __construct(ClassLoader $loader, private PluginManagerInterface $formElementManager)
    {
        $this->reflectionProvider = new InstanceOfReflectionProvider($loader, FormInterface::class);
    }

    public function locate(array $paths): array
    {
        $iterator = new AppendIterator();
        foreach ($paths as $path) {
            $located = $this->locateFormsAtPath($path);
            $iterator->append($located);
        }

        $formFiles = array_filter(array_map(
            /** @param ReflectionClass<FormInterface> $reflection */
            fn (ReflectionClass $reflection): ?FormFile => $this->getFormFile($reflection),
            iterator_to_array($iterator)
        ));

        usort(
            $formFiles,
            static fn (FormFile $a, FormFile $b): int => $a->reflection->getName() <=> $b->reflection->getName()
        );

        return $formFiles;
    }

    private function locateFormsAtPath(string $path): Iterator
    {
        if (! is_readable($path)) {
            return new EmptyIterator();
        }
        if (is_file($path)) {
            return (new ArrayObject([$path => $this->reflectionProvider->getReflection($path)]))->getIterator();
        }
        if (! is_dir($path)) {
            return new EmptyIterator();
        }

        $directoryIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        /** @var RecursiveInstanceOfReflectionIterator<FormInterface> $reflectionIterator */
        $reflectionIterator = new RecursiveInstanceOfReflectionIterator($directoryIterator, $this->reflectionProvider);

        return new CallbackFilterIterator(
            new RecursiveIteratorIterator($reflectionIterator),
            /** @param ReflectionClass<FormInterface>|null $reflection */
            static fn (?ReflectionClass $reflection): bool => $reflection !== null
        );
    }

    /**
     * @param ReflectionClass<FormInterface> $reflection
     */
    private function getFormFile(ReflectionClass $reflection): ?FormFile
    {
        try {
            $form = $this->formElementManager->get($reflection->getName());
        } catch (InvalidElementException) {
            return null;
        }

        if ($form instanceof FormInterface) {
            return new FormFile($reflection, $form);
        }

        return null;
    }
}

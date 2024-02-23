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
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Laminas\Form\Exception\InvalidElementException;
use Laminas\Form\FormInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

use function is_dir;
use function is_file;
use function is_readable;
use function iterator_to_array;
use function usort;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormLocator implements FormLocatorInterface
{
    private ReflectionProvider $reflectionProvider;

    public function __construct(ClassLoader $loader, private PluginManagerInterface $formElementManager)
    {
        $this->reflectionProvider = new ReflectionProvider($loader, FormInterface::class);
    }

    public function locate(array $paths): array
    {
        $iterator = new AppendIterator();
        foreach ($paths as $path) {
            $located = $this->locateFormsAtPath($path);
            $iterator->append($located);
        }

        /** @var array<FormFile> $formFiles */
        $formFiles = iterator_to_array($iterator);
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
            $formFile = $this->locateFormFromFile($path);
            return $formFile instanceof FormFile
                ? (new ArrayObject([$path => $formFile]))->getIterator()
                : new EmptyIterator();
        }
        if (! is_dir($path)) {
            return new EmptyIterator();
        }

        $directoryIterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        /** @var RecursiveReflectionIterator<FormInterface> $reflectionIterator */
        $reflectionIterator = new RecursiveReflectionIterator($directoryIterator, $this->reflectionProvider);
        $formFileIterator   = new RecursiveFormFileIterator($reflectionIterator, $this->formElementManager);

        return new CallbackFilterIterator(
            new RecursiveIteratorIterator($formFileIterator),
            static fn (?FormFile $formFile): bool => $formFile !== null
        );
    }

    private function locateFormFromFile(string $path): ?FormFile
    {
        /** @var ReflectionClass<FormInterface>|null $reflection */
        $reflection = $this->reflectionProvider->getReflection($path);
        if ($reflection == null) {
            return null;
        }

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

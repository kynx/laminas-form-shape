<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\File;

use Laminas\Form\FormInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use Nette\PhpGenerator\PhpFile;
use Psr\Container\ContainerExceptionInterface;

use function assert;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function is_a;

final readonly class FormReader implements FormReaderInterface
{
    public function __construct(private PluginManagerInterface $formElementManager)
    {
    }

    public function getFormFile(string $path): ?FormFile
    {
        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        $file     = PhpFile::fromCode($contents);
        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $className = $class->getName();
                if ($className === null) {
                    continue;
                }

                $fullyQualified = $namespace->resolveName($className);
                if ($this->isFormInterface($fullyQualified)) {
                    try {
                        $form = $this->formElementManager->get($fullyQualified);
                        assert($form instanceof FormInterface);
                        return new FormFile($path, $file, $form);
                    } catch (ContainerExceptionInterface) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    private function isFormInterface(string $fullyQualified): bool
    {
        return class_exists($fullyQualified) && is_a($fullyQualified, FormInterface::class, true);
    }
}

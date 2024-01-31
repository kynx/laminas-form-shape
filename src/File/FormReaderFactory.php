<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\File;

use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;

final readonly class FormReaderFactory
{
    public function __invoke(ContainerInterface $container): FormReader
    {
        if ($container->has(FormElementManager::class)) {
            $formElementManger = $container->get(FormElementManager::class);
        } else {
            $formElementManger = new FormElementManager($container);
        }

        return new FormReader($formElementManger);
    }
}

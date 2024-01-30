<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Laminas\Form\FormElementManager;
use Psr\Container\ContainerInterface;

final readonly class FormProcessorFactory
{
    public function __invoke(ContainerInterface $container): FormProcessor
    {
        if ($container->has(FormElementManager::class)) {
            $formElementManger = $container->get(FormElementManager::class);
        } else {
            $formElementManger = new FormElementManager($container);
        }

        return new FormProcessor($formElementManger);
    }
}

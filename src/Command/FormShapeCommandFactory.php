<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Form\FormProcessor;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Psr\Container\ContainerInterface;

final readonly class FormShapeCommandFactory
{
    public function __invoke(ContainerInterface $container): FormShapeCommand
    {
        return new FormShapeCommand(
            $container->get(FormProcessor::class),
            $container->get(InputFilterVisitorInterface::class)
        );
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Decorator\ArrayShapeDecorator;
use Kynx\Laminas\FormShape\File\FormReader;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Psr\Container\ContainerInterface;

final readonly class FormShapeCommandFactory
{
    public function __invoke(ContainerInterface $container): FormShapeCommand
    {
        return new FormShapeCommand(
            $container->get(FormReader::class),
            $container->get(InputFilterVisitorInterface::class),
            $container->get(ArrayShapeDecorator::class)
        );
    }
}

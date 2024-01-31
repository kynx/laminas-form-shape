<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Decorator\CollectionFilterShapeDecorator;
use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecorator;
use Kynx\Laminas\FormShape\File\FormReader;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Psr\Container\ContainerInterface;

final readonly class FormShapeCommandFactory
{
    public function __invoke(ContainerInterface $container): FormShapeCommand
    {
        $inputFilterShapeDecorator = $container->get(InputFilterShapeDecorator::class);
        return new FormShapeCommand(
            $container->get(FormReader::class),
            $container->get(FormVisitorInterface::class),
            $container->get(InputFilterShapeDecorator::class),
            new CollectionFilterShapeDecorator($inputFilterShapeDecorator)
        );
    }
}

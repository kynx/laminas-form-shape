<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Psr\Container\ContainerInterface;

final readonly class FileWriterFactory
{
    public function __invoke(ContainerInterface $container): FileWriter
    {
        return new FileWriter(
            $container->get(TypeNamerInterface::class),
            $container->get(DecoratorInterface::class)
        );
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FileWriterFactory
{
    public function __invoke(ContainerInterface $container): FileWriter
    {
        return new FileWriter($container->get(CodeGeneratorInterface::class));
    }
}

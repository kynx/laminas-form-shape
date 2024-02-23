<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Locator\FormLocator;
use Kynx\Laminas\FormShape\Writer\FileWriter;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormProcessorFactory
{
    public function __invoke(ContainerInterface $container): FormProcessor
    {
        return new FormProcessor(
            $container->get(FormLocator::class),
            $container->get(FormVisitor::class),
            $container->get(FileWriter::class),
        );
    }
}

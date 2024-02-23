<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Form\FormProcessor;
use Psr\Container\ContainerInterface;

final readonly class PsalmTypeCommandFactory
{
    public function __invoke(ContainerInterface $container): PsalmTypeCommand
    {
        return new PsalmTypeCommand(
            $container->get(FormProcessor::class)
        );
    }
}

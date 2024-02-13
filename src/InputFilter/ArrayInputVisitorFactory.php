<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psr\Container\ContainerInterface;

final readonly class ArrayInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): ArrayInputVisitor
    {
        return new ArrayInputVisitor($container->get(InputVisitor::class));
    }
}

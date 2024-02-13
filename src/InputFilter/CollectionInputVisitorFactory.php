<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psr\Container\ContainerInterface;

final readonly class CollectionInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): CollectionInputVisitor
    {
        return new CollectionInputVisitor($container->get(InputVisitor::class));
    }
}

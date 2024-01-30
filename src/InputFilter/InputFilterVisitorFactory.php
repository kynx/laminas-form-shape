<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psr\Container\ContainerInterface;

final readonly class InputFilterVisitorFactory
{
    public function __invoke(ContainerInterface $container): InputFilterVisitor
    {
        return new InputFilterVisitor($container->get(InputVisitorManager::class));
    }
}

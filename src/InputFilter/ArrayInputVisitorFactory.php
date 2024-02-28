<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psr\Container\ContainerInterface;

final readonly class ArrayInputVisitorFactory extends AbstractInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): ArrayInputVisitor
    {
        return new ArrayInputVisitor(
            $this->getFilterVisitors($container),
            $this->getValidatorVisitors($container)
        );
    }
}

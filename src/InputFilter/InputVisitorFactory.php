<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Psr\Container\ContainerInterface;

final readonly class InputVisitorFactory extends AbstractInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): InputVisitor
    {
        return new InputVisitor(
            $this->getFilterVisitors($container),
            $this->getValidatorVisitors($container)
        );
    }
}

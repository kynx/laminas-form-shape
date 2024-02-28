<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilter\AbstractInputVisitorFactory;
use Psr\Container\ContainerInterface;

final readonly class MockAbstractInputVisitorFactory extends AbstractInputVisitorFactory
{
    public function __invoke(ContainerInterface $container): MockInputVisitor
    {
        return new MockInputVisitor(
            $this->getFilterVisitors($container),
            $this->getValidatorVisitors($container)
        );
    }
}

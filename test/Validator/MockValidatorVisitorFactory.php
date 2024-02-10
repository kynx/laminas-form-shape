<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\ValidatorVisitorFactoryTrait;
use Psr\Container\ContainerInterface;

final readonly class MockValidatorVisitorFactory
{
    use ValidatorVisitorFactoryTrait;

    public function getVisitors(ContainerInterface $container): array
    {
        return $this->getValidatorVisitors($container);
    }
}

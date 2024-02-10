<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\ValidatorVisitorFactoryTrait;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

final readonly class MockValidatorVisitorFactory
{
    use ValidatorVisitorFactoryTrait;

    /**
     * @param list<class-string<ValidatorVisitorInterface>> $exclude
     */
    public function getVisitors(ContainerInterface $container, array $exclude = []): array
    {
        return $this->getValidatorVisitors($container, $exclude);
    }
}

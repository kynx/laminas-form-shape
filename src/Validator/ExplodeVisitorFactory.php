<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Psr\Container\ContainerInterface;

use function array_filter;

final readonly class ExplodeVisitorFactory
{
    use ValidatorVisitorFactoryTrait;

    public function __invoke(ContainerInterface $container): ExplodeVisitor
    {
        $validatorVisitors = array_filter(
            $this->getValidatorVisitors($container),
            static fn (ValidatorVisitorInterface $v): bool => ! $v instanceof ExplodeVisitor
        );

        return new ExplodeVisitor($validatorVisitors);
    }
}

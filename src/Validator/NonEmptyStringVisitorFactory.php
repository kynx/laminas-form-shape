<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class NonEmptyStringVisitorFactory
{
    public function __invoke(ContainerInterface $container): NonEmptyStringVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var list<class-string<ValidatorInterface>> $validators */
        $validators = $config['laminas-form-shape']['validator']['string']['validators']
            ?? NonEmptyStringVisitor::DEFAULT_VALIDATORS;

        return new NonEmptyStringVisitor($validators);
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\ConfigProvider;
use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type FormShapeConfigurationArray from ConfigProvider
 */
final readonly class FileValidatorVisitorFactory
{
    public function __invoke(ContainerInterface $container): FileValidatorVisitor
    {
        /** @var FormShapeConfigurationArray $config */
        $config = $container->get('config') ?? [];
        /** @var array{validators?: list<class-string<ValidatorInterface>>} $validatorConfig */
        $validatorConfig = (array) ($config['laminas-form-shape']['validator']['file'] ?? []);
        $validators      = $validatorConfig['validators'] ?? FileValidatorVisitor::DEFAULT_VALIDATORS;

        return new FileValidatorVisitor($validators);
    }
}

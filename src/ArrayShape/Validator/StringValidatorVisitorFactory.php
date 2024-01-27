<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ConfigProvider;
use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type ConfigProviderArray from ConfigProvider
 */
final readonly class StringValidatorVisitorFactory
{
    public function __invoke(ContainerInterface $container): StringValidatorVisitor
    {
        /** @var ConfigProviderArray $config */
        $config = $container->get('config') ?? [];
        /** @var list<class-string<ValidatorInterface>> $validators */
        $validators = $config['laminas-form-cli']['array-shape']['validator']['string']['validators']
            ?? StringValidatorVisitor::DEFAULT_VALIDATORS;

        return new StringValidatorVisitor($validators);
    }
}

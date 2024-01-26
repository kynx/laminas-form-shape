<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ConfigProvider;
use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type ConfigProviderArray from ConfigProvider
 */
final readonly class FileValidatorParserFactory
{
    public function __invoke(ContainerInterface $container): FileValidatorParser
    {
        /** @var ConfigProviderArray $config */
        $config = $container->get('config') ?? [];
        /** @var array{validators?: list<class-string<ValidatorInterface>>} $validatorConfig */
        $validatorConfig = (array) ($config['laminas-form-cli']['array-shape']['validator']['file'] ?? []);
        $validators      = $validatorConfig['validators'] ?? FileValidatorParser::DEFAULT_VALIDATORS;

        return new FileValidatorParser($validators);
    }
}

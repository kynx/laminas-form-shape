<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

/**
 * @psalm-import-type ConfigProviderArray from ConfigProvider
 */
final readonly class InArrayParserFactory
{
    public function __invoke(ContainerInterface $container): InArrayParser
    {
        /** @var ConfigProviderArray $config */
        $config       = $container->get('config');
        $parserConfig = (array) ($config['laminas-form-cli']['array-shape']['validator']['in-array'] ?? []);

        return new InArrayParser(
            (bool) ($parserConfig['allow-empty-haystack'] ?? true),
            (int) ($parserConfig['max-literals'] ?? InArrayParser::DEFAULT_MAX_LITERALS)
        );
    }
}

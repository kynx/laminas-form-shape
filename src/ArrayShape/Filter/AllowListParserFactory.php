<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ConfigProvider;
use Psr\Container\ContainerInterface;

/** @psalm-import-type ConfigProviderArray from ConfigProvider */
final readonly class AllowListParserFactory
{
    public function __invoke(ContainerInterface $container): AllowListParser
    {
        /** @var ConfigProviderArray $config */
        $config = $container->get('config');
        // phpcs:disable Generic.Files.LineLength.TooLong
        $allowEmpty = (bool)($config['laminas-form-cli']['array-shape']['filter']['allow-list']['allow-empty-list'] ?? true);
        $maxLiterals = (int) ($config['laminas-form-cli']['array-shape']['filter']['allow-list']['max-literals'] ?? AllowListParser::DEFAULT_MAX_LITERALS);
        // phpcs:enable

        return new AllowListParser($allowEmpty, $maxLiterals);
    }
}

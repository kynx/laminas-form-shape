<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\DigitsParser as DigitsFilterParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\InflectorParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToFloatParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntParser;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToNullParser;
use Laminas\ServiceManager\ConfigInterface;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-type ConfigProviderArray = array{
 *     laminas-cli: array,
 *     laminas-form-cli: array{
 *         array-shape: array{
 *             filter-parsers: array<class-string>,
 *             filter?: array<string, mixed>,
 *         },
 *     },
 *     dependencies: ServiceManagerConfigurationType,
 * }
 */
final readonly class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'laminas-cli'      => $this->getCliConfig(),
            'laminas-form-cli' => $this->getLaminasFormCliConfig(),
            'dependencies'     => $this->getDependencyConfig(),
        ];
    }

    private function getCliConfig(): array
    {
        return [];
    }

    private function getLaminasFormCliConfig(): array
    {
        return [
            'array-shape' => [
                'filter-parsers' => [
                    AllowListParser::class,
                    BooleanParser::class,
                    DigitsFilterParser::class,
                    InflectorParser::class,
                    ToFloatParser::class,
                    ToIntParser::class,
                    ToNullParser::class,
                ],
            ],
        ];
    }

    private function getDependencyConfig(): array
    {
        return [
            'factories' => [
                AllowListParser::class => AllowListParserFactory::class,
            ],
        ];
    }
}

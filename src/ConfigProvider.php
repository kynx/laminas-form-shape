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
use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\BetweenParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsParser as DigitsValidatorParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\HexParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsbnParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsCountableParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsInstanceOfParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\NotEmptyParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\Sitemap\PriorityParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StepParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringLengthParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorParser;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorParserFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\TimezoneParser;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\ServiceManager\ConfigInterface;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-type FilterParserList = list<class-string<FilterParserInterface>>
 * @psalm-type ValidatorParserList = list<class-string<ValidatorParserInterface>>
 * @psalm-type ArrayShapeArray = array{
 *      filter-parsers: FilterParserList,
 *      validator-parsers: ValidatorParserList,
 *      filter?: array<string, mixed>,
 *      validator: array<string, mixed>,
 * }
 * @psalm-type ConfigProviderArray = array{
 *     laminas-cli: array,
 *     laminas-form-cli: array{
 *         array-shape: ArrayShapeArray,
 *     },
 *     dependencies: ServiceManagerConfigurationType,
 * }
 */
final readonly class ConfigProvider
{
    /**
     * @return ConfigProviderArray
     */
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

    /**
     * @return array{array-shape: ArrayShapeArray}
     */
    private function getLaminasFormCliConfig(): array
    {
        return [
            'array-shape' => [
                'filter-parsers'    => [
                    AllowListParser::class,
                    BooleanParser::class,
                    DigitsFilterParser::class,
                    InflectorParser::class,
                    ToFloatParser::class,
                    ToIntParser::class,
                    ToNullParser::class,
                ],
                'validator-parsers' => [
                    BetweenParser::class,
                    DigitsValidatorParser::class,
                    ExplodeParser::class,
                    FileValidatorParser::class,
                    HexParser::class,
                    InArrayParser::class,
                    IsbnParser::class,
                    IsCountableParser::class,
                    IsInstanceOfParser::class,
                    NotEmptyParser::class,
                    PriorityParser::class,
                    RegexParser::class,
                    StepParser::class,
                    StringLengthParser::class,
                    StringValidatorParser::class,
                    TimezoneParser::class,
                ],
                'validator'         => [
                    'in-array' => [
                        'allow-empty-haystack' => true,
                        'max-literals'         => 10,
                    ],
                    'regex'    => [
                        'patterns' => [
                            [
                                /* @link \Laminas\Form\Element\Color */
                                'pattern' => '/^#[0-9a-fA-F]{6}$/',
                                'types'   => [PsalmType::String],
                                'replace' => [],
                            ],
                            [
                                /* @link \Laminas\Form\Element\Email */
                                'pattern' => '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/',
                                'types'   => [PsalmType::String],
                                'replace' => [],
                            ],
                            [
                                /* @link \Laminas\Form\Element\Month */
                                'pattern' => '/^[0-9]{4}\-(0[1-9]|1[012])$/',
                                'types'   => [PsalmType::String],
                                'replace' => [],
                            ],
                            [
                                /* @link \Laminas\Form\Element\Number */
                                'pattern' => '(^-?\d*(\.\d+)?$)',
                                'types'   => [PsalmType::Int, PsalmType::Float],
                                'replace' => [[PsalmType::String, PsalmType::NumericString]],
                            ],
                            [
                                /* @link \Laminas\Form\Element\Tel */
                                'pattern' => "/^[^\r\n]*$/",
                                'types'   => [PsalmType::String],
                                'replace' => [],
                            ],
                            [
                                /* @link \Laminas\Form\Element\Week */
                                'pattern' => '/^[0-9]{4}\-W[0-9]{2}$/',
                                'types'   => [PsalmType::String],
                                'replace' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return ServiceManagerConfigurationType
     */
    private function getDependencyConfig(): array
    {
        return [
            'factories' => [
                AllowListParser::class       => AllowListParserFactory::class,
                ExplodeParser::class         => ExplodeParserFactory::class,
                FileValidatorParser::class   => FileValidatorParserFactory::class,
                InArrayParser::class         => InArrayParserFactory::class,
                RegexParser::class           => RegexParserFactory::class,
                StringValidatorParser::class => StringValidatorParserFactory::class,
            ],
        ];
    }
}

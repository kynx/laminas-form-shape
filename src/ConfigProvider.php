<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\DigitsVisitor as DigitsFilterVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\InflectorVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToFloatVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Filter\ToNullVisitor;
use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Validator\BetweenVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\DigitsVisitor as DigitsValidatorVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\ExplodeVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\FileValidatorVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\HexVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\InArrayVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsbnVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsCountableVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\IsInstanceOfVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\RegexVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\Sitemap\PriorityVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StepVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringLengthVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Validator\StringValidatorVisitorFactory;
use Kynx\Laminas\FormCli\ArrayShape\Validator\TimezoneVisitor;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\ServiceManager\ConfigInterface;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-type FilterVisitorList = list<class-string<FilterVisitorInterface>>
 * @psalm-type ValidatorVisitorList = list<class-string<ValidatorVisitorInterface>>
 * @psalm-type ArrayShapeArray = array{
 *      filter-visitors: FilterVisitorList,
 *      validator-visitors: ValidatorVisitorList,
 *      filter?: array<string, mixed>,
 *      validator: array<string, mixed>,
 * }
 * @psalm-type FormCliConfigurationArray = array{
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
     * @return FormCliConfigurationArray
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
                'filter-visitors'    => [
                    AllowListVisitor::class,
                    BooleanVisitor::class,
                    DigitsFilterVisitor::class,
                    InflectorVisitor::class,
                    ToFloatVisitor::class,
                    ToIntVisitor::class,
                    ToNullVisitor::class,
                ],
                'validator-visitors' => [
                    BetweenVisitor::class,
                    DigitsValidatorVisitor::class,
                    ExplodeVisitor::class,
                    FileValidatorVisitor::class,
                    HexVisitor::class,
                    InArrayVisitor::class,
                    IsbnVisitor::class,
                    IsCountableVisitor::class,
                    IsInstanceOfVisitor::class,
                    NotEmptyVisitor::class,
                    PriorityVisitor::class,
                    RegexVisitor::class,
                    StepVisitor::class,
                    StringLengthVisitor::class,
                    StringValidatorVisitor::class,
                    TimezoneVisitor::class,
                ],
                'validator'          => [
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
                AllowListVisitor::class       => AllowListVisitorFactory::class,
                ExplodeVisitor::class         => ExplodeVisitorFactory::class,
                FileValidatorVisitor::class   => FileValidatorVisitorFactory::class,
                InArrayVisitor::class         => InArrayVisitorFactory::class,
                RegexVisitor::class           => RegexVisitorFactory::class,
                StringValidatorVisitor::class => StringValidatorVisitorFactory::class,
            ],
        ];
    }
}

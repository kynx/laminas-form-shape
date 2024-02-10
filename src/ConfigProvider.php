<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Command\FormShapeCommand;
use Kynx\Laminas\FormShape\Command\FormShapeCommandFactory;
use Kynx\Laminas\FormShape\Decorator\UnionDecorator;
use Kynx\Laminas\FormShape\Decorator\UnionDecoratorFactory;
use Kynx\Laminas\FormShape\File\FormReader;
use Kynx\Laminas\FormShape\File\FormReaderFactory;
use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\Filter\AllowListVisitorFactory;
use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Filter\DigitsVisitor as DigitsFilterVisitor;
use Kynx\Laminas\FormShape\Filter\InflectorVisitor;
use Kynx\Laminas\FormShape\Filter\ToFloatVisitor;
use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\Filter\ToNullVisitor;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\Form\FormVisitorFactory;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManager;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorManagerFactory;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Validator\BetweenVisitor;
use Kynx\Laminas\FormShape\Validator\CsrfVisitor;
use Kynx\Laminas\FormShape\Validator\DateStepVisitor;
use Kynx\Laminas\FormShape\Validator\DateVisitor;
use Kynx\Laminas\FormShape\Validator\DigitsVisitor as DigitsValidatorVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitorFactory;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitor;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitorFactory;
use Kynx\Laminas\FormShape\Validator\HexVisitor;
use Kynx\Laminas\FormShape\Validator\InArrayVisitor;
use Kynx\Laminas\FormShape\Validator\InArrayVisitorFactory;
use Kynx\Laminas\FormShape\Validator\IsbnVisitor;
use Kynx\Laminas\FormShape\Validator\IsCountableVisitor;
use Kynx\Laminas\FormShape\Validator\IsInstanceOfVisitor;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\Validator\RegexVisitor;
use Kynx\Laminas\FormShape\Validator\RegexVisitorFactory;
use Kynx\Laminas\FormShape\Validator\Sitemap\PriorityVisitor;
use Kynx\Laminas\FormShape\Validator\StepVisitor;
use Kynx\Laminas\FormShape\Validator\StringLengthVisitor;
use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitor;
use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitorFactory;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;
use Laminas\ServiceManager\ConfigInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-type FilterVisitorList = list<class-string<FilterVisitorInterface>>
 * @psalm-type ValidatorVisitorList = list<class-string<ValidatorVisitorInterface>>
 * @psalm-type InputVisitorArray = array<class-string<InputInterface>, class-string<InputVisitorInterface>>
 * @psalm-type FormShapeArray = array{
 *      indent: string,
 *      max-string-length: ?int,
 *      literal-limit: ?int,
 *      filter-visitors: FilterVisitorList,
 *      validator-visitors: ValidatorVisitorList,
 *      input-visitors: InputVisitorArray,
 *      filter?: array<string, mixed>,
 *      validator: array<string, mixed>,
 * }
 * @psalm-type FormShapeConfigurationArray = array{
 *     laminas-cli: array,
 *     laminas-form-shape: FormShapeArray,
 *     dependencies: ServiceManagerConfigurationType,
 * }
 */
final readonly class ConfigProvider
{
    /**
     * @return FormShapeConfigurationArray
     */
    public function __invoke(): array
    {
        return [
            'laminas-cli'        => $this->getCliConfig(),
            'laminas-form-shape' => $this->getLaminasFormShapeConfig(),
            'dependencies'       => $this->getDependencyConfig(),
        ];
    }

    private function getCliConfig(): array
    {
        return [
            'commands' => [
                'form:shape' => FormShapeCommand::class,
            ],
        ];
    }

    /**
     * @return FormShapeArray
     */
    private function getLaminasFormShapeConfig(): array
    {
        return [
            'indent'             => '    ',
            'max-string-length'  => null,
            'literal-limit'      => null,
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
                CsrfVisitor::class,
                DateStepVisitor::class,
                DateVisitor::class,
                DigitsValidatorVisitor::class,
                ExplodeVisitor::class,
                FileValidatorVisitor::class,
                HexVisitor::class,
                InArrayVisitor::class,
                IsCountableVisitor::class,
                IsInstanceOfVisitor::class,
                IsbnVisitor::class,
                NonEmptyStringVisitor::class,
                NotEmptyVisitor::class,
                PriorityVisitor::class,
                RegexVisitor::class,
                StepVisitor::class,
                StringLengthVisitor::class,
            ],
            'input-visitors'     => [
                Input::class => InputVisitor::class,
            ],
            'filter'             => [
                'allow-list' => [
                    'allow-empty-list'     => true,
                ],
            ],
            'validator'          => [
                'in-array' => [
                    'allow-empty-haystack' => true,
                ],
                'regex'    => [
                    'patterns' => [
                        /* @link \Laminas\Form\Element\Color */
                        '/^#[0-9a-fA-F]{6}$/' => [
                            TNonEmptyString::class
                        ],
                        /* @link \Laminas\Form\Element\Email */
                        '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/' => [
                            TNonEmptyString::class,
                        ],
                        /* @link \Laminas\Form\Element\Month */
                        '/^[0-9]{4}\-(0[1-9]|1[012])$/' => [
                            TNonEmptyString::class,
                        ],
                        /* @link \Laminas\Form\Element\MonthSelect */
                        '/^[0-9]{4}\-(0?[1-9]|1[012])$/' => [
                            TNonEmptyString::class,
                        ],
                        /* @link \Laminas\Form\Element\Number */
                        '(^-?\d*(\.\d+)?$)' => [
                            TFloat::class,
                            TInt::class,
                            TNumericString::class,
                        ],
                        /* @link \Laminas\Form\Element\Tel */
                        "/^[^\r\n]*$/" => [
                            TString::class,
                        ],
                        /* @link \Laminas\Form\Element\Week */
                        '/^[0-9]{4}\-W[0-9]{2}$/' => [
                            TNonEmptyString::class,
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
            'aliases'   => [
                FormVisitorInterface::class        => FormVisitor::class,
                InputFilterVisitorInterface::class => InputFilterVisitor::class,
            ],
            'factories' => [
                AllowListVisitor::class       => AllowListVisitorFactory::class,
                ExplodeVisitor::class         => ExplodeVisitorFactory::class,
                FileValidatorVisitor::class   => FileValidatorVisitorFactory::class,
                FormReader::class             => FormReaderFactory::class,
                FormShapeCommand::class       => FormShapeCommandFactory::class,
                FormVisitor::class            => FormVisitorFactory::class,
                InArrayVisitor::class         => InArrayVisitorFactory::class,
                InputFilterVisitor::class     => InputFilterVisitorFactory::class,
                InputVisitor::class           => InputVisitorFactory::class,
                InputVisitorManager::class    => InputVisitorManagerFactory::class,
                NonEmptyStringVisitor::class  => NonEmptyStringVisitorFactory::class,
                RegexVisitor::class           => RegexVisitorFactory::class,
                UnionDecorator::class         => UnionDecoratorFactory::class,
            ],
        ];
    }
}

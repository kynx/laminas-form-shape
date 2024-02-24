<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Command\PsalmTypeCommand;
use Kynx\Laminas\FormShape\Command\PsalmTypeCommandFactory;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinter;
use Kynx\Laminas\FormShape\Decorator\PrettyPrinterFactory;
use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\Filter\AllowListVisitorFactory;
use Kynx\Laminas\FormShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormShape\Filter\DigitsVisitor as DigitsFilterVisitor;
use Kynx\Laminas\FormShape\Filter\InflectorVisitor;
use Kynx\Laminas\FormShape\Filter\ToFloatVisitor;
use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormShape\Filter\ToNullVisitor;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Form\FormProcessor;
use Kynx\Laminas\FormShape\Form\FormProcessorFactory;
use Kynx\Laminas\FormShape\Form\FormVisitor;
use Kynx\Laminas\FormShape\Form\FormVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\ArrayInputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitor;
use Kynx\Laminas\FormShape\InputFilter\CollectionInputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputFilterVisitorFactory;
use Kynx\Laminas\FormShape\InputFilter\InputVisitor;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorFactory;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Locator\FormLocator;
use Kynx\Laminas\FormShape\Locator\FormLocatorFactory;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeNamer;
use Kynx\Laminas\FormShape\Psalm\TypeNamerFactory;
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
use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitor;
use Kynx\Laminas\FormShape\Validator\NonEmptyStringVisitorFactory;
use Kynx\Laminas\FormShape\Validator\NotEmptyVisitor;
use Kynx\Laminas\FormShape\Validator\RegexVisitor;
use Kynx\Laminas\FormShape\Validator\RegexVisitorFactory;
use Kynx\Laminas\FormShape\Validator\Sitemap\PriorityVisitor;
use Kynx\Laminas\FormShape\Validator\StepVisitor;
use Kynx\Laminas\FormShape\Validator\StringLengthVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Kynx\Laminas\FormShape\Writer\CodeGeneratorInterface;
use Kynx\Laminas\FormShape\Writer\FileWriter;
use Kynx\Laminas\FormShape\Writer\FileWriterFactory;
use Kynx\Laminas\FormShape\Writer\NetteCodeGenerator;
use Kynx\Laminas\FormShape\Writer\NetteCodeGeneratorFactory;
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
 * @psalm-type InputVisitorArray = list<class-string<InputVisitorInterface>>
 * @psalm-type FormShapeArray = array{
 *      indent: string,
 *      max-string-length: ?int,
 *      literal-limit: ?int,
 *      type-name-template: string,
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
                'form:psalm-type' => PsalmTypeCommand::class,
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
            'type-name-template' => 'T{shortName}Data',
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
                ArrayInputVisitor::class,
                CollectionInputVisitor::class,
                InputVisitor::class,
            ],
            'filter'             => [
                'allow-list' => [
                    'allow-empty-list' => true,
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
                            TNonEmptyString::class,
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
                CodeGeneratorInterface::class      => NetteCodeGenerator::class,
                DecoratorInterface::class          => PrettyPrinter::class,
                FormLocatorInterface::class        => FormLocator::class,
                InputFilterVisitorInterface::class => InputFilterVisitor::class,
                TypeNamerInterface::class          => TypeNamer::class,
            ],
            'factories' => [
                AllowListVisitor::class       => AllowListVisitorFactory::class,
                ArrayInputVisitor::class      => ArrayInputVisitorFactory::class,
                CollectionInputVisitor::class => CollectionInputVisitorFactory::class,
                ExplodeVisitor::class         => ExplodeVisitorFactory::class,
                FileValidatorVisitor::class   => FileValidatorVisitorFactory::class,
                FormLocator::class            => FormLocatorFactory::class,
                FormProcessor::class          => FormProcessorFactory::class,
                NetteCodeGenerator::class     => NetteCodeGeneratorFactory::class,
                PrettyPrinter::class          => PrettyPrinterFactory::class,
                PsalmTypeCommand::class       => PsalmTypeCommandFactory::class,
                FileWriter::class             => FileWriterFactory::class,
                FormVisitor::class            => FormVisitorFactory::class,
                InArrayVisitor::class         => InArrayVisitorFactory::class,
                InputFilterVisitor::class     => InputFilterVisitorFactory::class,
                InputVisitor::class           => InputVisitorFactory::class,
                NonEmptyStringVisitor::class  => NonEmptyStringVisitorFactory::class,
                RegexVisitor::class           => RegexVisitorFactory::class,
                TypeNamer::class              => TypeNamerFactory::class,
            ],
        ];
    }
}

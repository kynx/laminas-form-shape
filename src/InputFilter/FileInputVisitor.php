<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\Validator\FileValidatorVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\Callback;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Psr\Http\Message\UploadedFileInterface;

use function array_filter;
use function array_map;
use function array_unshift;
use function is_callable;

final readonly class FileInputVisitor implements InputVisitorInterface
{
    /**
     * @param array<FilterVisitorInterface> $filterVisitors
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     */
    public function __construct(
        private FileInputStyle $style,
        private array $filterVisitors,
        private array $validatorVisitors
    ) {
    }

    public function visit(InputInterface $input): ?Union
    {
        if (! $input instanceof FileInput) {
            return null;
        }

        $initial = new Union($this->getInitialTypes());
        $union   = $initial->getBuilder()->freeze();

        $validators = $this->prependUploadValidator($input, array_map(
            static fn (array $queueItem): ValidatorInterface => $queueItem['instance'],
            $input->getValidatorChain()->getValidators()
        ));

        foreach ($validators as $validator) {
            $union = $this->visitValidators($validator, $union);
        }

        if (! $input->continueIfEmpty() && ($input->allowEmpty() || ! $input->isRequired())) {
            $union = TypeUtil::widen($union, $initial);
        }

        foreach ($input->getFilterChain()->getIterator() as $filter) {
            if (is_callable($filter) && ! $filter instanceof FilterInterface) {
                $filter = new Callback($filter);
            }

            $union = $this->visitFilters($filter, $union);
        }

        if ($input->hasFallback()) {
            $union = Type::combineUnionTypes($union, TypeUtil::toStrictUnion($input->getFallbackValue()));
        }

        if ($union->getAtomicTypes() === []) {
            throw InputVisitorException::cannotGetInputType($input);
        }

        return $union;
    }

    /**
     * @return non-empty-array<Atomic>
     */
    private function getInitialTypes(): array
    {
        $types = [new TNull(), new TString()];
        if ($this->style === FileInputStyle::Laminas || $this->style === FileInputStyle::Both) {
            $types[] = FileValidatorVisitor::getUploadArray();
        }
        if ($this->style === FileInputStyle::Psr7 || $this->style === FileInputStyle::Both) {
            $types[] = new TNamedObject(UploadedFileInterface::class);
        }

        return $types;
    }

    /**
     * @param array<ValidatorInterface> $validators
     * @return array<ValidatorInterface>
     */
    private function prependUploadValidator(FileInput $input, array $validators): array
    {
        if (! $input->getAutoPrependUploadValidator()) {
            return $validators;
        }

        $hasUploadValidator = (bool) array_filter(
            $validators,
            static fn (ValidatorInterface $validator): bool => $validator instanceof UploadFile
        );

        if ($hasUploadValidator) {
            return $validators;
        }

        if (! $input->continueIfEmpty() && $input->isRequired() && ! $input->allowEmpty()) {
            array_unshift($validators, new UploadFile());
        }

        return $validators;
    }

    private function visitValidators(ValidatorInterface $validator, Union $union): Union
    {
        foreach ($this->validatorVisitors as $visitor) {
            $union = $visitor->visit($validator, $union);
        }

        return $union;
    }

    private function visitFilters(FilterInterface $filter, Union $union): Union
    {
        foreach ($this->filterVisitors as $visitor) {
            $union = $visitor->visit($filter, $union);
        }

        return $union;
    }
}

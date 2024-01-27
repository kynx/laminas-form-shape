<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\EmptyContextInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;

use function array_map;
use function array_unshift;
use function in_array;
use function is_bool;
use function is_int;
use function is_string;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class InputVisitor implements InputVisitorInterface
{
    /**
     * @param array<FilterVisitorInterface> $filterVisitors
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     */
    public function __construct(private array $filterVisitors, private array $validatorVisitors)
    {
    }

    public function getInputType(InputInterface $input): InputType
    {
        $types = [PsalmType::String];

        foreach ($input->getFilterChain()->getIterator() as $filter) {
            if (! $filter instanceof FilterInterface) {
                continue;
            }
            $types = $this->getFilterTypes($filter, $types);
        }

        $validators = array_map(
            static fn (array $queueItem): ValidatorInterface => $queueItem['instance'],
            $input->getValidatorChain()->getValidators()
        );
        if (! $input->allowEmpty()) {
            array_unshift($validators, new NotEmpty());
        }

        foreach ($validators as $validator) {
            $types = $this->getValidatorTypes($validator, $types);
        }

        $continueIfEmpty = $input instanceof EmptyContextInterface && $input->continueIfEmpty();
        if (! $continueIfEmpty && ($input->allowEmpty() || ! $input->isRequired())) {
            $types[] = PsalmType::Null;
        }

        $hasFallback = $input instanceof Input && $input->hasFallback();
        if ($hasFallback) {
            $hasFallback = true;
            $types       = $this->addFallbackType($input->getFallbackValue(), $types);
        }

        $unique = [];
        foreach ($types as $type) {
            if (! in_array($type, $unique)) {
                $unique[] = $type;
            }
        }

        if ($types === []) {
            throw ArrayShapeException::cannotGetInputType($input);
        }

        return new InputType($input->getName(), $unique, $hasFallback || ! $input->isRequired());
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getFilterTypes(FilterInterface $filter, array $existing): array
    {
        $types = $existing;
        foreach ($this->filterVisitors as $visitor) {
            $types = $visitor->getTypes($filter, $types);
        }

        return $types;
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getValidatorTypes(ValidatorInterface $validator, array $existing): array
    {
        $types = $existing;
        foreach ($this->validatorVisitors as $visitor) {
            $types = $visitor->getTypes($validator, $types);
        }

        return $types;
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    private function addFallbackType(mixed $value, array $types): array
    {
        if (is_string($value) && ! PsalmType::hasStringType($types)) {
            $types[] = new Literal([$value]);
        }
        if (is_int($value) && ! PsalmType::hasIntType($types)) {
            $types[] = new Literal([$value]);
        }
        if ($value === true && ! PsalmType::hasBoolType($types)) {
            $types[] = PsalmType::True;
        }
        if ($value === false && ! PsalmType::hasBoolType($types)) {
            $types[] = PsalmType::False;
        }
        if (is_string($value) || is_int($value) || is_bool($value)) {
            return $types;
        }

        $types[] = PsalmType::fromPhpValue($value);
        return $types;
    }
}

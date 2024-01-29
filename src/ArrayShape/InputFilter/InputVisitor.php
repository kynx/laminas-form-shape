<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\ArrayShapeException;
use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\InputVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Shape\ElementShape;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
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
 * @psalm-import-type VisitedArray from TypeUtil
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

    public function visit(InputInterface $input): ElementShape
    {
        $types = [PsalmType::Null, PsalmType::String];

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

        $continueIfEmpty = $input instanceof EmptyContextInterface && $input->continueIfEmpty();
        /**
         * There's some weirdness here: on the default `Text` element, upstream `''` validates, but `' '` fails. So
         * while I _think_ this should be `! $continueIfEmpty && ($input->isRequired() || ! $input->allowEmpty())`, it
         * can't be. And I shudder to think of the mayhem it would cause if I raised it as a bug ;)
         */
        if (! $continueIfEmpty && $input->isRequired() && ! $input->allowEmpty()) {
            array_unshift($validators, new NotEmpty());
        }

        foreach ($validators as $validator) {
            $types = $this->getValidatorTypes($validator, $types);
        }

        if (! $continueIfEmpty && ($input->allowEmpty() || ! $input->isRequired())) {
            $types   = TypeUtil::replaceStringTypes($types, [PsalmType::String]);
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

        return new ElementShape($input->getName(), $unique, $hasFallback || ! $input->isRequired());
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getFilterTypes(FilterInterface $filter, array $existing): array
    {
        $types = $existing;
        foreach ($this->filterVisitors as $visitor) {
            $types = $visitor->visit($filter, $types);
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
            $types = $visitor->visit($validator, $types);
        }

        return $types;
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    private function addFallbackType(mixed $value, array $types): array
    {
        if (is_string($value) && ! TypeUtil::hasStringType($types)) {
            $types[] = new Literal([$value]);
        }
        if (is_int($value) && ! TypeUtil::hasIntType($types)) {
            $types[] = new Literal([$value]);
        }
        if ($value === true && ! TypeUtil::hasBoolType($types)) {
            $types[] = PsalmType::True;
        }
        if ($value === false && ! TypeUtil::hasBoolType($types)) {
            $types[] = PsalmType::False;
        }
        if (is_string($value) || is_int($value) || is_bool($value)) {
            return $types;
        }

        $types[] = TypeUtil::fromPhpValue($value);
        return $types;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
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
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class InputParser implements InputParserInterface
{
    /**
     * @param array<FilterParserInterface> $filterParsers
     * @param array<ValidatorParserInterface> $validatorParsers
     */
    public function __construct(private array $filterParsers, private array $validatorParsers)
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
            throw ArrayShapeException::cannotParseInputType($input);
        }

        return new InputType($input->getName(), $unique, $hasFallback || ! $input->isRequired());
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getFilterTypes(FilterInterface $filter, array $existing): array
    {
        $types = $existing;
        foreach ($this->filterParsers as $parser) {
            $types = $parser->getTypes($filter, $types);
        }

        return $types;
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getValidatorTypes(ValidatorInterface $validator, array $existing): array
    {
        $types = $existing;
        foreach ($this->validatorParsers as $parser) {
            $types = $parser->getTypes($validator, $types);
        }

        return $types;
    }

    /**
     * @param ParsedArray $types
     * @return ParsedArray
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

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\EmptyContextInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Union;

use function array_filter;
use function array_map;
use function array_unshift;

abstract readonly class AbstractInputVisitor implements InputVisitorInterface
{
    /**
     * @param array<FilterVisitorInterface> $filterVisitors
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     */
    public function __construct(protected array $filterVisitors, protected array $validatorVisitors)
    {
    }

    protected function visitInput(InputInterface $input, Union $initial): Union
    {
        $union = $initial->getBuilder()->freeze();

        foreach ($input->getFilterChain()->getIterator() as $filter) {
            if (! $filter instanceof FilterInterface) {
                continue;
            }
            $union = $this->visitFilters($filter, $union);
        }

        $validators = $this->prependNotEmptyValidator($input, array_map(
            static fn (array $queueItem): ValidatorInterface => $queueItem['instance'],
            $input->getValidatorChain()->getValidators()
        ));

        foreach ($validators as $validator) {
            $union = $this->visitValidators($validator, $union);
        }

        if (! $this->continueIfEmpty($input) && ($input->allowEmpty() || ! $input->isRequired())) {
            $union = TypeUtil::widen($union, $initial);
        }

        return $union;
    }

    /**
     * @psalm-assert-if-true Input $input
     */
    protected function hasFallback(InputInterface $input): bool
    {
        return $input instanceof Input && $input->hasFallback();
    }

    private function continueIfEmpty(InputInterface $input): bool
    {
        return $input instanceof EmptyContextInterface && $input->continueIfEmpty();
    }

    /**
     * @param array<ValidatorInterface> $validators
     * @return array<ValidatorInterface>
     */
    private function prependNotEmptyValidator(InputInterface $input, array $validators): array
    {
        $hasNotEmpty = (bool) array_filter(
            $validators,
            static fn (ValidatorInterface $validator): bool => $validator instanceof NotEmpty
        );
        if ($hasNotEmpty) {
            return $validators;
        }

        /**
         * There's some weirdness here: on the default `Text` element, upstream `''` validates, but `' '` fails. So
         * while I _think_ this should be `! $continueIfEmpty && ($input->isRequired() || ! $input->allowEmpty())`, it
         * can't be. And I shudder to think of the mayhem it would cause if I raised it as a bug ;)
         */
        if (! $this->continueIfEmpty($input) && $input->isRequired() && ! $input->allowEmpty()) {
            array_unshift($validators, new NotEmpty());
        }

        return $validators;
    }

    private function visitFilters(FilterInterface $filter, Union $union): Union
    {
        foreach ($this->filterVisitors as $visitor) {
            $union = $visitor->visit($filter, $union);
        }

        return $union;
    }

    private function visitValidators(ValidatorInterface $validator, Union $union): Union
    {
        foreach ($this->validatorVisitors as $visitor) {
            $union = $visitor->visit($validator, $union);
        }

        return $union;
    }
}

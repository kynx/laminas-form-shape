<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\EmptyContextInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

use function array_map;
use function array_unshift;

final readonly class InputVisitor implements InputVisitorInterface
{
    /**
     * @param array<FilterVisitorInterface> $filterVisitors
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     */
    public function __construct(private array $filterVisitors, private array $validatorVisitors)
    {
    }

    public function visit(InputInterface $input): Union
    {
        $hasFallback = $input instanceof Input && $input->hasFallback();
        $union       = new Union([new TNull(), new TString()]);

        foreach ($input->getFilterChain()->getIterator() as $filter) {
            if (! $filter instanceof FilterInterface) {
                continue;
            }
            $union = $this->visitFilters($filter, $union);
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
            $union = $this->visitValidators($validator, $union);
        }

        if (! $continueIfEmpty && ($input->allowEmpty() || ! $input->isRequired())) {
            $union = TypeUtil::widen($union, new Union([new TString(), new TNull()]));
        }

        if ($input instanceof Input && $hasFallback) {
            $union = Type::combineUnionTypes($union, TypeUtil::toStrictUnion($input->getFallbackValue()));
        }

        if ($union->getAtomicTypes() === []) {
            throw InputVisitorException::cannotGetInputType($input);
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

    private function visitValidators(ValidatorInterface $validator, Union $union): Union
    {
        foreach ($this->validatorVisitors as $visitor) {
            $union = $visitor->visit($validator, $union);
        }

        return $union;
    }
}

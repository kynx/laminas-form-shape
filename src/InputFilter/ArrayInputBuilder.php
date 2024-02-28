<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\EmptyContextInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;

final readonly class ArrayInputBuilder
{
    public static function create(InputInterface $input): ArrayInput
    {
        $arrayInput = new ArrayInput($input->getName());
        $arrayInput->setRequired($input->isRequired());
        $arrayInput->setAllowEmpty($input->allowEmpty());
        $arrayInput->setContinueIfEmpty(self::continueIfEmpty($input));
        $arrayInput->setFilterChain(clone $input->getFilterChain());
        $arrayInput->setValidatorChain(clone $input->getValidatorChain());
        $arrayInput->setValue((array) $input->getRawValue());

        if ($input instanceof Input && $input->hasFallback()) {
            $arrayInput->setFallbackValue($input->getFallbackValue());
        }

        return $arrayInput;
    }

    public static function continueIfEmpty(InputInterface $input): bool
    {
        return $input instanceof EmptyContextInterface && $input->continueIfEmpty();
    }
}

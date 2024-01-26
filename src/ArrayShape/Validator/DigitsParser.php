<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;

final readonly class DigitsParser implements ValidatorParserInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Digits) {
            return $existing;
        }

        $types = [PsalmType::Int, PsalmType::Float, PsalmType::NumericString];
        if (PsalmType::hasStringType($existing)) {
            $existing[] = PsalmType::NumericString;
        }

        return PsalmType::filter($existing, $types);
    }
}

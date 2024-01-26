<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Step;
use Laminas\Validator\ValidatorInterface;

final readonly class StepParser implements ValidatorParserInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Step) {
            return $existing;
        }

        // Validator accepts strings, but floor() and round() only accept int|float
        return PsalmType::filter($existing, [
            PsalmType::Float,
            PsalmType::Int,
            PsalmType::NegativeInt,
            PsalmType::PositiveInt,
        ]);
    }
}

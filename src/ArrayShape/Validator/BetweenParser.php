<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;

use function is_numeric;

final readonly class BetweenParser implements ValidatorParserInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Between) {
            return $existing;
        }

        if (is_numeric($validator->getMin()) && is_numeric($validator->getMax())) {
            $types    = [PsalmType::Int, PsalmType::Float, PsalmType::NumericString];
            $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NumericString]);
        } else {
            $types = [PsalmType::String];
        }

        return PsalmType::filter($existing, $types);
    }
}

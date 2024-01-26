<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;

final readonly class StringLengthParser implements ValidatorParserInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof StringLength) {
            return $existing;
        }

        if ($validator->getMin() > 0) {
            $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);
        }

        return PsalmType::filter($existing, [PsalmType::String, PsalmType::NonEmptyString]);
    }
}

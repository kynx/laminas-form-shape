<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\Timezone;
use Laminas\Validator\ValidatorInterface;

final readonly class TimezoneVisitor implements ValidatorVisitorInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Timezone) {
            return $existing;
        }

        $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return PsalmType::filter($existing, [PsalmType::NonEmptyString]);
    }
}

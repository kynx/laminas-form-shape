<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\Date;
use Laminas\Validator\ValidatorInterface;

final readonly class DateVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Date) {
            return $existing;
        }

        $existing = PsalmType::replaceArrayTypes($existing, [PsalmType::NonEmptyArray]);
        $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return PsalmType::filter($existing, [
            new ClassString(DateTime::class),
            new ClassString(DateTimeImmutable::class),
            PsalmType::Float,
            PsalmType::Int,
            PsalmType::NegativeInt,
            PsalmType::NonEmptyArray,
            PsalmType::NonEmptyString,
            PsalmType::PositiveInt,
        ]);
    }
}

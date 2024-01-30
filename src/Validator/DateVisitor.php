<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormShape\Type\ClassString;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Date;
use Laminas\Validator\ValidatorInterface;

final readonly class DateVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Date) {
            return $existing;
        }

        $existing = TypeUtil::replaceArrayTypes($existing, [PsalmType::NonEmptyArray]);
        $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return TypeUtil::filter($existing, [
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

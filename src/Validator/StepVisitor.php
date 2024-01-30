<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Step;
use Laminas\Validator\ValidatorInterface;

final readonly class StepVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Step) {
            return $existing;
        }

        // `floor()` and `round()` _do_ accept numeric strings!
        $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NumericString]);

        return TypeUtil::filter($existing, [
            PsalmType::Float,
            PsalmType::Int,
            PsalmType::NegativeInt,
            PsalmType::NumericString,
            PsalmType::PositiveInt,
        ]);
    }
}

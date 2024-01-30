<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Between;
use Laminas\Validator\ValidatorInterface;

use function is_numeric;

final readonly class BetweenVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Between) {
            return $existing;
        }

        if (is_numeric($validator->getMin()) && is_numeric($validator->getMax())) {
            $types    = [PsalmType::Int, PsalmType::Float, PsalmType::NumericString];
            $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NumericString]);
        } else {
            $types = [PsalmType::String];
        }

        return TypeUtil::filter($existing, $types);
    }
}

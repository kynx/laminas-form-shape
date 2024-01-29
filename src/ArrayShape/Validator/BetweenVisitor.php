<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
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

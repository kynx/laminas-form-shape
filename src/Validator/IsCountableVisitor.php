<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Countable;
use Kynx\Laminas\FormShape\Type\ClassString;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsCountable;
use Laminas\Validator\ValidatorInterface;

final readonly class IsCountableVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof IsCountable) {
            return $existing;
        }

        if (TypeUtil::hasArrayType($existing)) {
            return [PsalmType::Array, new ClassString(Countable::class)];
        }

        return [];
    }
}

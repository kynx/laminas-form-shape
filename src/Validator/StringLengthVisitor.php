<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;

final readonly class StringLengthVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof StringLength) {
            return $existing;
        }

        if ($validator->getMin() > 0) {
            $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NonEmptyString]);
        }

        return TypeUtil::filter($existing, [PsalmType::String, PsalmType::NonEmptyString]);
    }
}

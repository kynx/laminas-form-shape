<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Csrf;
use Laminas\Validator\ValidatorInterface;

final readonly class CsrfVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Csrf) {
            return $existing;
        }

        $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return TypeUtil::filter($existing, [PsalmType::NonEmptyString]);
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Isbn;
use Laminas\Validator\ValidatorInterface;

final readonly class IsbnVisitor implements ValidatorVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Isbn) {
            return $existing;
        }

        return TypeUtil::filter($existing, [
            PsalmType::Int,
            PsalmType::PositiveInt,
            PsalmType::String,
            PsalmType::NonEmptyString,
        ]);
    }
}

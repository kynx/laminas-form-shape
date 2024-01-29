<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
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

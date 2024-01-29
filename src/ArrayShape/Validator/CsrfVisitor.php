<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
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

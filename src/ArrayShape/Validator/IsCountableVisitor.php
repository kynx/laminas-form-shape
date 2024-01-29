<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Countable;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
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

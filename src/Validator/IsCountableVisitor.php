<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Countable;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\IsCountable;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;

final readonly class IsCountableVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof IsCountable) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([
            new TArray([Type::getArrayKey(), Type::getMixed()]),
            new TNamedObject(Countable::class),
        ]));
    }
}

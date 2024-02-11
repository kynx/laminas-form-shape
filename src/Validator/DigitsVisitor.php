<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Union;

final readonly class DigitsVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Digits) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([
            new TFloat(),
            new TInt(),
            new TNumericString(),
        ]));
    }
}

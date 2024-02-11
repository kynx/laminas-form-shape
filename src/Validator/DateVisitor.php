<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use DateTimeInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Date;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Union;

final readonly class DateVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Date) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([
            new TFloat(),
            new TInt(),
            new TNamedObject(DateTimeInterface::class),
            new TNonEmptyArray([Type::getArrayKey(), new Union([new TNumericString(), new TInt()])]),
            new TNonEmptyString(),
        ]));
    }
}

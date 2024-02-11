<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Hex;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Union;

final readonly class HexVisitor implements ValidatorVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Hex) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([
            new TInt(),
            new TNonEmptyString(),
        ]));
    }
}

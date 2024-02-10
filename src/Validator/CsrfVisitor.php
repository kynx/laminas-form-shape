<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Csrf;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Union;

final readonly class CsrfVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Csrf) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([new TNonEmptyString()]));
    }
}

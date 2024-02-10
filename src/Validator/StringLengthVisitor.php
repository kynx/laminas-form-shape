<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class StringLengthVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof StringLength) {
            return $previous;
        }

        if ($validator->getMin() > 0) {
            return TypeUtil::narrow($previous, new Union([new TNonEmptyString()]));
        }

        return TypeUtil::narrow($previous, new Union([new TString()]));
    }
}

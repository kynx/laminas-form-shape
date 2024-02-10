<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Isbn;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Union;

final readonly class IsbnVisitor implements ValidatorVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Isbn) {
            return $previous;
        }

        $min = match ($validator->getType()) {
            Isbn::ISBN13 => 9780000000000,
            default      => 1000000000,
        };
        $max = match ($validator->getType()) {
            Isbn::ISBN10 => 9999999999,
            default      => 9799999999999,
        };

        return TypeUtil::narrow($previous, new Union([
            new TIntRange($min, $max),
            new TNonEmptyString(),
        ]));
    }
}

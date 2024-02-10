<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator\Sitemap;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Union;

final readonly class PriorityVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Priority) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([
            new TFloat(),
            new TIntRange(0, 1),
            new TNumericString(),
        ]));
    }
}

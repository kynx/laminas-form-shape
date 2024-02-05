<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToFloat;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Union;

final readonly class ToFloatVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof ToFloat) {
            return $previous;
        }

        return TypeUtil::widen($previous, new Union([new TFloat()]));
    }
}

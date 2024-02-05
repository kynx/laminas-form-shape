<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\Inflector;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class InflectorVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof Inflector) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union([new TString()]));
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class DigitsVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof Digits) {
            return $previous;
        }

        $builder = $previous->getBuilder();

        $builder->removeType((new TFloat())->getKey());
        $builder->removeType((new TInt())->getKey());
        $builder->removeType((new TString())->getKey());

        if ($builder->equals($previous->getBuilder())) {
            return $previous;
        }

        $builder->addType(new TNumericString());

        return $builder->freeze();
    }
}

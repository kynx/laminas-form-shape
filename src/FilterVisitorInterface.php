<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Laminas\Filter\FilterInterface;
use Psalm\Type\Union;

interface FilterVisitorInterface
{
    /**
     * Returns union of types that would result from running given filter
     *
     * If implementations cannot handle the filter they _must_ return the existing union unaltered.
     */
    public function visit(FilterInterface $filter, Union $previous): Union;
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Type\TypeUtil;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
interface FilterVisitorInterface
{
    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    public function visit(FilterInterface $filter, array $existing): array;
}

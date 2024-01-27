<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
interface FilterVisitorInterface
{
    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    public function getTypes(FilterInterface $filter, array $existing): array;
}

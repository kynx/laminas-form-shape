<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\Inflector;

final readonly class InflectorVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof Inflector) {
            return $existing;
        }

        return [PsalmType::String];
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToFloat;

final readonly class ToFloatVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof ToFloat) {
            return $existing;
        }

        $existing[] = PsalmType::Float;
        return $existing;
    }
}

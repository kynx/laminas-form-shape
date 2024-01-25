<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToInt;

final readonly class ToIntParser implements FilterParserInterface
{
    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof ToInt) {
            return $existing;
        }

        $existing[] = PsalmType::Int;
        return $existing;
    }
}

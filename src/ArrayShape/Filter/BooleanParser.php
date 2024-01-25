<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;

final readonly class BooleanParser implements FilterParserInterface
{
    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof Boolean) {
            return $existing;
        }

        if ($filter->getCasting()) {
            return [PsalmType::Bool];
        }

        $type = $filter->getType();
        if ($type & Boolean::TYPE_NULL) {
            $existing = PsalmType::removeType(PsalmType::Null, $existing);
        }

        $existing[] = PsalmType::Bool;
        return $existing;
    }
}

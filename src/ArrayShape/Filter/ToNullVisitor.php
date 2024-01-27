<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToNull;

final readonly class ToNullVisitor implements FilterVisitorInterface
{
    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof ToNull) {
            return $existing;
        }

        $type = $filter->getType();

        if ($type === 0) {
            return $existing;
        }

        if ($type & ToNull::TYPE_STRING) {
            $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);
        }
        if ($type & ToNull::TYPE_INTEGER) {
            $existing = PsalmType::replaceIntTypes($existing, [PsalmType::NegativeInt, PsalmType::PositiveInt]);
        }
        if ($type & ToNull::TYPE_BOOLEAN) {
            $existing = PsalmType::replaceBoolTypes($existing, [PsalmType::True]);
        }

        $existing[] = PsalmType::Null;
        return $existing;
    }
}

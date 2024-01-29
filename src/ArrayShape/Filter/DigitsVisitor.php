<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
final readonly class DigitsVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof Digits) {
            return $existing;
        }

        if (! $this->hasDigitType($existing)) {
            return $existing;
        }

        $existing = TypeUtil::removeIntTypes($existing);
        $existing = TypeUtil::removeType(PsalmType::Float, $existing);

        if (! TypeUtil::hasStringType($existing)) {
            $existing[] = PsalmType::NumericString;
        }

        return $existing;
    }

    /**
     * @param VisitedArray $existing
     */
    private function hasDigitType(array $existing): bool
    {
        return TypeUtil::hasIntType($existing)
            || TypeUtil::hasStringType($existing)
            || TypeUtil::hasType(PsalmType::Float, $existing);
    }
}

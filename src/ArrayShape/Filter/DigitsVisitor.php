<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class DigitsVisitor implements FilterVisitorInterface
{
    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof Digits) {
            return $existing;
        }

        if (! $this->hasDigitType($existing)) {
            return $existing;
        }

        $existing = PsalmType::removeIntTypes($existing);
        $existing = PsalmType::removeType(PsalmType::Float, $existing);

        if (! PsalmType::hasStringType($existing)) {
            $existing[] = PsalmType::NumericString;
        }

        return $existing;
    }

    /**
     * @param VisitedArray $existing
     */
    private function hasDigitType(array $existing): bool
    {
        return PsalmType::hasIntType($existing)
            || PsalmType::hasStringType($existing)
            || PsalmType::hasType(PsalmType::Float, $existing);
    }
}

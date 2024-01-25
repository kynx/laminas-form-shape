<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Digits;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class DigitsParser implements FilterParserInterface
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
     * @param ParsedArray $existing
     */
    private function hasDigitType(array $existing): bool
    {
        return PsalmType::hasIntType($existing)
            || PsalmType::hasStringType($existing)
            || PsalmType::hasType(PsalmType::Float, $existing);
    }
}

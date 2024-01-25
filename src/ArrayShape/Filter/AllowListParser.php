<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterParserInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use Laminas\Filter\FilterInterface;

use function assert;
use function count;
use function in_array;
use function is_scalar;
use function is_string;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class AllowListParser implements FilterParserInterface
{
    public const DEFAULT_MAX_LITERALS = 10;

    public function __construct(private int $maxLiterals = self::DEFAULT_MAX_LITERALS)
    {
    }

    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof AllowList) {
            return $existing;
        }

        if (count($filter->getList()) > $this->maxLiterals) {
            return $filter->getStrict() === true
                ? $this->getStrictTypes($filter->getList(), $existing)
                : $this->getLaxTypes($filter->getList(), $existing);
        }

        return [
            $filter->getStrict() === true
                ? $this->getStrictLiteral($filter->getList(), $existing)
                : $this->getLaxLiteral($filter->getList(), $existing),
            PsalmType::Null,
        ];
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getStrictTypes(array $list, array $existing): array
    {
        $types = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow));
            $types = $this->appendUnique(PsalmType::fromPhpValue($allow), $types, $existing);
        }
        $types[] = PsalmType::Null;

        return $types;
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getLaxTypes(array $list, array $existing): array
    {
        $types = $this->getStrictTypes($list, $existing);
        return $this->appendUnique(PsalmType::String, $types, $existing);
    }

    /**
     * @param ParsedArray $existing
     */
    private function getStrictLiteral(array $list, array $existing): Literal
    {
        $values = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow));
            $type = PsalmType::fromPhpValue($allow);
            if (is_string($allow) && PsalmType::hasStringType($existing)) {
                $values[] = "'$allow'";
            } elseif (PsalmType::hasType($type, $existing)) {
                $values[] = $allow;
            }
        }

        return new Literal($values);
    }

    /**
     * @param ParsedArray $existing
     */
    private function getLaxLiteral(array $list, array $existing): Literal
    {
        $values = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow));
            $type = PsalmType::fromPhpValue($allow);
            if (PsalmType::hasStringType($existing)) {
                $values[] = "'$allow'";
            }

            if (! is_string($allow) && PsalmType::hasType($type, $existing)) {
                $values[] = $allow;
            }
        }

        return new Literal($values);
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function appendUnique(ClassString|PsalmType $type, array $types, array $existing): array
    {
        if (PsalmType::hasType($type, $existing) && ! in_array($type, $types)) {
            $types[] = $type;
        }

        return $types;
    }
}

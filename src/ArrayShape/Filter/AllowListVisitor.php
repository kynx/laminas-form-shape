<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use Laminas\Filter\FilterInterface;

use function assert;
use function count;
use function in_array;
use function is_int;
use function is_numeric;
use function is_scalar;
use function is_string;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class AllowListVisitor implements FilterVisitorInterface
{
    public const DEFAULT_MAX_LITERALS = 10;

    public function __construct(
        private bool $allowEmptyList = true,
        private int $maxLiterals = self::DEFAULT_MAX_LITERALS
    ) {
    }

    public function getTypes(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof AllowList) {
            return $existing;
        }

        $list = $filter->getList();
        if ($list === []) {
            return $this->allowEmptyList ? $existing : [PsalmType::Null];
        }

        $existing[] = PsalmType::Null;

        if (count($list) > $this->maxLiterals) {
            return $filter->getStrict() === true
                ? $this->getStrictTypes($list, $existing)
                : $this->getLaxTypes($list, $existing);
        }

        $types = $filter->getStrict() === true
            ? $this->getStrictLiteral($list, $existing)
            : $this->getLaxLiteral($list, $existing);
        return $this->appendUnique(PsalmType::Null, $types, $existing);
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getStrictTypes(array $list, array $existing): array
    {
        $types = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow) || $allow === null);
            $types = $this->appendUnique(PsalmType::fromPhpValue($allow), $types, $existing);
        }
        $types[] = PsalmType::Null;

        return $types;
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getLaxTypes(array $list, array $existing): array
    {
        $types = $this->getStrictTypes($list, $existing);
        return $this->appendUnique(PsalmType::String, $types, $existing);
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getStrictLiteral(array $list, array $existing): array
    {
        $types = $literals = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow) || $allow === null);
            $type = PsalmType::fromPhpValue($allow);
            if (is_string($allow) && PsalmType::hasStringType($existing)) {
                $literals[] = "$allow";
            } elseif (is_int($allow) && PsalmType::hasIntType($existing)) {
                $literals[] = $allow;
            } elseif (PsalmType::hasType($type, $existing)) {
                $types[] = $type;
            }
        }

        $types = $this->appendUnique(PsalmType::Null, $types, $existing);

        if ($literals !== []) {
            $types[] = new Literal($literals);
        }

        return $types;
    }

    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getLaxLiteral(array $list, array $existing): array
    {
        $types       = $literals = [];
        $numLiterals = 0;
        $numeric     = true;
        foreach ($list as $allow) {
            assert(is_scalar($allow) || $allow === null);
            $type = PsalmType::fromPhpValue($allow);
            if (is_int($allow) && PsalmType::hasIntType($existing)) {
                $literals[] = $allow;
            }
            if ((is_string($allow) || is_int($allow)) && PsalmType::hasStringType($existing)) {
                $literals[] = "$allow";
                $numLiterals++;
                continue;
            }
            if (PsalmType::hasType($type, $existing)) {
                $types[] = $type;
            }
            $numeric = $numeric && is_numeric($allow);
        }

        if (count($list) !== $numLiterals && PsalmType::hasStringType($existing)) {
            $types = $this->appendUnique(PsalmType::String, $types, $existing);
        }

        if ($numeric) {
            $types = PsalmType::replaceStringTypes($types, [PsalmType::NumericString]);
        }

        $types = $this->appendUnique(PsalmType::Null, $types, $existing);

        if ($literals !== []) {
            $types[] = new Literal($literals);
        }

        return $types;
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function appendUnique(ClassString|PsalmType $type, array $types, array $existing): array
    {
        if (PsalmType::hasType($type, $existing) && ! in_array($type, $types)) {
            $types[] = $type;
        }

        return $types;
    }
}

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
use function is_int;
use function is_scalar;
use function is_string;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class AllowListParser implements FilterParserInterface
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
     * @param ParsedArray $existing
     * @return ParsedArray
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
     * @return ParsedArray
     */
    private function getStrictLiteral(array $list, array $existing): array
    {
        $types = $literals = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow) || $allow === null);
            $type = PsalmType::fromPhpValue($allow);
            if (is_string($allow) && PsalmType::hasStringType($existing)) {
                $literals[] = "'$allow'";
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
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getLaxLiteral(array $list, array $existing): array
    {
        $types = $literals = [];
        foreach ($list as $allow) {
            assert(is_scalar($allow) || $allow === null);
            $type = PsalmType::fromPhpValue($allow);
            if (is_int($allow) && PsalmType::hasIntType($existing)) {
                $literals[] = $allow;
            }
            if ((is_string($allow) || is_int($allow)) && PsalmType::hasStringType($existing)) {
                $literals[] = "'$allow'";
                continue;
            }
            if (PsalmType::hasType($type, $existing)) {
                $types[] = $type;
            }
        }

        if ($types !== []) {
            $types = $this->appendUnique(PsalmType::String, $types, $existing);
        }
        $types = $this->appendUnique(PsalmType::Null, $types, $existing);

        if ($literals !== []) {
            $types[] = new Literal($literals);
        }

        return $types;
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

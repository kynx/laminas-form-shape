<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Iterator;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\InArray;
use Laminas\Validator\ValidatorInterface;

use function assert;
use function count;
use function in_array;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;
use function iterator_to_array;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class InArrayParser implements ValidatorParserInterface
{
    public const DEFAULT_MAX_LITERALS = 10;

    public function __construct(
        private bool $allowEmptyHaystack = true,
        private int $maxLiterals = self::DEFAULT_MAX_LITERALS
    ) {
    }

    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof InArray) {
            return $existing;
        }

        $haystack = $validator->getHaystack();
        if ($haystack instanceof Iterator) {
            $haystack = iterator_to_array($haystack);
        }
        assert(is_array($haystack));

        if ($haystack === [] && $this->allowEmptyHaystack) {
            return $existing;
        }

        if (count($haystack) > $this->maxLiterals) {
            return $validator->getStrict() === true
                ? $this->getStrictTypes($haystack, $existing)
                : $this->getLaxTypes($haystack, $existing);
        }

        return $validator->getStrict() === true
            ? $this->getStrictLiteral($haystack, $existing)
            : $this->getLaxLiteral($haystack, $existing);
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getStrictTypes(array $haystack, array $existing): array
    {
        $types = [];
        foreach ($haystack as $value) {
            assert(is_scalar($value) || $value === null);
            $types = $this->appendUnique(PsalmType::fromPhpValue($value), $types, $existing);
        }

        return $types;
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getLaxTypes(array $haystack, array $existing): array
    {
        $types = $this->getStrictTypes($haystack, $existing);
        return $this->appendUnique(PsalmType::String, $types, $existing);
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getStrictLiteral(array $haystack, array $existing): array
    {
        $types = $literals = [];
        foreach ($haystack as $value) {
            assert(is_scalar($value) || $value === null);
            $type = PsalmType::fromPhpValue($value);
            if (is_string($value) && PsalmType::hasStringType($existing)) {
                $literals[] = "'$value'";
            } elseif (is_int($value) && PsalmType::hasIntType($existing)) {
                $literals[] = $value;
            } elseif (PsalmType::hasType($type, $existing)) {
                $types[] = $type;
            }
        }
        if ($literals !== []) {
            $types[] = new Literal($literals);
        }

        return $types;
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getLaxLiteral(array $haystack, array $existing): array
    {
        $types = $literals = [];
        foreach ($haystack as $value) {
            assert(is_scalar($value) || $value === null);
            $type = PsalmType::fromPhpValue($value);
            if (is_int($value) && PsalmType::hasIntType($existing)) {
                $literals[] = $value;
            }
            if ((is_string($value) || is_int($value)) && PsalmType::hasStringType($existing)) {
                $literals[] = "'$value'";
                continue;
            }
            if (PsalmType::hasType($type, $existing)) {
                $types[] = $type;
            }
        }

        if ($types !== []) {
            $types = $this->appendUnique(PsalmType::String, $types, $existing);
        }

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

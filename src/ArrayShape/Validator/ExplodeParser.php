<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function array_merge;
use function array_unique;
use function sort;

use const SORT_STRING;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final readonly class ExplodeParser implements ValidatorParserInterface
{
    public const DEFAULT_ITEM_TYPES = [PsalmType::String, PsalmType::Null];

    /**
     * @param array<ValidatorParserInterface> $validatorParsers
     * @param list<PsalmType> $itemTypes
     */
    public function __construct(private array $validatorParsers, private array $itemTypes = self::DEFAULT_ITEM_TYPES)
    {
    }

    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Explode) {
            return $existing;
        }

        if (! $this->hasItems($validator, $existing)) {
            return $this->getItemTypes($validator, $existing);
        }

        $types = [];

        if (PsalmType::hasArrayType($existing)) {
            $itemTypes = $this->getItemTypes($validator, $this->itemTypes);
            $types[]   = new Generic(PsalmType::Array, $itemTypes);
        }
        if ($this->hasClassString(Traversable::class, $existing)) {
            $itemTypes = $this->getItemTypes($validator, $this->itemTypes);
            $types[]   = new Generic(new ClassString(Traversable::class), $itemTypes);
        }
        if ($this->hasExplodeableString($validator, $existing)) {
            $types[] = PsalmType::String;
        }

        return array_merge($types, $this->getItemTypes($validator, $existing));
    }

    /**
     * @param ParsedArray $existing
     */
    private function hasItems(Explode $explode, array $existing): bool
    {
        if (PsalmType::hasArrayType($existing) || $this->hasClassString(Traversable::class, $existing)) {
            return true;
        }

        return $this->hasExplodeableString($explode, $existing);
    }

    /**
     * @param ParsedArray $existing
     */
    private function hasExplodeableString(Explode $explode, array $existing): bool
    {
        /** @psalm-suppress  RedundantConditionGivenDocblockType There is nothing upstream to stop it being null */
        return PsalmType::hasStringType($existing) && $explode->getValueDelimiter() !== null;
    }

    /**
     * @param ParsedArray $existing
     */
    private function hasClassString(string $type, array $existing): bool
    {
        foreach ($existing as $test) {
            if ($test instanceof Generic) {
                $test = $test->type;
            }
            if ($test instanceof ClassString && $test->classString === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    private function getItemTypes(Explode $explode, array $existing): array
    {
        $types     = $existing;
        $validator = $explode->getValidator();
        if ($validator === null) {
            return $types;
        }

        foreach ($this->validatorParsers as $parser) {
            $types = $parser->getTypes($validator, $types);
        }

        $types = array_unique($types);
        sort($types, SORT_STRING);

        return $types;
    }
}

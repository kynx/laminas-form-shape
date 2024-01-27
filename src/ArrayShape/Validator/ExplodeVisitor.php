<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function array_merge;
use function array_unique;
use function sort;

use const SORT_STRING;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class ExplodeVisitor implements ValidatorVisitorInterface
{
    public const DEFAULT_ITEM_TYPES = [PsalmType::String, PsalmType::Null];

    /**
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     * @param list<PsalmType> $itemTypes
     */
    public function __construct(private array $validatorVisitors, private array $itemTypes = self::DEFAULT_ITEM_TYPES)
    {
    }

    public function visit(ValidatorInterface $validator, array $existing): array
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
     * @param VisitedArray $existing
     */
    private function hasItems(Explode $explode, array $existing): bool
    {
        if (PsalmType::hasArrayType($existing) || $this->hasClassString(Traversable::class, $existing)) {
            return true;
        }

        return $this->hasExplodeableString($explode, $existing);
    }

    /**
     * @param VisitedArray $existing
     */
    private function hasExplodeableString(Explode $explode, array $existing): bool
    {
        /** @psalm-suppress  RedundantConditionGivenDocblockType There is nothing upstream to stop it being null */
        return PsalmType::hasStringType($existing) && $explode->getValueDelimiter() !== null;
    }

    /**
     * @param VisitedArray $existing
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
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    private function getItemTypes(Explode $explode, array $existing): array
    {
        $types     = $existing;
        $validator = $explode->getValidator();
        if ($validator === null) {
            return $types;
        }

        foreach ($this->validatorVisitors as $visitor) {
            $types = $visitor->visit($validator, $types);
        }

        $types = array_unique($types);
        sort($types, SORT_STRING);

        return $types;
    }
}

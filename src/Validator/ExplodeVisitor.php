<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Explode;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Traversable;

use function array_filter;
use function array_map;
use function array_merge;
use function class_exists;
use function interface_exists;
use function is_a;

final readonly class ExplodeVisitor implements ValidatorVisitorInterface
{
    /**
     * @param array<ValidatorVisitorInterface> $validatorVisitors
     */
    public function __construct(private array $validatorVisitors)
    {
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Explode) {
            return $previous;
        }

        $delimiter = $validator->getValueDelimiter();
        $validator = $validator->getValidator();

        $narrow = array_merge(
            $this->visitArrays($validator, $previous),
            $this->visitKeyedArrays($validator, $previous),
            $this->visitTraversables($validator, $previous),
            $this->visitExisting($validator, $previous, $delimiter),
        );

        if ($narrow === []) {
            return $previous;
        }

        return TypeUtil::narrow($previous, new Union($narrow));
    }

    /**
     * @return array<TArray>
     */
    private function visitArrays(?ValidatorInterface $validator, Union $previous): array
    {
        /** @var array<TArray> $arrays */
        $arrays = array_filter(
            $previous->getAtomicTypes(),
            static fn (Atomic $type): bool => $type instanceof TArray
        );

        if ($validator === null) {
            return $arrays;
        }

        return array_map(
            fn (TArray $t): TArray => new TArray([$t->type_params[0], $this->validate($validator, $t->type_params[1])]),
            $arrays
        );
    }

    /**
     * @return array<TGenericObject|TNamedObject>
     */
    private function visitTraversables(?ValidatorInterface $validator, Union $previous): array
    {
        /** @var array<TNamedObject> $traversables */
        $traversables = array_filter(
            $previous->getAtomicTypes(),
            static fn (Atomic $type): bool => self::isTraversable($type)
        );

        if ($validator === null) {
            return $traversables;
        }

        return array_map(
            fn (TNamedObject $t): TGenericObject => self::visitTraversable($t, $validator),
            $traversables
        );
    }

    private function visitTraversable(TNamedObject $traversable, ValidatorInterface $validator): TGenericObject
    {
        if ($traversable instanceof TGenericObject) {
            return new TGenericObject($traversable->value, array_map(
                fn (Union $union): Union => $this->validate($validator, $union),
                $traversable->type_params
            ));
        }

        return new TGenericObject($traversable->value, [$this->validate($validator, Type::getMixed())]);
    }

    /**
     * @return array<TKeyedArray>
     */
    private function visitKeyedArrays(?ValidatorInterface $validator, Union $previous): array
    {
        $validated = [];
        /** @var array<TKeyedArray> $arrays */
        $arrays = array_filter(
            $previous->getAtomicTypes(),
            static fn (Atomic $t): bool => $t instanceof TKeyedArray,
        );

        if ($validator === null) {
            return $arrays;
        }

        foreach ($arrays as $array) {
            $validated[] = new TKeyedArray(array_map(
                fn (Union $property): Union => $this->validate($validator, $property),
                $array->properties
            ));
        }

        return $validated;
    }

    /**
     * @return array<Atomic>
     */
    private function visitExisting(?ValidatorInterface $validator, Union $union, ?string $delimiter): array
    {
        if ($validator === null) {
            return $union->getAtomicTypes();
        }

        $types = array_filter(
            $union->getAtomicTypes(),
            static fn (Atomic $t): bool => ! ($t instanceof TNamedObject && self::isATraversable($t->value))
        );

        if ($types === []) {
            return [];
        }

        $filtered = new Union($types);

        $validated = $this->validate($validator, $filtered);
        if ($delimiter === null) {
            return $validated->getAtomicTypes();
        }

        // preserve original string type - it might be a delimited list of valid types, or an un-delimited string
        $strings = TypeUtil::filter($union, new Union([new TString()]));
        $removed = TypeUtil::remove($validated, new Union([new TString()]));
        return array_merge($removed->getAtomicTypes(), $strings->getAtomicTypes());
    }

    private function validate(ValidatorInterface $validator, Union $union): Union
    {
        foreach ($this->validatorVisitors as $visitor) {
            $union = $visitor->visit($validator, $union);
        }

        return $union;
    }

    private static function isTraversable(Atomic $type): bool
    {
        if (! $type instanceof TNamedObject) {
            return false;
        }

        return $type->value === 'traversable' || self::isATraversable($type->value);
    }

    private static function isATraversable(string $value): bool
    {
        if ($value === 'traversable') {
            return true;
        }

        if (! (class_exists($value) || interface_exists($value))) {
            return false;
        }

        return is_a(Traversable::class, $value, true);
    }
}

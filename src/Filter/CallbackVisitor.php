<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Closure;
use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Laminas\Filter\Callback;
use Laminas\Filter\FilterInterface;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_combine;
use function array_map;
use function array_merge;
use function array_shift;
use function assert;
use function count;
use function is_array;
use function is_object;
use function is_string;

final readonly class CallbackVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof Callback) {
            return $previous;
        }

        $callable = $filter->getCallback();
        $union    = match (true) {
            $callable instanceof Closure => $this->getClosureReturnType($callable),
            is_array($callable)          => $this->getArrayReturnType($callable),
            is_object($callable)         => $this->getInvokableReturnType($callable),
            is_string($callable)         => $this->getStringReturnType($callable),
        };

        return $union ?? $previous;
    }

    /**
     * @param array{0: class-string|object, 1: string} $callable
     */
    private function getArrayReturnType(array $callable): ?Union
    {
        try {
            $reflection = new ReflectionClass($callable[0]);
            $method     = $reflection->getMethod($callable[1]);
        } catch (ReflectionException) {
            return null;
        }

        return $this->getUnion($this->getReturnType($method), $reflection);
    }

    private function getInvokableReturnType(object $invokable): ?Union
    {
        try {
            $reflection = new ReflectionClass($invokable);
            $method     = $reflection->getMethod('__invoke');
        } catch (ReflectionException) {
            return null;
        }

        return $this->getUnion($this->getReturnType($method), $reflection);
    }

    /**
     * @param callable-string $function
     */
    private function getStringReturnType(string $function): ?Union
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException) {
            return null;
        }

        return $this->getUnion($this->getReturnType($reflection));
    }

    private function getClosureReturnType(Closure $closure): ?Union
    {
        try {
            $reflection = new ReflectionFunction($closure);
        } catch (ReflectionException) {
            return null;
        }

        return $this->getUnion($this->getReturnType($reflection));
    }

    private function getReturnType(ReflectionFunctionAbstract $function): ?ReflectionType
    {
        if ($function->hasReturnType()) {
            return $function->getReturnType();
        }

        return $function->getTentativeReturnType();
    }

    private function getUnion(?ReflectionType $type, ?ReflectionClass $self = null): ?Union
    {
        if ($type === null) {
            return null;
        }

        $types = [];
        if ($type instanceof ReflectionIntersectionType) {
            /** @var array<TNamedObject> $intersection */
            $intersection = self::getAtomicTypes($type, $self);
            $first        = array_shift($intersection);
            $keys         = array_map(static fn (TNamedObject $type): string => $type->getKey(), $intersection);
            $types        = [
                new TNamedObject(
                    $first->value,
                    false,
                    false,
                    array_combine($keys, $intersection)
                ),
            ];
        } elseif ($type instanceof ReflectionUnionType) {
            $types = self::getAtomicTypes($type, $self);
        } else {
            assert($type instanceof ReflectionNamedType);
            $types[] = self::getAtomicType($type, $self);
        }

        assert(count($types) > 0);
        return new Union($types);
    }

    /**
     * @return array<string, Atomic>
     */
    private static function getAtomicTypes(
        ReflectionIntersectionType|ReflectionUnionType $type,
        ?ReflectionClass $self
    ): array {
        $types = [];
        foreach ($type->getTypes() as $subType) {
            if ($subType instanceof ReflectionIntersectionType) {
                $types = array_merge($types, self::getAtomicTypes($subType, $self));
            } else {
                $atomicType                   = self::getAtomicType($subType, $self);
                $types[$atomicType->getKey()] = $atomicType;
            }
        }

        return $types;
    }

    private static function getAtomicType(ReflectionNamedType $type, ?ReflectionClass $self): Atomic
    {
        if ($self !== null) {
            $parent = $self->getParentClass();
            $atomic = match ($type->getName()) {
                'parent'         => new TNamedObject($parent->getName()),
                'self', 'static' => new TNamedObject($self->getName()),
                default          => null,
            };
            if ($atomic) {
                return $atomic;
            }
        }

        return Atomic::create($type->getName());
    }
}

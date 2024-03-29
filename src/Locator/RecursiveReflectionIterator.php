<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeIgnore;
use RecursiveIterator;
use ReflectionClass;
use SplFileInfo;

use function is_string;

/**
 * @internal
 *
 * @template T of object
 * @implements RecursiveIterator<array-key, T|null>
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final class RecursiveReflectionIterator implements RecursiveIterator
{
    /**
     * @param ReflectionProvider<T> $reflectionProvider
     */
    public function __construct(
        private RecursiveIterator $iterator,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function current(): ?ReflectionClass
    {
        $current    = $this->iterator->current();
        $reflection = null;
        if ($current instanceof SplFileInfo) {
            $reflection = $this->reflectionProvider->getReflection($current->getPathname());
        }
        if (is_string($current)) {
            $reflection = $this->reflectionProvider->getReflection($current);
        }

        if ($reflection !== null && $reflection->getAttributes(PsalmTypeIgnore::class) !== []) {
            return null;
        }

        return $reflection;
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function hasChildren(): bool
    {
        return $this->iterator->hasChildren();
    }

    public function getChildren(): ?self
    {
        if (! $this->iterator->hasChildren()) {
            return null;
        }

        return new self($this->iterator->getChildren(), $this->reflectionProvider);
    }
}

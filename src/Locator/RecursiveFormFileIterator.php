<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Locator;

use Laminas\Form\Exception\InvalidElementException;
use Laminas\Form\FormInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use RecursiveIterator;
use ReflectionClass;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 * @implements RecursiveIterator<array-key, FormFile|null>
 */
final readonly class RecursiveFormFileIterator implements RecursiveIterator
{
    public function __construct(private RecursiveIterator $iterator, private PluginManagerInterface $formElementManager)
    {
    }

    public function current(): FormFile|null
    {
        /** @var ReflectionClass<FormInterface>|null $current */
        $current = $this->iterator->current();
        if (! $current instanceof ReflectionClass) {
            return null;
        }
        if ($current->isAbstract()) {
            return null;
        }

        try {
            $form = $this->formElementManager->get($current->getName());
        } catch (InvalidElementException) {
            return null;
        }

        if ($form instanceof FormInterface) {
            return new FormFile($current, $form);
        }

        return null;
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

    /**
     * @psalm-suppress InvalidNullableReturnType Just don't understand why psalm chokes on this
     */
    public function getChildren(): ?self
    {
        if (! $this->iterator->hasChildren()) {
            /** @psalm-suppress NullableReturnStatement Yes I _can_ return null!! */
            return null;
        }

        return new self($this->iterator->getChildren(), $this->formElementManager);
    }
}

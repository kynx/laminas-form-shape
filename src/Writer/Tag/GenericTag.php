<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class GenericTag implements TagInterface
{
    public function __construct(private string $contents)
    {
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    public function isBefore(TagInterface $tag): bool
    {
        return false;
    }

    public function matches(TagInterface $tag): bool
    {
        return (string) $this === (string) $tag;
    }
}

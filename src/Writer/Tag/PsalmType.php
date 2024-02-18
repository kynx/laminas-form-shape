<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

use function preg_match;
use function sprintf;
use function str_starts_with;
use function trim;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class PsalmType implements TagInterface
{
    public function __construct(private string $name, private string $definition)
    {
    }

    public function __toString(): string
    {
        return sprintf('@psalm-type %s = %s', $this->name, $this->definition);
    }

    public function isBefore(TagInterface $tag): bool
    {
        return ! str_starts_with(trim((string) $tag), '@psalm-import-type ');
    }

    public function matches(TagInterface $tag): bool
    {
        return (bool) preg_match('/@psalm-type\s+' . $this->name . '/', (string) $tag);
    }
}

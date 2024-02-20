<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

use function sprintf;
use function str_starts_with;
use function trim;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class PsalmTemplateExtends implements TagInterface
{
    public function __construct(private string $parent, private string $type)
    {
    }

    public function __toString(): string
    {
        return sprintf('@psalm-template-extends %s<%s>', $this->parent, $this->type);
    }

    public function isBefore(TagInterface $tag): bool
    {
        return false;
    }

    public function matches(TagInterface $tag): bool
    {
        return str_starts_with(trim((string) $tag), '@psalm-template-extends ');
    }
}

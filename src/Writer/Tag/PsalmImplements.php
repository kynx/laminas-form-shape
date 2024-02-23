<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

use function sprintf;
use function str_starts_with;
use function trim;

final readonly class PsalmImplements implements TagInterface
{
    public function __construct(private string $interface, private string $type)
    {
    }

    public function __toString(): string
    {
        return sprintf('@implements %s<%s>', $this->interface, $this->type);
    }

    public function isBefore(TagInterface $tag): bool
    {
        return false;
    }

    public function matches(TagInterface $tag): bool
    {
        return str_starts_with(trim((string) $tag), '@implements ');
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

use function implode;
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
final readonly class Method implements TagInterface
{
    public function __construct(private string $name, private array $args = [], private string $returnType = '')
    {
    }

    public function __toString(): string
    {
        return sprintf(
            '@method %s%s(%s)',
            $this->returnType === '' ? '' : $this->returnType . ' ',
            $this->name,
            implode($this->args)
        );
    }

    public function isBefore(TagInterface $tag): bool
    {
        $trimmed = trim((string) $tag);
        return str_starts_with($trimmed, '@param')
            || str_starts_with($trimmed, '@return')
            || str_starts_with($trimmed, '@psalm');
    }

    public function matches(TagInterface $tag): bool
    {
        return (bool) preg_match('/^\s*@method\s.*' . $this->name . '\s*\(/s', (string) $tag);
    }
}

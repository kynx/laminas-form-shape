<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer\Tag;

use Stringable;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
interface TagInterface extends Stringable
{
    /**
     * Returns true if line has matching tag name
     */
    public function isBefore(TagInterface $tag): bool;

    /**
     * Returns true if line matches this tag
     */
    public function matches(TagInterface $tag): bool;
}

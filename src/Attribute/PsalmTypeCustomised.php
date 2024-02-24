<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Attribute;

use Attribute;

/**
 * Marks a form or fieldset as having a custom type
 *
 * The class will be loaded and any child fieldsets will be processed, but changes will not be written to disk. Provide
 * the `@psalm-type` so it can be imported by other forms and fieldsets.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PsalmTypeCustomised
{
    public function __construct(public string $psalmType)
    {
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Attribute;

use Attribute;

/**
 * Marks a form or fieldset as ignored
 *
 * Ignored elements are not loaded from the `FormElementManager` and no child fieldsets will be processed. You typically
 * only want to use this if the form cannot actually be loaded in a normal way.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class PsalmTypeIgnore
{
}

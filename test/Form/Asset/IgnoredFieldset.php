<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form\Asset;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeIgnore;
use Laminas\Form\Fieldset;

#[PsalmTypeIgnore]
final class IgnoredFieldset extends Fieldset
{
}

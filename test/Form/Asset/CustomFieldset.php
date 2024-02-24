<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form\Asset;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeCustomised;
use Laminas\Form\Fieldset;

#[PsalmTypeCustomised('TCustomType')]
final class CustomFieldset extends Fieldset
{
}

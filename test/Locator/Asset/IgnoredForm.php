<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Locator\Asset;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeIgnore;
use Laminas\Form\Form;

#[PsalmTypeIgnore]
final class IgnoredForm extends Form
{
}

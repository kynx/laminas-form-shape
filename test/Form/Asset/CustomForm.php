<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Form\Asset;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeCustomised;
use Laminas\Form\Form;

#[PsalmTypeCustomised('TFormType')]
final class CustomForm extends Form
{
}

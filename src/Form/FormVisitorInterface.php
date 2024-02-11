<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Laminas\Form\FormInterface;
use Psalm\Type\Union;

interface FormVisitorInterface
{
    public function visit(FormInterface $form): Union;
}

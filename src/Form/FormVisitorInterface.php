<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\Form\FormInterface;

interface FormVisitorInterface
{
    public function visit(FormInterface $form): CollectionFilterShape|InputFilterShape;
}

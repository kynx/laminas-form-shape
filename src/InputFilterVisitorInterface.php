<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Laminas\InputFilter\InputFilterInterface;
use Psalm\Type\Union;

interface InputFilterVisitorInterface
{
    public function visit(InputFilterInterface $inputFilter): Union;
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Laminas\InputFilter\InputFilterInterface;

interface InputFilterVisitorInterface
{
    public function visit(InputFilterInterface $inputFilter, string $name = '', int $indent = 0): ArrayShape;
}

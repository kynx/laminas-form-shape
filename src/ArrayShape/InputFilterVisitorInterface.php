<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Shape\ArrayShape;
use Laminas\InputFilter\InputFilterInterface;

interface InputFilterVisitorInterface
{
    public function visit(InputFilterInterface $inputFilter, string $name = '', int $indent = 0): ArrayShape;
}

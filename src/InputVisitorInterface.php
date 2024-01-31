<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Shape\InputShape;
use Laminas\InputFilter\InputInterface;

interface InputVisitorInterface
{
    public function visit(InputInterface $input): InputShape;
}

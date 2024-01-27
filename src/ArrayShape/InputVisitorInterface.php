<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Laminas\InputFilter\InputInterface;

interface InputVisitorInterface
{
    public function visit(InputInterface $input): InputType;
}

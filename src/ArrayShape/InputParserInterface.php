<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\InputType;
use Laminas\InputFilter\InputInterface;

interface InputParserInterface
{
    public function getInputType(InputInterface $input): InputType;
}

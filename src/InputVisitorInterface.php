<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Laminas\InputFilter\InputInterface;
use Psalm\Type\Union;

interface InputVisitorInterface
{
    public function visit(InputInterface $input): Union;
}

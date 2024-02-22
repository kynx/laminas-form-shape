<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Laminas\InputFilter\InputFilterInterface;
use Psalm\Type\Union;

interface InputFilterVisitorInterface
{
    public function visit(InputFilterInterface $inputFilter, ImportType|ImportTypes $importTypes): Union;
}

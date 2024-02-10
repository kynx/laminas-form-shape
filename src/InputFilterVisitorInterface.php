<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;

interface InputFilterVisitorInterface
{
    public function visit(InputFilterInterface $inputFilter): Union;
}

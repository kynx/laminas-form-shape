<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;

interface InputFilterVisitorInterface
{
    /**
     * @psalm-return ($inputFilter is CollectionInputFilter ? CollectionFilterShape : InputFilterShape)
     */
    public function visit(InputFilterInterface $inputFilter, string $name = ''): CollectionFilterShape|InputFilterShape;
}

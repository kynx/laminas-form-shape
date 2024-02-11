<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Union;

interface ValidatorVisitorInterface
{
    /**
     * Returns union of types that would result from running given validator
     *
     * If implementations cannot handle the validator they _must_ return the existing union unaltered.
     */
    public function visit(ValidatorInterface $validator, Union $previous): Union;
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Laminas\InputFilter\InputInterface;
use Psalm\Type\Union;

interface InputVisitorInterface
{
    /**
     * Returns union for input if it can be processed, or `null`
     */
    public function visit(InputInterface $input): ?Union;
}

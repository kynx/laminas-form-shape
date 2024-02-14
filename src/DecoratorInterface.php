<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Psalm\Type\Union;

interface DecoratorInterface
{
    public function decorate(Union $union, int $indent = 0): string;
}

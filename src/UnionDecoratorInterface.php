<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Psalm\Type\Union;

interface UnionDecoratorInterface
{
    public function decorate(Union $union, int $indent = 0): string;
}
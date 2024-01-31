<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;

use function str_contains;

abstract readonly class AbstractInputFilterShapeDecorator
{
    protected function getTypeName(InputFilterShape|InputShape $shape): string
    {
        $name = str_contains($shape->name, ' ') ? "'" . $shape->name . "'" : $shape->name;
        return $shape->optional ? "$name?" : $name;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\ArrayShape;
use Kynx\Laminas\FormShape\Shape\ElementShape;

use function str_contains;

abstract readonly class AbstractDecorator
{
    protected function getTypeName(ArrayShape|ElementShape $shape): string
    {
        $name = str_contains($shape->name, ' ') ? "'" . $shape->name . "'" : $shape->name;
        return $shape->optional ? "$name?" : $name;
    }
}

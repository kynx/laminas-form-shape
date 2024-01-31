<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\InputShape;
use Kynx\Laminas\FormShape\Type\TypeStringInterface;

use function array_map;
use function implode;
use function sort;

use const SORT_STRING;

final readonly class InputShapeDecorator
{
    public function decorate(InputShape $shape): string
    {
        $types = array_map(
            static fn (TypeStringInterface $type): string => $type->getTypeString(),
            $shape->types
        );

        sort($types, SORT_STRING);

        return implode('|', $types);
    }
}

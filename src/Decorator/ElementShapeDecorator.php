<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\ElementShape;
use Kynx\Laminas\FormShape\Type\TypeStringInterface;

use function array_map;
use function implode;
use function sort;

use const SORT_STRING;

final readonly class ElementShapeDecorator extends AbstractDecorator
{
    public function decorate(ElementShape $shape): string
    {
        $types = array_map(
            static fn (TypeStringInterface $type): string => $type->getTypeString(),
            $shape->types
        );

        sort($types, SORT_STRING);

        return implode('|', $types);
    }
}

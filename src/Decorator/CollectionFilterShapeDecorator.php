<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;

use function sprintf;

final readonly class CollectionFilterShapeDecorator
{
    public function __construct(private InputFilterShapeDecorator $inputFilterShapeDecorator)
    {
    }

    public function decorate(CollectionFilterShape $shape, int $indent = 0): string
    {
        $type        = $shape->nonEmpty ? 'non-empty-array' : 'array';
        $shapeString = $shape->shape instanceof InputFilterShape
            ? $this->inputFilterShapeDecorator->decorate($shape->shape, $indent)
            : $this->decorate($shape->shape, $indent);

        return sprintf('%s<%s>', $type, $shapeString);
    }
}

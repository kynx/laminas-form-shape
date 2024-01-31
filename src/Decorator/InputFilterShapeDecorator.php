<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;

use function array_map;
use function array_reduce;
use function implode;
use function max;
use function sprintf;
use function str_contains;
use function str_pad;
use function str_repeat;
use function strlen;

final readonly class InputFilterShapeDecorator
{
    private CollectionFilterShapeDecorator $collectionFilterShapeDecorator;
    private InputShapeDecorator $inputShapeDecorator;

    public function __construct(private string $indentString = '    ')
    {
        $this->collectionFilterShapeDecorator = new CollectionFilterShapeDecorator($this);
        $this->inputShapeDecorator            = new InputShapeDecorator();
    }

    public function decorate(InputFilterShape $shape, int $indent = 0): string
    {
        $padding  = $this->calculatePadding($shape);
        $elements = array_map(
            fn (CollectionFilterShape|InputFilterShape|InputShape $shape): string
                => $this->formatShape($shape, $padding, $indent + 1),
            $shape->shapes
        );

        return sprintf(
            "array{\n%s%s}",
            implode('', $elements),
            $this->getIndent($indent, $this->indentString)
        );
    }

    private function calculatePadding(InputFilterShape $shape): int
    {
        return array_reduce(
            $shape->shapes,
            function (int $max, CollectionFilterShape|InputFilterShape|InputShape $input): int {
                $padding = strlen(sprintf('%s: ', $this->getTypeName($input)));
                return max($padding, $max);
            },
            0
        );
    }

    private function getTypeName(CollectionFilterShape|InputFilterShape|InputShape $shape): string
    {
        $name = str_contains($shape->name, ' ') ? "'" . $shape->name . "'" : $shape->name;
        return $shape->optional ? "$name?" : $name;
    }

    private function formatShape(
        CollectionFilterShape|InputFilterShape|InputShape $shape,
        int $padding,
        int $indent
    ): string {
        $shapeString = match ($shape::class) {
            CollectionFilterShape::class => $this->collectionFilterShapeDecorator->decorate($shape, $indent),
            InputFilterShape::class      => $this->decorate($shape, $indent),
            InputShape::class            => $this->inputShapeDecorator->decorate($shape),
        };

        return sprintf(
            "%s%s%s,\n",
            $this->getIndent($indent, $this->indentString),
            str_pad(sprintf('%s: ', $this->getTypeName($shape)), $padding),
            $shapeString
        );
    }

    private function getIndent(int $indent, string $indentString): string
    {
        return str_repeat($indentString, $indent);
    }
}

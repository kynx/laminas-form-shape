<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Kynx\Laminas\FormShape\Shape\InputShape;

use function array_map;
use function array_reduce;
use function implode;
use function max;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;

final readonly class InputFilterShapeDecorator extends AbstractInputFilterShapeDecorator
{
    private InputShapeDecorator $elementShapeDecorator;

    public function __construct(private string $indentString = '    ')
    {
        $this->elementShapeDecorator = new InputShapeDecorator();
    }

    public function decorate(InputFilterShape $arrayShape, int $indent = 0): string
    {
        $padding  = $this->calculatePadding($arrayShape);
        $elements = array_map(
            fn (InputFilterShape|InputShape $shape): string => $this->formatShape($shape, $padding, $indent + 1),
            $arrayShape->shapes
        );

        return sprintf(
            "array{\n%s%s}",
            implode('', $elements),
            $this->getIndent($indent, $this->indentString)
        );
    }

    private function calculatePadding(InputFilterShape $shape): int
    {
        return array_reduce($shape->shapes, function (int $max, InputFilterShape|InputShape $input): int {
            $padding = strlen(sprintf('%s: ', $this->getTypeName($input)));
            return max($padding, $max);
        }, 0);
    }

    private function formatShape(InputFilterShape|InputShape $shape, int $padding, int $indent): string
    {
        $shapeString = $shape instanceof InputFilterShape
            ? $this->decorate($shape, $indent)
            : $this->elementShapeDecorator->decorate($shape);

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

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;

use function implode;
use function max;
use function sprintf;
use function str_contains;
use function str_pad;
use function str_repeat;
use function strlen;

final readonly class KeyedArrayDecorator
{
    public function __construct(private UnionDecorator $unionDecorator)
    {
    }

    public function decorate(TKeyedArray $keyedArray, int $indent = 0): string
    {
        $padding = $this->calculatePadding($keyedArray->properties);

        $elements = [];
        foreach ($keyedArray->properties as $name => $union) {
            $name       = str_pad(sprintf('%s: ', $this->getName($name, $union->possibly_undefined)), $padding);
            $elements[] = sprintf(
                "%s%s%s,\n",
                $this->getIndent($indent + 1, $this->unionDecorator->indentString),
                $name,
                $this->unionDecorator->decorate($union, $indent + 1)
            );
        }

        return sprintf(
            "array{\n%s%s}",
            implode('', $elements),
            $this->getIndent($indent, $this->unionDecorator->indentString)
        );
    }

    /**
     * @param array<Union> $properties
     */
    private function calculatePadding(array $properties): int
    {
        $padding = 0;
        foreach ($properties as $name => $union) {
            $length  = strlen(sprintf('%s: ', $this->getName($name, $union->possibly_undefined)));
            $padding = max($padding, $length);
        }

        return $padding;
    }

    private function getName(int|string $name, bool $possiblyUndefined): string
    {
        $name = str_contains((string) $name, ' ') ? "'" . $name . "'" : $name;
        return $possiblyUndefined ? "$name?" : (string) $name;
    }

    private function getIndent(int $indent, string $indentString): string
    {
        return str_repeat($indentString, $indent);
    }
}

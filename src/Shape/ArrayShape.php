<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Shape;

use Kynx\Laminas\FormShape\Type\TypeNameInterface;
use Kynx\Laminas\FormShape\Type\TypeStringInterface;

use function array_map;
use function array_reduce;
use function implode;
use function max;
use function sprintf;
use function str_contains;
use function str_pad;
use function str_repeat;
use function strlen;

final readonly class ArrayShape implements TypeNameInterface, TypeStringInterface
{
    /**
     * @param list<TypeNameInterface&TypeStringInterface> $types
     */
    public function __construct(private string $name, private array $types, private bool $optional = false)
    {
    }

    public function getTypeName(): string
    {
        if ($this->optional) {
            return $this->escapeName() . '?';
        }
        return $this->escapeName();
    }

    public function getTypeString(int $indent = 0, string $indentString = '    '): string
    {
        $padding = $this->calculatePadding();
        $inputs  = array_map(
            fn (TypeNameInterface&TypeStringInterface $input): string => $this->formatInput(
                $input,
                $padding,
                $indent + 1,
                $indentString
            ),
            $this->types
        );

        return sprintf(
            "array{\n%s%s}",
            implode('', $inputs),
            $this->getIndent($indent, $indentString)
        );
    }

    private function calculatePadding(): int
    {
        return array_reduce($this->types, static function (int $max, TypeNameInterface $input): int {
            $padding = strlen(sprintf('%s: ', $input->getTypeName()));
            return max($padding, $max);
        }, 0);
    }

    private function formatInput(
        TypeNameInterface&TypeStringInterface $input,
        int $padding,
        int $indent,
        string $indentString
    ): string {
        return sprintf(
            "%s%s%s,\n",
            $this->getIndent($indent, $indentString),
            str_pad(sprintf('%s: ', $input->getTypeName()), $padding),
            $input->getTypeString($indent, $indentString)
        );
    }

    private function escapeName(): string
    {
        return str_contains($this->name, ' ') ? "'" . $this->name . "'" : $this->name;
    }

    private function getIndent(int $indent, string $indentString): string
    {
        return str_repeat($indentString, $indent);
    }
}

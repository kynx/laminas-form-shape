<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function array_map;
use function implode;
use function sprintf;
use function str_contains;
use function str_repeat;

final readonly class ArrayType implements TypeNameInterface, TypeStringInterface
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
        $inputs = array_map(
            fn (TypeNameInterface&TypeStringInterface $input): string => $this->formatInput(
                $input,
                $indent + 1,
                $indentString
            ),
            $this->types
        );
        return sprintf("array{\n%s}", implode('', $inputs));
    }

    private function formatInput(
        TypeNameInterface&TypeStringInterface $input,
        int $indent,
        string $indentString
    ): string {
        return sprintf(
            "%s%s: %s,\n",
            str_repeat($indentString, $indent),
            $input->getTypeName(),
            $input->getTypeString($indent, $indentString)
        );
    }

    private function escapeName(): string
    {
        return str_contains($this->name, ' ') ? "'" . $this->name . "'" : $this->name;
    }
}

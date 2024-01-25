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

    public function getTypeString(string $indent = '    '): string
    {
        $inputs = array_map(
            fn (TypeNameInterface&TypeStringInterface $input): string => $this->formatInput($input, $indent),
            $this->types
        );
        return sprintf("array{\n%s%s}", $indent, implode($indent, $inputs));
    }

    private function formatInput(TypeNameInterface&TypeStringInterface $input, string $indent): string
    {
        return $input->getTypeName() . ': ' . $input->getTypeString(str_repeat($indent, 2)) . ",\n";
    }

    private function escapeName(): string
    {
        return str_contains($this->name, ' ') ? "'" . $this->name . "'" : $this->name;
    }
}

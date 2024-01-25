<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function array_map;
use function implode;
use function sort;

use const SORT_STRING;

final readonly class Generic extends AbstractParsedType
{
    public function __construct(public PsalmType|ClassString $type, public array $union)
    {
    }

    public function getTypeString(string $indent = '    '): string
    {
        if ($this->union === []) {
            return $this->type->getTypeString();
        }
        $union = array_map(
            static fn (TypeStringInterface $type): string => $type->getTypeString(),
            $this->union
        );

        sort($union, SORT_STRING);

        return $this->type->getTypeString() . '<' . implode('|', $union) . '>';
    }
}

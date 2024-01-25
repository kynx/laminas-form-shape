<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function implode;
use function sort;

use const SORT_STRING;

final readonly class Literal extends AbstractParsedType
{
    /**
     * @param array<scalar> $values
     */
    public function __construct(private array $values)
    {
    }

    public function getTypeString(string $indent = '    '): string
    {
        $values = $this->values;

        sort($values, SORT_STRING);

        return implode('|', $values);
    }
}

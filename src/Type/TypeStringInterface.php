<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Type;

interface TypeStringInterface
{
    public function getTypeString(int $indent = 0, string $indentString = '    '): string;
}

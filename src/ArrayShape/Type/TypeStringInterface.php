<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

interface TypeStringInterface
{
    public function getTypeString(string $indent = '    '): string;
}

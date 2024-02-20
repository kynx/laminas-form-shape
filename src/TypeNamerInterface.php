<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use ReflectionClass;

interface TypeNamerInterface
{
    /**
     * Returns name for psalm type (ie `TMyClassArray`)
     */
    public function name(ReflectionClass $reflection): string;
}

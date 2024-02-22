<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type\Atomic\TAnonymousClassInstance;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\TypeNode;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
trait IsFqcnTypeTrait
{
    /**
     * @psalm-assert-if-true TNamedObject $type
     */
    private function isFqcnType(TypeNode $type): bool
    {
        if ($type instanceof TClosure || $type instanceof TAnonymousClassInstance) {
            return false;
        }

        return $type instanceof TNamedObject;
    }
}
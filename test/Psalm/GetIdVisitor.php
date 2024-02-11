<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Psalm\Type\TypeNode as T;
use Psalm\Type\TypeVisitor;
use Psalm\Type\Union;

/**
 * Calling `getId()` on a union has side effects! So much for all that `MutableUnion` stuff :|
 *
 * This traverses types calling `getId()` on any child unions so tests can match types
 */
final class GetIdVisitor extends TypeVisitor
{
    protected function enterNode(T $type): ?int
    {
        if ($type instanceof Union) {
            /** @psalm-suppress UnusedMethodCall */
            $type->getId(false);
        }

        return null;
    }
}

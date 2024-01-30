<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Type;

/**
 * @psalm-type VisitedUnion = AbstractVisitedType|PsalmType
 * @psalm-type VisitedArray = array<array-key, VisitedUnion>
 */
abstract readonly class AbstractVisitedType implements TypeStringInterface
{
}

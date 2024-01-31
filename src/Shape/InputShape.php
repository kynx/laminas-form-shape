<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Shape;

use Kynx\Laminas\FormShape\Type\TypeUtil;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
final readonly class InputShape
{
    /**
     * @param VisitedArray $types
     */
    public function __construct(public string $name, public array $types, public bool $optional = false)
    {
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Shape;

final readonly class ArrayShape
{
    /**
     * @param list<ArrayShape|ElementShape> $shapes
     */
    public function __construct(public string $name, public array $shapes, public bool $optional = false)
    {
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Shape;

final readonly class InputFilterShape
{
    /**
     * @param list<CollectionFilterShape|InputFilterShape|InputShape> $shapes
     */
    public function __construct(public string $name, public array $shapes, public bool $optional = false)
    {
    }
}

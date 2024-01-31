<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Shape;

final readonly class CollectionFilterShape
{
    public function __construct(
        public string $name,
        public CollectionFilterShape|InputFilterShape $shape,
        public bool $optional = false,
        public bool $nonEmpty = true
    ) {
    }
}

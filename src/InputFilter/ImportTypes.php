<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

final readonly class ImportTypes
{
    /**
     * @param array<ImportTypes> $children
     */
    public function __construct(private ?ImportType $type = null, private array $children = [])
    {
    }

    public function get(): ?ImportType
    {
        return $this->type;
    }

    public function getChildren(int|string $key): self
    {
        return $this->children[$key] ?? new self(null, []);
    }
}

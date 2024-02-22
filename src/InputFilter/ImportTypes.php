<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

final readonly class ImportTypes
{
    /**
     * @param array<ImportType|array> $importTypes
     */
    public function __construct(private array $importTypes)
    {
    }

    public function get(int|string $key): ImportType|ImportTypes
    {
        /** @var ImportType|array<ImportType|array> $value */
        $value = $this->importTypes[$key] ?? [];
        if ($value instanceof ImportType) {
            return $value;
        }

        return new self($value);
    }
}

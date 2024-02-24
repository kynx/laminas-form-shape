<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\CodingStandards;

interface FixerInterface
{
    public function getName(): string;

    public function addFile(string $path): void;

    public function fix(): void;
}

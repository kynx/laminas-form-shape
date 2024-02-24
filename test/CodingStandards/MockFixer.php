<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\CodingStandards;

use Kynx\Laminas\FormShape\CodingStandards\FixerInterface;

final class MockFixer implements FixerInterface
{
    public array $paths = [];
    public bool $fixed  = false;

    public function getName(): string
    {
        return 'mock-fixer';
    }

    public function addFile(string $path): void
    {
        $this->paths[] = $path;
    }

    public function fix(): void
    {
        $this->fixed = true;
    }
}

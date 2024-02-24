<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\CodingStandards;

use Kynx\Laminas\FormShape\CodingStandards\FixerInterface;

use function array_map;
use function basename;
use function escapeshellarg;
use function escapeshellcmd;
use function implode;
use function passthru;

final class PhpCodeSnifferFixer implements FixerInterface
{
    private array $paths = [];

    public function __construct(private string $phpCbf)
    {
    }

    public function getName(): string
    {
        return basename($this->phpCbf);
    }

    public function addFile(string $path): void
    {
        $this->paths[] = $path;
    }

    public function fix(): void
    {
        $paths = array_map(static fn (string $path): string => escapeshellarg($path), $this->paths);
        passthru(escapeshellcmd($this->phpCbf) . ' ' . implode(' ', $paths));
    }
}

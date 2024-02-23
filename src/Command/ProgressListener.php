<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Form\ProgressListenerInterface;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\StyleInterface;

use function implode;
use function sprintf;
use function strlen;
use function substr;

final class ProgressListener implements ProgressListenerInterface
{
    private int $status = Command::SUCCESS;

    /**
     * @param array<string> $paths
     */
    public function __construct(
        private readonly StyleInterface $io,
        private readonly string $cwd,
        private readonly array $paths
    ) {
    }

    public function error(string $error): void
    {
        $this->status = Command::FAILURE;
        $this->io->error($error);
    }

    public function success(ReflectionClass $reflection): void
    {
        $path = substr($reflection->getFileName(), strlen($this->cwd) + 1);
        $this->io->text("Processed $path");
    }

    public function finally(int $processed): void
    {
        if ($processed === 0) {
            $this->status = Command::INVALID;
            $this->io->error(sprintf("Cannot find any forms at '%s'", implode("', '", $this->paths)));
            return;
        }

        $this->io->success(sprintf("Added types to %s forms", $processed));
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}

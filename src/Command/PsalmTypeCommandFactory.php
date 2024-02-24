<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Composer\InstalledVersions;
use Kynx\Laminas\FormShape\CodingStandards\FixerInterface;
use Kynx\Laminas\FormShape\CodingStandards\PhpCodeSnifferFixer;
use Kynx\Laminas\FormShape\Form\FormProcessor;
use Psr\Container\ContainerInterface;

use function is_executable;
use function sprintf;

final readonly class PsalmTypeCommandFactory
{
    public function __invoke(ContainerInterface $container): PsalmTypeCommand
    {
        return new PsalmTypeCommand(
            $container->get(FormProcessor::class),
            $this->getFixer()
        );
    }

    private function getFixer(): ?FixerInterface
    {
        $phpCodeSniffer = null;
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (isset($installed['versions']['squizlabs/php_codesniffer'])) {
                $phpCodeSniffer = $installed['versions']['squizlabs/php_codesniffer'];
                break;
            }
        }
        if ($phpCodeSniffer === null || ! isset($phpCodeSniffer['install_path'])) {
            return null;
        }

        $path = sprintf('%s/bin/phpcbf', $phpCodeSniffer['install_path']);
        if (! is_executable($path)) {
            return null;
        }

        return new PhpCodeSnifferFixer($path);
    }
}

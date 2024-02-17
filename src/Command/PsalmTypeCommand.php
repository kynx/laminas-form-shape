<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function sprintf;

final class PsalmTypeCommand extends Command
{
    public function __construct(
        private readonly FormLocatorInterface $formLocator,
        private readonly FormVisitorInterface $formVisitor,
        private readonly DecoratorInterface $decorator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription("Generate psalm type for form")
            ->addArgument(
                'path',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Paths to scan'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $paths */
        $paths = (array) $input->getArgument('path');
        $io    = new SymfonyStyle($input, $output);

        $formFiles = $this->formLocator->locate($paths);
        if ($formFiles === []) {
            $io->error(sprintf("Cannot find any forms at '%s'", implode("', '", $paths)));
            return self::INVALID;
        }

        $success = true;
        foreach ($formFiles as $formFile) {
            $success = $this->outputType($io, $formFile) && $success;
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function outputType(SymfonyStyle $io, FormFile $formFile): bool
    {
        try {
            $union = $this->formVisitor->visit($formFile->form);
        } catch (InputVisitorException $e) {
            $io->error($e->getMessage());
            return false;
        }

        $io->section(sprintf("Psalm type for %s", $formFile->reflection->getFileName()));
        $io->block($this->decorator->decorate($union));
        return true;
    }
}

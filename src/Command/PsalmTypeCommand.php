<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\CodingStandards\FixerInterface;
use Kynx\Laminas\FormShape\Form\FormProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function getcwd;

final class PsalmTypeCommand extends Command
{
    public function __construct(
        private readonly FormProcessorInterface $formProcessor,
        private readonly ?FixerInterface $fixer,
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
            )
            ->addOption(
                'fieldset-types',
                null,
                InputOption::VALUE_NEGATABLE,
                'Add types to fieldsets (enabled by default)',
                true
            )
            ->addOption(
                'remove-getdata-return',
                null,
                InputOption::VALUE_NEGATABLE,
                'Remove @return from getData(), if present (disabled by default)',
                false
            );

        if ($this->fixer !== null) {
            $name = $this->fixer->getName();
            $this->addOption(
                'cs-fix',
                null,
                InputOption::VALUE_NONE,
                "Run $name on changed files",
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $paths */
        $paths               = (array) $input->getArgument('path');
        $processFieldsets    = (bool) $input->getOption('fieldset-types');
        $removeGetDataReturn = (bool) $input->getOption('remove-getdata-return');

        $fix = $this->fixer !== null && $input->getOption('cs-fix');

        $io       = new SymfonyStyle($input, $output);
        $listener = new ProgressListener($io, $fix ? $this->fixer : null, (string) getcwd(), $paths);

        $this->formProcessor->process($paths, $listener, $processFieldsets, $removeGetDataReturn);

        if ($fix) {
            $io->info("Running " . $this->fixer->getName() . "...");
            $this->fixer->fix();
        }

        return $listener->getStatus();
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

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
    public function __construct(private readonly FormProcessorInterface $formProcessor)
    {
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
                'Add types to fieldsets',
                true
            )
            ->addOption(
                'remove-getdata-return',
                null,
                InputOption::VALUE_NEGATABLE,
                'Remove @return from getData(), if present',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $paths */
        $paths               = (array) $input->getArgument('path');
        $processFieldsets    = (bool) $input->getOption('fieldset-types');
        $removeGetDataReturn = (bool) $input->getOption('remove-getdata-return');

        $io       = new SymfonyStyle($input, $output);
        $listener = new ProgressListener($io, getcwd(), $paths);

        $this->formProcessor->process($paths, $listener, $processFieldsets, $removeGetDataReturn);

        return $listener->getStatus();
    }
}

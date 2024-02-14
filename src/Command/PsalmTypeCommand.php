<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PsalmTypeCommand extends Command
{
    public function __construct(
        private readonly FormReaderInterface $formReader,
        private readonly FormVisitorInterface $formVisitor,
        private readonly DecoratorInterface $decorator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription("Generate psalm type for form")
            ->addArgument('path', InputArgument::REQUIRED, 'Path to form');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('path');
        $io   = new SymfonyStyle($input, $output);

        $formFile = $this->formReader->getFormFile($path);
        if ($formFile === null) {
            $io->error("Cannot find form at path '$path'");
            return self::INVALID;
        }

        try {
            $union = $this->formVisitor->visit($formFile->form);
        } catch (InputVisitorException $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        $io->section("Psalm type for $path");
        $io->block($this->decorator->decorate($union));

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\ArrayShapeException;
use Kynx\Laminas\FormShape\Form\FormProcessor;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FormShapeCommand extends Command
{
    public function __construct(
        private readonly FormProcessor $formProcessor,
        private readonly InputFilterVisitorInterface $inputFilterVisitor
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription("Generate array shape for form")
            ->addArgument('path', InputArgument::REQUIRED, 'Path to form');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('path');
        $io   = new SymfonyStyle($input, $output);

        $form = $this->formProcessor->getFormFromPath($path);
        if ($form === null) {
            $io->error("Cannot find form at path '$path'");
            return self::INVALID;
        }

        try {
            $arrayShape = $this->inputFilterVisitor->visit($form->getInputFilter())->getTypeString();
        } catch (ArrayShapeException $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        $io->section("Psalm type for $path");
        $io->block($arrayShape);
        return self::SUCCESS;
    }
}

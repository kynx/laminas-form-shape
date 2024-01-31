<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\Decorator\CollectionFilterShapeDecorator;
use Kynx\Laminas\FormShape\Decorator\InputFilterShapeDecorator;
use Kynx\Laminas\FormShape\File\FormReaderInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorException;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FormShapeCommand extends Command
{
    public function __construct(
        private readonly FormReaderInterface $formReader,
        private readonly FormVisitorInterface $formVisitor,
        private readonly InputFilterShapeDecorator $inputFilterShapeDecorator,
        private readonly CollectionFilterShapeDecorator $collectionFilterShapeDecorator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription("Generate array shape for form")
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
            $shape = $this->formVisitor->visit($formFile->form);
        } catch (InputVisitorException $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        $io->section("Psalm type for $path");
        if ($shape instanceof InputFilterShape) {
            $io->block($this->inputFilterShapeDecorator->decorate($shape));
        } else {
            $io->block($this->collectionFilterShapeDecorator->decorate($shape));
        }
        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Command;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\Form\FormVisitorInterface;
use Kynx\Laminas\FormShape\InputFilter\InputVisitorException;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Kynx\Laminas\FormShape\Writer\DocBlock;
use Kynx\Laminas\FormShape\Writer\FileWriter;
use Kynx\Laminas\FormShape\Writer\Tag\Method;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmTemplateExtends;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use Kynx\Laminas\FormShape\Writer\Tag\ReturnType;
use Psalm\Type\Union;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private readonly TypeNamerInterface $typeNamer
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
                'output',
                'o',
                InputOption::VALUE_NONE,
                'Output type instead of updating form'
            )
            ->addOption(
                'remove-getdata-return',
                null,
                InputOption::VALUE_NONE,
                'Remove @return from getData(), if present'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $paths */
        $paths               = (array) $input->getArgument('path');
        $update              = ! $input->getOption('output');
        $removeGetDataReturn = (bool) $input->getOption('remove-getdata-return');

        $io = new SymfonyStyle($input, $output);

        $formFiles = $this->formLocator->locate($paths);
        if ($formFiles === []) {
            $io->error(sprintf("Cannot find any forms at '%s'", implode("', '", $paths)));
            return self::INVALID;
        }

        $success = true;

        foreach ($formFiles as $formFile) {
            $success = $this->processForm($io, $formFile, $update, $removeGetDataReturn) && $success;
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function processForm(SymfonyStyle $io, FormFile $formFile, bool $update, bool $removeGetDataReturn): bool
    {
        try {
            $union = $this->formVisitor->visit($formFile->form);
        } catch (InputVisitorException $e) {
            $io->error($e->getMessage());
            return false;
        }

        if ($update) {
            $this->updateForm($io, $formFile, $union, $removeGetDataReturn);
            return true;
        }

        $this->outputType($io, $formFile, $union);
        return true;
    }

    private function updateForm(SymfonyStyle $io, FormFile $formFile, Union $union, bool $removeGetDataReturn): void
    {
        $typeName       = $this->typeNamer->name($formFile->reflection);
        $type           = $this->decorator->decorate($union);
        $extends        = $formFile->reflection->getParentClass()->getShortName();
        $classDocBlock  = DocBlock::fromDocComment($formFile->reflection->getDocComment())
            ->withTag(new PsalmType($typeName, $type))
            ->withTag(new PsalmTemplateExtends($extends, $typeName));
        $methodDocBlock = null;

        if ($removeGetDataReturn) {
            $classDocBlock = $classDocBlock->withoutTag(new Method('getData'));

            if ($formFile->reflection->hasMethod('getData')) {
                $method         = $formFile->reflection->getMethod('getData');
                $methodDocBlock = DocBlock::fromDocComment($method->getDocComment())
                    ->withoutTag(new ReturnType(''));
            }
        }

        FileWriter::write($formFile->reflection, $classDocBlock, $methodDocBlock);

        $io->info(sprintf("Updated %s", $formFile->reflection->getFileName()));
    }

    private function outputType(SymfonyStyle $io, FormFile $formFile, Union $union): void
    {
        $name = $this->typeNamer->name($formFile->reflection);
        $type = $this->decorator->decorate($union);

        $decorated = new PsalmType($name, $type);
        $io->section(sprintf("Psalm type for %s", $formFile->reflection->getFileName()));
        $io->block((string) $decorated);
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\Attribute\PsalmTypeCustomised;
use Kynx\Laminas\FormShape\Attribute\PsalmTypeIgnore;
use Kynx\Laminas\FormShape\ExceptionInterface;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\Locator\FormFile;
use Kynx\Laminas\FormShape\Locator\FormLocatorInterface;
use Kynx\Laminas\FormShape\Writer\FileWriterInterface;
use Laminas\Form\Element\Collection;
use Laminas\Form\Fieldset;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use Psalm\Type\Atomic\TTypeAlias;
use ReflectionClass;

use function array_merge;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormProcessor implements FormProcessorInterface
{
    private FieldsetVisitor $fieldsetVisitor;

    public function __construct(
        private FormLocatorInterface $locator,
        private FormVisitor $formVisitor,
        private FileWriterInterface $fileWriter
    ) {
        $this->fieldsetVisitor = new FieldsetVisitor($this->formVisitor);
    }

    public function process(
        array $paths,
        ProgressListenerInterface $listener,
        bool $processFieldsets = true,
        bool $removeGetDataReturn = true
    ): void {
        $formFiles = $this->locator->locate($paths);
        if ($formFiles === []) {
            $listener->finally(0);
            return;
        }

        $types = [];
        if ($processFieldsets) {
            $types = $this->processFieldsets($formFiles, $listener);
        }

        $processed = $this->processForms($formFiles, $types, $listener, $removeGetDataReturn);
        $listener->finally($processed);
    }

    /**
     * @param array<FormFile> $formFiles
     * @return array<class-string, ImportType>
     */
    private function processFieldsets(array $formFiles, ProgressListenerInterface $listener): array
    {
        $fieldsets = [];
        foreach ($formFiles as $formFile) {
            $fieldsets = array_merge($fieldsets, $this->getFieldsets($formFile->form));
        }

        $types = [];
        foreach ($fieldsets as $fieldset) {
            $types = $this->processFieldset($fieldset, $types, $listener);
        }

        return $types;
    }

    /**
     * @param array<FormFile> $formFiles
     * @param array<class-string, ImportType> $types
     */
    private function processForms(
        array $formFiles,
        array $types,
        ProgressListenerInterface $listener,
        bool $removeGetDataReturn
    ): int {
        $count = 0;
        foreach ($formFiles as $formFile) {
            $count += $this->processForm($formFile, $types, $listener, $removeGetDataReturn);
        }
        return $count;
    }

    /**
     * @param array<class-string, ImportType> $types
     */
    private function processForm(
        FormFile $formFile,
        array $types,
        ProgressListenerInterface $listener,
        bool $removeGetDataReturn
    ): int {
        try {
            $union = $this->formVisitor->visit($formFile->form, $types);
            if ($this->getCustomType($formFile->reflection) !== null) {
                return 0;
            }

            $this->fileWriter->write($formFile->reflection, $union, $types, $removeGetDataReturn);
            $listener->success($formFile->reflection);
        } catch (ExceptionInterface $e) {
            $listener->error(sprintf(
                "Error processing %s: %s",
                $formFile->reflection->getName(),
                $e->getMessage()
            ));
            return 0;
        }

        return 1;
    }

    /**
     * @param array<class-string, ImportType> $types
     * @return array<class-string, ImportType>
     */
    private function processFieldset(
        FieldsetInterface $fieldset,
        array $types,
        ProgressListenerInterface $listener
    ): array {
        $reflection = new ReflectionClass($fieldset);
        if (! $this->canProcess($reflection)) {
            return [];
        }

        try {
            $union    = $this->fieldsetVisitor->visit($fieldset, $types);
            $typeName = $this->getCustomType($reflection);

            if ($typeName === null) {
                $typeName = $this->fileWriter->write($reflection, $union, $types);
                $listener->success($reflection);
            }
        } catch (ExceptionInterface $e) {
            $listener->error(sprintf(
                "Error processing %s: %s",
                $reflection->getName(),
                $e->getMessage()
            ));
            return [];
        }

        $types[$fieldset::class] = new ImportType(new TTypeAlias($reflection->getName(), $typeName), $union);
        return $types;
    }

    /**
     * @return array<class-string, FieldsetInterface>
     */
    private function getFieldsets(FieldsetInterface $fieldset): array
    {
        $fieldsets = [];
        foreach ($fieldset->getFieldsets() as $child) {
            $fieldsets = array_merge($fieldsets, $this->getFieldsets($child));
        }

        if ($fieldset instanceof Collection) {
            $targetElement = $fieldset->getTargetElement();
            if ($targetElement instanceof FieldsetInterface) {
                $fieldsets = array_merge($fieldsets, $this->getFieldsets($targetElement));
            }
        } elseif (! ($fieldset::class === Fieldset::class || $fieldset instanceof FormInterface)) {
            $fieldsets[$fieldset::class] = $fieldset;
        }

        return $fieldsets;
    }

    private function canProcess(ReflectionClass $reflection): bool
    {
        return $reflection->getAttributes(PsalmTypeIgnore::class) === [];
    }

    private function getCustomType(ReflectionClass $reflection): ?string
    {
        $attributes = $reflection->getAttributes(PsalmTypeCustomised::class);
        if ($attributes === []) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();
        return $attribute->psalmType;
    }
}

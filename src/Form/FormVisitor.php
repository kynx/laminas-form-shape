<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilter\CollectionInput;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Laminas\Form\Element\Collection;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Psalm\Type\Union;

use function array_keys;
use function assert;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FormVisitor
{
    public function __construct(private InputFilterVisitorInterface $inputFilterVisitor)
    {
    }

    public function visit(FormInterface $form, array $importTypes): Union
    {
        $clone = clone $form;

        $data = $this->createData($form);
        assert($data !== null);
        $clone->setData($data)->isValid();
        $inputFilter = $this->convertCollectionFilters($clone, $clone->getInputFilter());

        return $this->inputFilterVisitor->visit($inputFilter, $this->getImportTypes($clone, $importTypes));
    }

    /**
     * Return dummy data so collection input filters are populated
     */
    private function createData(ElementInterface $elementOrFieldset): ?array
    {
        if (! $elementOrFieldset instanceof FieldsetInterface) {
            return null;
        }

        $data = [];
        foreach ($elementOrFieldset->getElements() as $element) {
            $data[(string) $element->getName()] = null;
        }

        foreach ($elementOrFieldset->getFieldsets() as $childFieldset) {
            if ($childFieldset instanceof Collection) {
                $targetElement = $childFieldset->getTargetElement();
                if ($targetElement === null) {
                    continue;
                }

                $count = $childFieldset->getCount() ?: 1;
                for ($i = 0; $i < $count; $i++) {
                    $data[(string) $childFieldset->getName()][$i] = $this->createData($targetElement);
                }
                continue;
            }

            $data[(string) $childFieldset->getName()] = $this->createData($childFieldset);
        }

        return $data;
    }

    /**
     * Convert input filters for collections to `CollectionInputFilter` so they are recognised as such
     */
    private function convertCollectionFilters(
        FieldsetInterface $fieldset,
        InputFilterInterface $inputFilter
    ): InputFilterInterface {
        foreach ($fieldset->getFieldsets() as $childFieldset) {
            $name = (string) $childFieldset->getName();
            if (! $inputFilter->has($name)) {
                continue;
            }

            $inputOrFilter = $inputFilter->get($name);
            if (! $inputOrFilter instanceof InputFilterInterface) {
                continue;
            }

            $childFilter = $this->convertCollectionFilters($childFieldset, $inputOrFilter);
            if (! $childFieldset instanceof Collection || $childFilter instanceof CollectionInputFilter) {
                continue;
            }

            if (! $childFilter->has(0)) {
                continue;
            }

            $target   = $childFilter->get(0);
            $required = ! $childFieldset->allowRemove();
            $count    = $required ? $childFieldset->getCount() : 0;

            if ($target instanceof InputInterface) {
                $inputOrFilter = CollectionInput::fromInput($target, $count, ! $required);
            } else {
                $inputOrFilter = new CollectionInputFilter();
                $inputOrFilter->setIsRequired($required);
                foreach (array_keys($target->getRawValues()) as $inputName) {
                    $inputOrFilter->getInputFilter()->add($target->get($inputName), $inputName);
                }
            }

            $inputFilter->remove($name);
            $inputFilter->add($inputOrFilter, $name);
        }

        return $inputFilter;
    }

    private function getImportTypes(FormInterface $form, array $importTypes): ImportTypes
    {
        return new ImportTypes($this->keyTypes($form, $importTypes));
    }

    private function keyTypes(FieldsetInterface $fieldset, array $importTypes): array
    {
        $keyed = [];
        foreach ($fieldset->getFieldsets() as $childFieldset) {
            $name = (string) $childFieldset->getName();
            if ($childFieldset instanceof Collection) {
                $childFieldset = $childFieldset->getTargetElement() instanceof FieldsetInterface
                    ? $childFieldset->getTargetElement()
                    : $childFieldset;
            }

            $keyed[$name] = $importTypes[$childFieldset::class]
                ?? $this->keyTypes($childFieldset, $importTypes);
        }

        return $keyed;
    }
}

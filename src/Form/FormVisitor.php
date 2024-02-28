<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Form;

use Kynx\Laminas\FormShape\InputFilter\ArrayInputBuilder;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\InputFilter\ImportTypes;
use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Laminas\Form\Element\Collection;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\BaseInputFilter;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;
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

    /**
     * @param array<ImportType> $importTypes
     */
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
            $data[(string) $element->getName()] = '';
        }

        foreach ($elementOrFieldset->getFieldsets() as $childFieldset) {
            if ($childFieldset instanceof Collection) {
                $targetElement = $childFieldset->getTargetElement();
                if ($targetElement === null) {
                    continue;
                }

                $count     = $childFieldset->getCount() ?: 1;
                $childData = [];
                for ($i = 0; $i < $count; $i++) {
                    $childData[$i] = $this->createData($targetElement);
                }
                $data[(string) $childFieldset->getName()] = $childData;
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
        $newFilter = new InputFilter();

        foreach ($fieldset->getIterator() as $elementOrFieldset) {
            $name = (string) $elementOrFieldset->getName();
            if (! $inputFilter->has($name)) {
                continue;
            }

            $inputOrFilter = $inputFilter->get($name);
            if (! ($inputOrFilter instanceof InputFilterInterface && $elementOrFieldset instanceof FieldsetInterface)) {
                $newFilter->add($inputOrFilter, $name);
                continue;
            }

            // If the collection's target element is an InputFilterProviderInterface, it's input filter will already be
            // a CollectionInputFilter. If the target element isn't, it won't be. That's just nuts :-[
            if ($elementOrFieldset instanceof Collection && $inputOrFilter instanceof CollectionInputFilter) {
                $targetElement = $elementOrFieldset->getTargetElement();
                // If `$targetElement` isn't a fieldset, something is very wrong
                assert($targetElement instanceof FieldsetInterface);

                $collectionFilter = $this->convertCollectionFilters(
                    $targetElement,
                    $inputOrFilter->getInputFilter()
                );
                // CollectionInputFilter::setInputFilter() will throw an exception if it isn't a BaseInputFilter
                assert($collectionFilter instanceof BaseInputFilter);

                $childFilter = new CollectionInputFilter();
                $childFilter->setIsRequired($inputOrFilter->getIsRequired())
                    ->setCount($inputOrFilter->getCount())
                    ->setInputFilter($collectionFilter);
            } else {
                $childFilter = $this->convertCollectionFilters($elementOrFieldset, $inputOrFilter);
            }

            if (! $elementOrFieldset instanceof Collection || $childFilter instanceof CollectionInputFilter) {
                $newFilter->add($childFilter, $name);
                continue;
            }

            if (! $childFilter->has(0)) {
                $newFilter->add($childFilter, $name);
                continue;
            }

            $target   = $childFilter->get(0);
            $required = ! $elementOrFieldset->allowRemove();
            $count    = $required ? $elementOrFieldset->getCount() : 0;

            if ($target instanceof InputInterface) {
                $inputOrFilter = ArrayInputBuilder::create($target);
                $inputOrFilter->setRequired($inputOrFilter->isRequired() || $count > 0);
            } else {
                $inputOrFilter = new CollectionInputFilter();
                $inputOrFilter->setIsRequired($required);
                foreach (array_keys($target->getRawValues()) as $inputName) {
                    $inputOrFilter->getInputFilter()->add($target->get($inputName), $inputName);
                }
            }

            $newFilter->add($inputOrFilter, $name);
        }

        $newFilter->setData($inputFilter->getRawValues());
        return $newFilter;
    }

    /**
     * @param array<ImportType> $importTypes
     */
    private function getImportTypes(FieldsetInterface $fieldset, array $importTypes): ImportTypes
    {
        $children = [];
        foreach ($fieldset->getFieldsets() as $childFieldset) {
            $name = (string) $childFieldset->getName();
            if ($childFieldset instanceof Collection) {
                $targetElement = $childFieldset->getTargetElement();
                if ($targetElement instanceof FieldsetInterface) {
                    $children[$name] = $this->getImportTypes($targetElement, $importTypes);
                }
                continue;
            }

            $children[$name] = $this->getImportTypes($childFieldset, $importTypes);
        }

        return new ImportTypes($importTypes[$fieldset::class] ?? null, $children);
    }
}

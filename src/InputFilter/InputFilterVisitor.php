<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\InputVisitorInterface;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\InputFilter\OptionalInputFilter;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Union;

use function array_keys;

final readonly class InputFilterVisitor implements InputFilterVisitorInterface
{
    /**
     * @param array<InputVisitorInterface> $inputVisitors
     */
    public function __construct(private array $inputVisitors)
    {
    }

    public function visit(InputFilterInterface $inputFilter, ImportTypes $importTypes): Union
    {
        if ($inputFilter instanceof CollectionInputFilter) {
            return $this->visitCollectionInputFilter($inputFilter, $importTypes);
        }

        $elements = [];
        foreach (array_keys($inputFilter->getRawValues()) as $childName) {
            $child = $inputFilter->get($childName);
            if ($child instanceof InputInterface) {
                $elements[$childName] = $this->visitInput($child);
                continue;
            }

            $childTypes           = $importTypes->getChildren($childName);
            $elements[$childName] = $this->visit($child, $childTypes);
        }

        $properties = ['possibly_undefined' => $inputFilter instanceof OptionalInputFilter];

        if ($elements === []) {
            return new Union([new TArray([Type::getArrayKey(), Type::getMixed()])], $properties);
        }

        $union = new Union([new TKeyedArray($elements)], $properties);
        return $this->getTypeAliasUnion($union, $importTypes);
    }

    private function visitCollectionInputFilter(CollectionInputFilter $inputFilter, ImportTypes $importTypes): Union
    {
        $collection = $this->visit($inputFilter->getInputFilter(), $importTypes);

        if ($inputFilter->getIsRequired()) {
            return new Union([new TNonEmptyArray([Type::getArrayKey(), $collection])]);
        }

        return new Union([new TArray([Type::getArrayKey(), $collection])]);
    }

    private function visitInput(InputInterface $input): Union
    {
        foreach ($this->inputVisitors as $visitor) {
            $union = $visitor->visit($input);
            if ($union !== null) {
                return $union;
            }
        }

        throw InputVisitorException::noVisitorForInput($input);
    }

    private function getTypeAliasUnion(Union $filterUnion, ImportTypes $importTypes): Union
    {
        $importType = $importTypes->get();
        if ($importType === null) {
            return $filterUnion;
        }

        if ($filterUnion->equals($importType->union, false, false)) {
            return new Union([$importType->type], ['possibly_undefined' => $filterUnion->possibly_undefined]);
        }

        return $filterUnion;
    }
}

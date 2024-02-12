<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
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
    public function __construct(private InputVisitorManager $inputVisitorManager)
    {
    }

    public function visit(InputFilterInterface $inputFilter): Union
    {
        if ($inputFilter instanceof CollectionInputFilter) {
            return $this->visitCollectionInputFilter($inputFilter);
        }

        $elements = [];
        foreach (array_keys($inputFilter->getRawValues()) as $childName) {
            $child = $inputFilter->get($childName);
            if ($child instanceof InputInterface) {
                $visitor              = $this->inputVisitorManager->getVisitor($child);
                $elements[$childName] = $visitor->visit($child);
                continue;
            }

            $elements[$childName] = $this->visit($child);
        }

        $properties = ['possibly_undefined' => $inputFilter instanceof OptionalInputFilter];

        if ($elements === []) {
            return new Union([new TArray([Type::getArrayKey(), Type::getMixed()])], $properties);
        }

        return new Union([new TKeyedArray($elements)], $properties);
    }

    private function visitCollectionInputFilter(CollectionInputFilter $inputFilter): Union
    {
        $collection = $this->visit($inputFilter->getInputFilter());
        $properties = ['possibly_undefined' => ! $inputFilter->getIsRequired()];

        if ($inputFilter->getCount() || $inputFilter->getIsRequired()) {
            $count = $inputFilter->getCount();
            return new Union([
                new TNonEmptyArray(
                    type_params: [Type::getArrayKey(), $collection],
                    min_count:   $count > 0 ? $count : 1
                ),
            ], $properties);
        }

        return new Union([new TArray([Type::getArrayKey(), $collection])], $properties);
    }
}

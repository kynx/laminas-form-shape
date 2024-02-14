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

use function array_filter;
use function array_keys;

final readonly class InputFilterVisitor implements InputFilterVisitorInterface
{
    /**
     * @param array<InputVisitorInterface> $inputVisitors
     */
    public function __construct(private array $inputVisitors)
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
                $elements[$childName] = $this->visitInput($child);
                continue;
            }

            $elements[$childName] = $this->visit($child);
        }

        $elementsRequired = (bool) array_filter(
            $elements,
            static fn (Union $element): bool => ! $element->possibly_undefined
        );

        $properties = ['possibly_undefined' => ! $elementsRequired || $inputFilter instanceof OptionalInputFilter];

        if ($elements === []) {
            return new Union([new TArray([Type::getArrayKey(), Type::getMixed()])], $properties);
        }

        return new Union([new TKeyedArray($elements)], $properties);
    }

    private function visitCollectionInputFilter(CollectionInputFilter $inputFilter): Union
    {
        $collection = $this->visit($inputFilter->getInputFilter());

        if ($inputFilter->getIsRequired()) {
            return new Union([new TNonEmptyArray([Type::getArrayKey(), $collection])]);
        }

        return new Union([new TArray([Type::getArrayKey(), $collection])], ['possibly_undefined' => true]);
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
}

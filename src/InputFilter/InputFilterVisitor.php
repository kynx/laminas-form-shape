<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Shape\CollectionFilterShape;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\InputFilter\OptionalInputFilter;

use function array_keys;

final readonly class InputFilterVisitor implements InputFilterVisitorInterface
{
    public function __construct(private InputVisitorManager $inputVisitorManager)
    {
    }

    public function visit(InputFilterInterface $inputFilter, string $name = ''): CollectionFilterShape|InputFilterShape
    {
        if ($inputFilter instanceof CollectionInputFilter) {
            $collection = $this->visit($inputFilter->getInputFilter());
            $optional   = ! $inputFilter->getIsRequired() && $inputFilter->getCount() === 0;
            return new CollectionFilterShape($name, $collection, $optional, $inputFilter->getCount() > 0);
        }

        $types = [];
        foreach (array_keys($inputFilter->getRawValues()) as $childName) {
            $child = $inputFilter->get($childName);
            if ($child instanceof InputInterface) {
                $visitor = $this->inputVisitorManager->getVisitor($child);
                $types[] = $visitor->visit($child);
                continue;
            }

            $types[] = $this->visit($child, (string) $childName);
        }

        return new InputFilterShape($name, $types, $inputFilter instanceof OptionalInputFilter);
    }
}

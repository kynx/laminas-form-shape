<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormShape\Shape\InputFilterShape;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;

use function array_keys;

final readonly class InputFilterVisitor implements InputFilterVisitorInterface
{
    public function __construct(private InputVisitorManager $inputVisitorManager)
    {
    }

    public function visit(InputFilterInterface $inputFilter, string $name = '', int $indent = 0): InputFilterShape
    {
        $types = [];
        foreach (array_keys($inputFilter->getRawValues()) as $childName) {
            $child = $inputFilter->get($childName);
            if ($child instanceof InputInterface) {
                $visitor = $this->inputVisitorManager->getVisitor($child);
                $types[] = $visitor->visit($child);
                continue;
            }

            $types[] = $this->visit($child, (string) $childName, $indent + 1);
        }

        return new InputFilterShape($name, $types);
    }
}

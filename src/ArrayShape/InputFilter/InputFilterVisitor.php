<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\InputFilter;

use Kynx\Laminas\FormCli\ArrayShape\InputFilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\ArrayType;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;

use function array_keys;

final readonly class InputFilterVisitor implements InputFilterVisitorInterface
{
    public function __construct(private InputVisitorManager $inputVisitorManager)
    {
    }

    public function visit(InputFilterInterface $inputFilter, string $name = '', int $indent = 0): ArrayType
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

        return new ArrayType($name, $types);
    }
}

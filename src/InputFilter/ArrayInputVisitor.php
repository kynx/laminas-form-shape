<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\InputFilter\ArrayInput;
use Laminas\InputFilter\InputInterface;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

use function array_map;

final readonly class ArrayInputVisitor extends AbstractInputVisitor
{
    public function visit(InputInterface $input): ?Union
    {
        if (! $input instanceof ArrayInput) {
            return null;
        }

        $initial = new Union([new TNull(), new TString()]);
        $union   = $this->visitInput($input, $initial);

        if ($union->getAtomicTypes() === []) {
            throw InputVisitorException::cannotGetInputType($input);
        }

        $union = new Union([new TArray([Type::getArrayKey(), $union])]);

        if ($this->isNonEmpty($input)) {
            $nonEmpty = array_map(
                static fn (Atomic $type): Atomic => $type instanceof TArray
                    ? new TNonEmptyArray($type->type_params)
                    : $type,
                $union->getAtomicTypes()
            );
            $union    = new Union($nonEmpty);
        }

        if ($this->hasFallback($input)) {
            return Type::combineUnionTypes($union, TypeUtil::toStrictUnion($input->getFallbackValue()));
        }

        return $union;
    }

    private function isNonEmpty(ArrayInput $input): bool
    {
        if ($input instanceof CollectionInput) {
            return (bool) $input->getCount();
        }
        return $input->isRequired();
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\InArray;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Union;

use function array_reduce;
use function assert;
use function current;
use function is_array;

final readonly class InArrayVisitor implements ValidatorVisitorInterface
{
    public function __construct(private bool $allowEmptyHaystack = true)
    {
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof InArray) {
            return $previous;
        }

        $haystack = $validator->getHaystack();
        assert(is_array($haystack));

        if ($haystack === [] && $this->allowEmptyHaystack) {
            return $previous;
        }
        if ($haystack === []) {
            return TypeUtil::remove($previous, $previous);
        }

        return $validator->getStrict() === true
            ? $this->getStrict($haystack, $previous)
            : $this->getLoose($haystack, $previous);
    }

    private function getStrict(array $haystack, Union $previous): Union
    {
        $union = array_reduce(
            $haystack,
            static fn (Union $u, mixed $v): Union => Type::combineUnionTypes($u, TypeUtil::toStrictUnion($v)),
            TypeUtil::toStrictUnion(current($haystack))
        );

        return TypeUtil::narrow($previous, $union);
    }

    private function getLoose(array $haystack, Union $previous): Union
    {
        $union = array_reduce(
            $haystack,
            static fn (Union $u, mixed $v): Union => Type::combineUnionTypes($u, TypeUtil::toLooseUnion($v)),
            TypeUtil::toLooseUnion(current($haystack))
        );

        return TypeUtil::narrow($previous, $union);
    }
}

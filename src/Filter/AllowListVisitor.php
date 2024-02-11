<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\AllowList;
use Laminas\Filter\FilterInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Union;

use function array_reduce;
use function current;

final readonly class AllowListVisitor implements FilterVisitorInterface
{
    public function __construct(private bool $allowEmptyList = true)
    {
    }

    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof AllowList) {
            return $previous;
        }

        $nullUnion = new Union([new TNull()]);

        $list = $filter->getList();
        if ($list === []) {
            return $this->allowEmptyList
                ? TypeUtil::widen($previous, $nullUnion)
                : $nullUnion;
        }

        $union = $filter->getStrict() === true
            ? $this->getStrict($list, $previous)
            : $this->getLoose($list, $previous);

        return TypeUtil::widen($union, $nullUnion);
    }

    private function getStrict(array $list, Union $previous): Union
    {
        $union = array_reduce(
            $list,
            static fn (Union $u, mixed $v): Union => Type::combineUnionTypes($u, TypeUtil::toStrictUnion($v)),
            TypeUtil::toStrictUnion(current($list))
        );

        return TypeUtil::narrow($previous, $union);
    }

    private function getLoose(array $list, Union $previous): Union
    {
        $union = array_reduce(
            $list,
            static fn (Union $u, mixed $v): Union => Type::combineUnionTypes($u, TypeUtil::toLooseUnion($v)),
            TypeUtil::toLooseUnion(current($list))
        );

        return TypeUtil::narrow($previous, $union);
    }
}

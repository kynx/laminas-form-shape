<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Union;

final readonly class BooleanVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof Boolean) {
            return $previous;
        }

        if ($filter->getCasting()) {
            return new Union([new TBool()]);
        }

        $type   = $filter->getType();
        $remove = [];

        if ($type & Boolean::TYPE_FALSE_STRING) {
            $remove[] = TypeUtil::getAtomicStringFromLiteral('false');
            $remove[] = TypeUtil::getAtomicStringFromLiteral('true');
        }

        if ($type & Boolean::TYPE_NULL) {
            $remove[] = new TNull();
        }

        if ($type & Boolean::TYPE_EMPTY_ARRAY) {
            $remove[] = Type::getEmptyArrayAtomic();
        }

        if ($type & Boolean::TYPE_ZERO_STRING) {
            $remove[] = TypeUtil::getAtomicStringFromLiteral('0');
            $remove[] = TypeUtil::getAtomicStringFromLiteral('1');
        }

        if ($type & Boolean::TYPE_FLOAT) {
            $remove[] = new TLiteralFloat(0.0);
            $remove[] = new TLiteralFloat(1.0);
        }

        if ($type & Boolean::TYPE_INTEGER) {
            $remove[] = new TLiteralInt(0);
            $remove[] = new TLiteralInt(1);
        }

        $visited = $remove === [] ? $previous : TypeUtil::remove($previous, new Union($remove));
        return TypeUtil::widen($visited, new Union([new TBool()]));
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToNull;
use Psalm\Type;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;

final readonly class ToNullVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, Union $previous): Union
    {
        if (! $filter instanceof ToNull) {
            return $previous;
        }

        $type = $filter->getType();

        if (! ($type & ToNull::TYPE_ALL)) {
            return $previous;
        }

        $visited = $previous;

        if ($type & ToNull::TYPE_FLOAT) {
            $visited = TypeUtil::replaceType($visited, new TLiteralFloat(0.0), new Union([new TFloat()]));
        }

        if ($type & ToNull::TYPE_ZERO_STRING) {
            $visited = TypeUtil::replaceType($visited, TLiteralString::make('0'), new Union([new TString()]));
        }

        if ($type & ToNull::TYPE_STRING) {
            $visited = TypeUtil::replaceType($visited, new TString(), new Union([new TNonEmptyString()]));
        }

        if ($type & ToNull::TYPE_EMPTY_ARRAY) {
            $visited = TypeUtil::replaceType(
                $visited,
                Type::getEmptyArrayAtomic(),
                new Union([new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()])])
            );
        }

        if ($type & ToNull::TYPE_INTEGER) {
            $visited = TypeUtil::replaceType($visited, new TLiteralInt(0), new Union([new TInt()]));
        }

        if ($type & ToNull::TYPE_BOOLEAN) {
            $visited = TypeUtil::replaceType($visited, new TBool(), new Union([new TTrue()]));
        }

        return $visited === $previous ? $previous : TypeUtil::widen($visited, new Union([new TNull()]));
    }
}

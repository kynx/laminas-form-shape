<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Countable;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TObject;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;
use Stringable;

final readonly class NotEmptyVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof NotEmpty) {
            return $previous;
        }

        $narrow = $remove = [];
        $type   = $validator->getType();
        $object = (bool) ($type & (NotEmpty::OBJECT_COUNT | NotEmpty::OBJECT_STRING));

        $visited = TypeUtil::narrow($previous, new Union([
            new TArray([Type::getArrayKey(), Type::getMixed()]),
            new TNull(),
            new TObject(),
            new TScalar(),
        ]));

        if ($type & NotEmpty::OBJECT_COUNT) {
            $narrow[] = new TNamedObject(Countable::class);
        }
        if ($type & NotEmpty::OBJECT_STRING) {
            $narrow[] = new TNamedObject(Stringable::class);
        }
        if (! ($type & NotEmpty::OBJECT) && ! $object) {
            $remove[] = new TObject();
        }
        if ($type & NotEmpty::SPACE) {
            $remove[] = TLiteralString::make(' ');
        }
        if ($type & NotEmpty::NULL) {
            $remove[] = new TNull();
        }
        if ($type & NotEmpty::EMPTY_ARRAY) {
            $narrow[] = new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()]);
        }
        if ($type & NotEmpty::ZERO) {
            $remove[] = TLiteralString::make('0');
        }
        if ($type & NotEmpty::STRING) {
            $narrow[] = new TNonEmptyString();
        }
        if ($type & NotEmpty::FLOAT) {
            $remove[] = new TLiteralFloat(0.0);
        }
        if ($type & NotEmpty::INTEGER) {
            $remove[] = new TLiteralInt(0);
            $narrow[] = new TIntRange(null, -1);
            $narrow[] = new TIntRange(1, null);
        }
        if ($type & NotEmpty::BOOLEAN) {
            $narrow[] = new TTrue();
        }

        if ($narrow !== []) {
            $visited = TypeUtil::narrow($visited, new Union($narrow));
        }
        if ($remove !== []) {
            $visited = TypeUtil::remove($visited, new Union($remove));
        }

        return $visited;
    }
}

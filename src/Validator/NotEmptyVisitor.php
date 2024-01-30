<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;

final readonly class NotEmptyVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof NotEmpty) {
            return $existing;
        }

        $types  = TypeUtil::filter($existing, [
            PsalmType::Null,
            PsalmType::String,
            PsalmType::NonEmptyString,
            PsalmType::Int,
            PsalmType::NegativeInt,
            PsalmType::PositiveInt,
            PsalmType::Float,
            PsalmType::Bool,
            PsalmType::Array,
            PsalmType::NonEmptyArray,
            PsalmType::Object,
        ]);
        $type   = $validator->getType();
        $object = (bool) ($type & (NotEmpty::OBJECT_COUNT | NotEmpty::OBJECT_STRING));

        if (! $object && $type & NotEmpty::OBJECT) {
            $types = TypeUtil::removeObjectTypes($types);
        }
        if ($type & NotEmpty::SPACE) {
            $types = TypeUtil::replaceStringTypes($types, [PsalmType::NonEmptyString]);
        }
        if ($type & NotEmpty::NULL) {
            $types = TypeUtil::removeType(PsalmType::Null, $types);
        }
        if ($type & NotEmpty::EMPTY_ARRAY) {
            $types = TypeUtil::replaceArrayTypes($types, [PsalmType::NonEmptyArray]);
        }
        if ($type & NotEmpty::STRING) {
            $types = TypeUtil::replaceStringTypes($types, [PsalmType::NonEmptyString]);
        }
        if ($type & NotEmpty::INTEGER) {
            $types = TypeUtil::replaceIntTypes($types, [PsalmType::NegativeInt, PsalmType::PositiveInt]);
        }
        if ($type & NotEmpty::BOOLEAN) {
            $types = TypeUtil::replaceBoolTypes($types, [PsalmType::True]);
        }

        return $types;
    }
}

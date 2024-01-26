<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;

final readonly class NotEmptyParser implements ValidatorParserInterface
{
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof NotEmpty) {
            return $existing;
        }

        $types  = PsalmType::filter($existing, [
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
            $types = PsalmType::removeObjectTypes($types);
        }
        if ($type & NotEmpty::SPACE) {
            $types = PsalmType::replaceStringTypes($types, [PsalmType::NonEmptyString]);
        }
        if ($type & NotEmpty::NULL) {
            $types = PsalmType::removeType(PsalmType::Null, $types);
        }
        if ($type & NotEmpty::EMPTY_ARRAY) {
            $types = PsalmType::replaceArrayTypes($types, [PsalmType::NonEmptyArray]);
        }
        if ($type & NotEmpty::STRING) {
            $types = PsalmType::replaceStringTypes($types, [PsalmType::NonEmptyString]);
        }
        if ($type & NotEmpty::INTEGER) {
            $types = PsalmType::replaceIntTypes($types, [PsalmType::NegativeInt, PsalmType::PositiveInt]);
        }
        if ($type & NotEmpty::BOOLEAN) {
            $types = PsalmType::replaceBoolTypes($types, [PsalmType::True]);
        }

        return $types;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorParserInterface;
use Laminas\Validator\Hex;
use Laminas\Validator\ValidatorInterface;

final readonly class HexParser implements ValidatorParserInterface
{
    /**
     * @inheritDoc
     */
    public function getTypes(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Hex) {
            return $existing;
        }

        $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return PsalmType::filter($existing, [PsalmType::Int, PsalmType::NonEmptyString]);
    }
}

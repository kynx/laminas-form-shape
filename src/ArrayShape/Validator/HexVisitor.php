<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\Hex;
use Laminas\Validator\ValidatorInterface;

final readonly class HexVisitor implements ValidatorVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Hex) {
            return $existing;
        }

        $existing = PsalmType::replaceStringTypes($existing, [PsalmType::NonEmptyString]);

        return PsalmType::filter($existing, [PsalmType::Int, PsalmType::NonEmptyString]);
    }
}

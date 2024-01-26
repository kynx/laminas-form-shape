<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Laminas\Validator\ValidatorInterface;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
interface ValidatorParserInterface
{
    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    public function getTypes(ValidatorInterface $validator, array $existing): array;
}

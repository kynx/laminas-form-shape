<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

/**
 * @psalm-type ParsedUnion = AbstractParsedType|PsalmType
 * @psalm-type ParsedArray = array<array-key, ParsedUnion>
 */
abstract readonly class AbstractParsedType implements TypeStringInterface
{
}

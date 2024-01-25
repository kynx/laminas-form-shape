<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Laminas\Filter\FilterInterface;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
interface FilterParserInterface
{
    /**
     * @param ParsedArray $existing
     * @return ParsedArray
     */
    public function getTypes(FilterInterface $filter, array $existing): array;
}

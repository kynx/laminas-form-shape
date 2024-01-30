<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Type\PsalmType;

/**
 * @psalm-type ReplaceTuple = list{PsalmType, PsalmType}
 */
final readonly class RegexPattern
{
    /**
     * @param list<PsalmType> $types
     * @param list<ReplaceTuple> $replace
     */
    public function __construct(public string $pattern, public array $types, public array $replace)
    {
    }
}

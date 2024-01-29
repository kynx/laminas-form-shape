<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
use Kynx\Laminas\FormCli\ArrayShape\ValidatorVisitorInterface;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;

final readonly class RegexVisitor implements ValidatorVisitorInterface
{
    /** @var array<string, RegexPattern> */
    private array $patterns;

    public function __construct(RegexPattern ...$regexPatterns)
    {
        $patterns = [];
        foreach ($regexPatterns as $pattern) {
            $patterns[$pattern->pattern] = $pattern;
        }

        $this->patterns = $patterns;
    }

    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Regex) {
            return $existing;
        }

        /** @psalm-suppress PossiblyNullArrayOffset Upstream docblock is wrong - it can't be null */
        $pattern = $this->patterns[$validator->getPattern()] ?? null;
        if ($pattern === null) {
            return $existing;
        }

        $types    = $pattern->types;
        $replaced = TypeUtil::filter($existing, [
            PsalmType::Float,
            PsalmType::Int,
            PsalmType::NegativeInt,
            PsalmType::PositiveInt,
            PsalmType::String,
            PsalmType::NonEmptyString,
        ]);
        foreach ($pattern->replace as $replacement) {
            [$search, $replace] = $replacement;
            $replaced           = TypeUtil::replaceType($search, $replace, $replaced);
            $types[]            = $replace;
        }

        return TypeUtil::filter($replaced, $types);
    }
}

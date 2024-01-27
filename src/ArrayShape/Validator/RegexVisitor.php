<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Validator;

use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
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

    public function getTypes(ValidatorInterface $validator, array $existing): array
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
        $replaced = PsalmType::filter($existing, [PsalmType::Float, PsalmType::Int, PsalmType::String]);
        foreach ($pattern->replace as $replacement) {
            [$search, $replace] = $replacement;
            $replaced           = PsalmType::replaceType($search, $replace, $replaced);
            $types[]            = $replace;
        }

        return PsalmType::filter($replaced, $types);
    }
}

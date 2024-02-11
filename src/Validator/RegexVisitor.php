<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

final readonly class RegexVisitor implements ValidatorVisitorInterface
{
    /**
     * @param array<string, Union> $patterns
     */
    public function __construct(private array $patterns)
    {
    }

    public function visit(ValidatorInterface $validator, Union $previous): Union
    {
        if (! $validator instanceof Regex) {
            return $previous;
        }

        $visited = TypeUtil::narrow($previous, new Union([
            new TFloat(),
            new TInt(),
            new TString(),
        ]));

        /** @psalm-suppress PossiblyNullArrayOffset Upstream docblock is wrong - it can't be null */
        $pattern = $this->patterns[$validator->getPattern()] ?? null;
        if ($pattern === null) {
            return $visited;
        }

        return TypeUtil::narrow($visited, $pattern);
    }
}

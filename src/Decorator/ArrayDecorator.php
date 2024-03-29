<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Psalm\Type\Atomic\TArray;

use function sprintf;

final readonly class ArrayDecorator
{
    public function __construct(private PrettyPrinter $unionDecorator)
    {
    }

    public function decorate(TArray $array, int $indent = 0): string
    {
        [$key, $value] = $array->type_params;
        return sprintf(
            '%s<%s, %s>',
            $array->value,
            $key->getKey(),
            $this->unionDecorator->decorate($value, $indent)
        );
    }
}

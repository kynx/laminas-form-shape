<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\TypeNamerInterface;
use ReflectionClass;

use function str_replace;

final readonly class TypeNamer implements TypeNamerInterface
{
    public function __construct(private string $template)
    {
    }

    public function name(ReflectionClass $reflection): string
    {
        return str_replace('{shortName}', $reflection->getShortName(), $this->template);
    }
}

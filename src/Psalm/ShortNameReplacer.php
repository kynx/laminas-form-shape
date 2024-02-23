<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\MutableTypeVisitor;
use Psalm\Type\TypeNode;

use function strrpos;
use function substr;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final class ShortNameReplacer extends MutableTypeVisitor
{
    use IsFqcnTypeTrait;

    /**
     * @param array<string, string> $aliases
     */
    public function __construct(private readonly array $aliases)
    {
    }

    protected function enterNode(TypeNode &$type): ?int
    {
        if ($this->isFqcnType($type)) {
            $type = $this->getNamedObject($type);
        }
        if ($type instanceof TTypeAlias) {
            $type = new TNamedObject($type->alias_name);
        }

        return null;
    }

    private function getNamedObject(TNamedObject $type): TNamedObject
    {
        $shortName = $this->aliases[$type->value] ?? substr($type->value, (int) strrpos($type->value, '\\'));

        return match ($type::class) {
            TGenericObject::class => new TGenericObject($shortName, $type->type_params),
            TNamedObject::class   => new TNamedObject($shortName)
        };
    }
}

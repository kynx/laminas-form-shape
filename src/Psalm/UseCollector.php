<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\TypeNode;
use Psalm\Type\TypeVisitor;

use function array_unique;
use function array_values;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final class UseCollector extends TypeVisitor
{
    use IsFqcnTypeTrait;

    /** @var list<string> */
    private array $uses = [];

    protected function enterNode(TypeNode $type): ?int
    {
        if ($type instanceof TTypeAlias) {
            $this->uses[] = $type->declaring_fq_classlike_name;
        }
        if ($this->isFqcnType($type)) {
            $this->uses[] = $type->value;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function getUses(): array
    {
        return array_values(array_unique($this->uses));
    }
}

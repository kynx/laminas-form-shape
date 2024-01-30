<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;

final readonly class BooleanVisitor implements FilterVisitorInterface
{
    public function visit(FilterInterface $filter, array $existing): array
    {
        if (! $filter instanceof Boolean) {
            return $existing;
        }

        if ($filter->getCasting()) {
            return [PsalmType::Bool];
        }

        $type = $filter->getType();
        if ($type & Boolean::TYPE_NULL) {
            $existing = TypeUtil::removeType(PsalmType::Null, $existing);
        }

        $existing[] = PsalmType::Bool;
        return $existing;
    }
}

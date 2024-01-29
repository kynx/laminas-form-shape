<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\FilterVisitorInterface;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Kynx\Laminas\FormCli\ArrayShape\Type\TypeUtil;
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

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator\Sitemap;

use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Sitemap\Priority;
use Laminas\Validator\ValidatorInterface;

final readonly class PriorityVisitor implements ValidatorVisitorInterface
{
    public function visit(ValidatorInterface $validator, array $existing): array
    {
        if (! $validator instanceof Priority) {
            return $existing;
        }

        $existing = TypeUtil::replaceStringTypes($existing, [PsalmType::NumericString]);

        return TypeUtil::filter($existing, [PsalmType::Int, PsalmType::Float, PsalmType::NumericString]);
    }
}

<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape;

use Kynx\Laminas\FormShape\Type\TypeUtil;
use Laminas\Validator\ValidatorInterface;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
interface ValidatorVisitorInterface
{
    /**
     * @param VisitedArray $existing
     * @return VisitedArray
     */
    public function visit(ValidatorInterface $validator, array $existing): array;
}
